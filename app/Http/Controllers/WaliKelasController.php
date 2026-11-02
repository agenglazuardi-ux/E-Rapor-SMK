<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\RaporSiswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class WaliKelasController extends Controller
{
    private const TINGKAT_BERIKUTNYA = ['X' => 'XI', 'XI' => 'XII'];

    public function index(Request $request)
    {
        $query = Kelas::with('jurusan', 'waliKelas', 'siswas')->orderBy('nama');
        if ($request->user()->role === 'guru') {
            $query->where('wali_kelas_id', $request->user()->guru?->id ?? 0);
        }
        $kelasList = $query->get();
        $ta = TahunAjaran::aktif();

        return view('wali.index', compact('kelasList', 'ta'));
    }

    public function absensi(Request $request, Kelas $kelas)
    {
        $this->authorizeKelas($request, $kelas);
        $ta = TahunAjaran::aktifOrAbort();
        $siswas = $kelas->siswas;
        $rapors = RaporSiswa::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $siswas->pluck('id'))->get()->keyBy('siswa_id');

        $genap = $ta->semester === 'Genap';
        $tingkatTujuan = self::TINGKAT_BERIKUTNYA[$kelas->tingkat] ?? null;
        $tujuanList = ($genap && $tingkatTujuan)
            ? Kelas::with('jurusan')->where('tingkat', $tingkatTujuan)->orderBy('nama')->get()
            : collect();

        return view('wali.absensi', compact('kelas', 'ta', 'siswas', 'rapors', 'genap', 'tingkatTujuan', 'tujuanList'));
    }

    public function simpanAbsensi(Request $request, Kelas $kelas)
    {
        $this->authorizeKelas($request, $kelas);
        $ta = TahunAjaran::aktifOrAbort();
        $request->validate([
            'data' => 'array',
            'data.*.sakit' => 'nullable|integer|min:0|max:365',
            'data.*.izin' => 'nullable|integer|min:0|max:365',
            'data.*.alpa' => 'nullable|integer|min:0|max:365',
            'data.*.catatan_wali' => 'nullable|string|max:1000',
            'data.*.keputusan' => 'nullable|in:naik,tinggal,lulus',
            'data.*.kelas_tujuan' => 'nullable|string|max:100',
        ]);

        $siswas = $kelas->siswas()->get()->keyBy('id');
        $genap = $ta->semester === 'Genap';

        // Keputusan kenaikan hanya diisi di semester Genap; validasi dulu sebelum ada yang disimpan.
        $keputusan = [];
        if ($genap) {
            $tingkatTujuan = self::TINGKAT_BERIKUTNYA[$kelas->tingkat] ?? null;
            $namaTujuan = $tingkatTujuan ? Kelas::where('tingkat', $tingkatTujuan)->pluck('nama')->all() : [];
            $errors = [];
            foreach ($request->input('data', []) as $siswaId => $row) {
                $s = $siswas[(int) $siswaId] ?? null;
                if (! $s) {
                    continue;
                }
                $k = ($row['keputusan'] ?? null) ?: null;
                $tujuan = null;
                if ($k === 'naik') {
                    $tujuan = $row['kelas_tujuan'] ?? null;
                    if (! $tingkatTujuan) {
                        $errors[] = "{$s->nama}: kelas {$kelas->tingkat} tidak bisa dinaikkan, pilih Lulus atau Tinggal.";
                        continue;
                    }
                    if (! $tujuan || ! in_array($tujuan, $namaTujuan, true)) {
                        $errors[] = "{$s->nama}: pilih kelas tujuan tingkat {$tingkatTujuan}.";
                        continue;
                    }
                } elseif ($k === 'lulus') {
                    if ($kelas->tingkat !== 'XII') {
                        $errors[] = "{$s->nama}: Lulus hanya untuk siswa kelas XII.";
                        continue;
                    }
                } elseif ($k === 'tinggal') {
                    $tujuan = $kelas->nama;
                }
                $keputusan[$s->id] = ['keputusan' => $k, 'kelas_tujuan' => $tujuan];
            }
            if ($errors) {
                return back()->with('error', 'Belum disimpan. '.implode(' ', $errors));
            }
        }

        foreach ($request->input('data', []) as $siswaId => $row) {
            if (! $siswas->has((int) $siswaId)) {
                continue;
            }
            $isi = [
                'sakit' => (int) ($row['sakit'] ?? 0),
                'izin' => (int) ($row['izin'] ?? 0),
                'alpa' => (int) ($row['alpa'] ?? 0),
                'catatan_wali' => $row['catatan_wali'] ?? null,
            ];
            if ($genap) {
                $isi += $keputusan[(int) $siswaId] ?? ['keputusan' => null, 'kelas_tujuan' => null];
            }
            RaporSiswa::updateOrCreate(['siswa_id' => $siswaId, 'tahun_ajaran_id' => $ta->id], $isi);
        }

        RiwayatKelas::sinkron($kelas->siswas()->get(), $ta);

        return back()->with('success', $genap ? 'Absensi, catatan, dan keputusan kenaikan kelas disimpan.' : 'Absensi & catatan wali kelas disimpan.');
    }

    public function rekap(Request $request, Kelas $kelas)
    {
        $this->authorizeKelas($request, $kelas);
        $aktif = TahunAjaran::aktifOrAbort();
        $ta = $request->ta ? TahunAjaran::findOrFail($request->ta) : $aktif;
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->orderByDesc('semester')->get();
        $kelas->load('jurusan', 'waliKelas');

        $siswas = $ta->id === $aktif->id
            ? $kelas->siswas
            : Siswa::whereIn('id', RiwayatKelas::where('kelas_id', $kelas->id)->where('tahun_ajaran_id', $ta->id)->pluck('siswa_id'))->orderBy('nama')->get();

        $nilaiAll = Nilai::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $siswas->pluck('id'))->get();
        $mapelIds = $nilaiAll->pluck('mapel_id')->merge($ta->id === $aktif->id ? $kelas->pengajars()->pluck('mapel_id') : [])->unique();
        $mapels = Mapel::whereIn('id', $mapelIds)->get()
            ->sortBy(fn ($m) => array_search($m->kelompok, Mapel::KELOMPOK) * 1000 + $m->urutan)->values();
        $nilai = $nilaiAll->groupBy('siswa_id')->map(fn ($g) => $g->pluck('nilai_akhir', 'mapel_id'));

        $rows = $siswas->map(function ($s) use ($nilai, $mapels) {
            $n = $nilai[$s->id] ?? collect();
            $vals = $mapels->map(fn ($m) => $n[$m->id] ?? null)->filter(fn ($v) => $v !== null);

            return ['siswa' => $s, 'nilai' => $n, 'jumlah' => $vals->sum(), 'rata' => $vals->count() ? round($vals->avg(), 2) : 0];
        });

        $ranked = $rows->sortByDesc('jumlah')->values();
        // Jumlah nilai sama = peringkat sama (1, 2, 2, 4).
        $rank = [];
        $jumlahSebelum = null;
        $rankSebelum = 0;
        foreach ($ranked as $i => $r) {
            if ($r['jumlah'] <= 0) {
                $rank[$r['siswa']->id] = '-';
                continue;
            }
            if ($r['jumlah'] !== $jumlahSebelum) {
                $rankSebelum = $i + 1;
                $jumlahSebelum = $r['jumlah'];
            }
            $rank[$r['siswa']->id] = $rankSebelum;
        }

        $rataMapel = $mapels->mapWithKeys(fn ($m) => [$m->id => round($rows->map(fn ($r) => $r['nilai'][$m->id] ?? null)->filter(fn ($v) => $v !== null)->avg() ?? 0, 2)]);

        return view('wali.rekap', compact('kelas', 'ta', 'tahunAjarans', 'mapels', 'rows', 'rank', 'rataMapel'));
    }

    private function authorizeKelas(Request $request, Kelas $kelas): void
    {
        $user = $request->user();
        abort_unless($user->role === 'admin' || $kelas->wali_kelas_id === $user->guru?->id, 403, 'Anda bukan wali kelas ini.');
    }
}