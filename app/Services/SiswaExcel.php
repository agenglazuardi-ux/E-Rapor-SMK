<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaExcel
{
    public const HEADER = ['NIS', 'NISN', 'Nama Lengkap', 'L/P', 'Kelas', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Alamat', 'Nama Orang Tua/Wali'];

    public function template(): StreamedResponse
    {
        $book = $this->book([['2026001', '0101234567', 'Contoh Nama Siswa', 'L', Kelas::orderBy('nama')->value('nama') ?? 'X TKJ 1', 'Pekanbaru', '2010-05-17', 'Jl. Contoh No. 1', 'Nama Orang Tua']]);
        $ref = $book->createSheet()->setTitle('Daftar Kelas');
        $ref->fromArray([['Nama Kelas (salin persis ke kolom Kelas)']], null, 'A1');
        $ref->fromArray(Kelas::orderBy('nama')->pluck('nama')->map(fn ($n) => [$n])->all(), null, 'A2');
        $ref->getColumnDimension('A')->setWidth(42);
        $ref->getStyle('A1')->getFont()->setBold(true);
        $book->setActiveSheetIndex(0);

        return $this->download($book, 'template-import-siswa.xlsx');
    }

    public function export(Collection $siswas): StreamedResponse
    {
        $rows = $siswas->map(fn ($s) => [
            $s->nis, $s->nisn, $s->nama, $s->jenis_kelamin, $s->kelas?->nama, $s->tempat_lahir,
            $s->tanggal_lahir?->format('Y-m-d'), $s->alamat, $s->nama_ortu, ucfirst($s->status),
        ])->all();
        $book = $this->book($rows, [...self::HEADER, 'Status']);

        return $this->download($book, 'data-siswa-'.now()->format('Ymd').'.xlsx');
    }

    // Mengembalikan ['baru' => n, 'diperbarui' => n, 'gagal' => [pesan...]].
    public function import(string $path, bool $perbarui): array
    {
        set_time_limit(600);
        $rows = IOFactory::load($path)->getSheet(0)->toArray(null, true, false, false);
        $kelas = Kelas::pluck('id', 'nama')->mapWithKeys(fn ($id, $nama) => [mb_strtolower(trim($nama)) => $id]);
        $hasil = ['baru' => 0, 'diperbarui' => 0, 'gagal' => []];
        $tersentuh = [];

        foreach (array_slice($rows, 1, null, true) as $i => $r) {
            $baris = $i + 1;
            $r = array_map(fn ($v) => is_string($v) ? trim($v) : $v, array_pad($r, 9, null));
            if (collect($r)->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }
            [$nis, $nisn, $nama, $jk, $kelasNama, $tempat, $tgl, $alamat, $ortu] = $r;
            $nis = (string) $nis;
            $nisn = $nisn === null || $nisn === '' ? null : (string) $nisn;
            $jk = $this->jenisKelamin($jk);
            $kelasId = $kelasNama ? ($kelas[mb_strtolower((string) $kelasNama)] ?? false) : null;
            $tanggal = $this->tanggal($tgl);
            $ada = Siswa::where('nis', $nis)->first();

            $v = Validator::make(compact('nis', 'nisn', 'nama', 'jk'), [
                'nis' => 'required|max:20',
                'nisn' => 'nullable|max:20|unique:siswas,nisn,'.$ada?->id,
                'nama' => 'required|max:255',
                'jk' => 'required|in:L,P',
            ], ['jk.in' => 'L/P harus L atau P.', 'jk.required' => 'L/P wajib diisi.', 'nisn.unique' => 'NISN sudah dipakai siswa lain.']);
            $errors = $v->errors()->all();
            if ($kelasId === false) {
                $errors[] = "Kelas \"{$kelasNama}\" tidak ditemukan.";
            }
            if ($tgl && ! $tanggal) {
                $errors[] = 'Format tanggal lahir tidak dikenali.';
            }
            if ($ada && ! $perbarui) {
                $errors[] = "NIS {$nis} sudah terdaftar.";
            }
            if (! $ada && User::where('username', $nis)->exists()) {
                $errors[] = "Username {$nis} sudah dipakai akun lain.";
            }
            if ($errors) {
                $hasil['gagal'][] = "Baris {$baris} ({$nama}): ".implode(' ', $errors);
                continue;
            }

            $data = ['nis' => $nis, 'nisn' => $nisn, 'nama' => $nama, 'jenis_kelamin' => $jk, 'kelas_id' => $kelasId,
                'tempat_lahir' => $tempat ?: null, 'tanggal_lahir' => $tanggal, 'alamat' => $alamat ?: null, 'nama_ortu' => $ortu ?: null];

            $siswa = DB::transaction(function () use ($ada, $data, $nis) {
                if ($ada) {
                    RiwayatKelas::bekukan([$ada]);
                    $ada->update($data);
                    $ada->user->update(['name' => $data['nama']]);

                    return $ada;
                }
                $user = User::create(['name' => $data['nama'], 'username' => $nis, 'role' => 'siswa', 'password' => Hash::make($nis, ['rounds' => 10])]);

                return $user->siswa()->create($data);
            });
            $ada ? $hasil['diperbarui']++ : $hasil['baru']++;
            $tersentuh[] = $siswa->id;
        }

        RiwayatKelas::sinkron(Siswa::whereIn('id', $tersentuh)->get());

        return $hasil;
    }

    private function jenisKelamin($v): ?string
    {
        $v = mb_strtoupper(trim((string) $v));

        return match (true) {
            in_array($v, ['L', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA'], true) => 'L',
            in_array($v, ['P', 'PEREMPUAN', 'WANITA'], true) => 'P',
            $v === '' => null,
            default => $v,
        };
    }

    private function tanggal($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return Date::excelToDateTimeObject($v)->format('Y-m-d');
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'] as $f) {
            try {
                $d = Carbon::createFromFormat('!'.$f, (string) $v);
                if ($d && $d->format($f) === (string) $v) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function book(array $rows, array $header = self::HEADER): Spreadsheet
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet()->setTitle('Data Siswa');
        $sheet->fromArray([$header], null, 'A1');
        foreach ($rows as $i => $row) {
            foreach (array_values($row) as $c => $val) {
                $sheet->setCellValueExplicit([$c + 1, $i + 2], (string) $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        $last = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$last}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$last}1")->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0F3D2E');
        foreach (range('A', $last) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        return $book;
    }

    private function download(Spreadsheet $book, string $nama): StreamedResponse
    {
        return response()->streamDownload(fn () => (new Xlsx($book))->save('php://output'), $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
