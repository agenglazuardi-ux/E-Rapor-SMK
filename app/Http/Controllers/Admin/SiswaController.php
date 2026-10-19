<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\SiswaExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->query($request)->paginate(20)->withQueryString();
        $kelas = Kelas::orderBy('nama')->get();

        return view('admin.siswa.index', compact('items', 'kelas'));
    }

    public function create()
    {
        return view('admin.siswa.form', ['item' => new Siswa(['status' => 'aktif']), 'kelas' => Kelas::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $siswa = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['nis'],
                'role' => 'siswa',
                'password' => $data['password'] ?: $data['nis'],
            ]);

            return $user->siswa()->create($data);
        });
        RiwayatKelas::sinkron([$siswa]);

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa ditambahkan. Login: NIS, password default: NIS (jika dikosongkan).');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.form', ['item' => $siswa, 'kelas' => Kelas::orderBy('nama')->get()]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $this->validated($request, $siswa);
        if ($data['status'] !== 'aktif') {
            $data['kelas_id'] = null;
        }
        DB::transaction(function () use ($data, $siswa) {
            RiwayatKelas::bekukan([$siswa]);
            $siswa->update($data);
            $userData = ['name' => $data['nama'], 'username' => $data['nis']];
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $siswa->user->update($userData);
        });
        $siswa = $siswa->fresh('kelas');
        RiwayatKelas::sinkron([$siswa]);
        $kelas = $siswa->kelas?->nama ?? ucfirst($siswa->status);

        return redirect()->route('admin.siswa.index')->with('success', "Data siswa diperbarui, {$siswa->nama} ({$kelas}).");
    }

    public function destroy(Siswa $siswa)
    {
        if (\App\Models\Nilai::terisi()->where('siswa_id', $siswa->id)->exists() || \App\Models\RaporSiswa::terisi()->where('siswa_id', $siswa->id)->exists()) {
            return back()->with('error', "Siswa {$siswa->nama} sudah memiliki nilai/absensi. Menghapusnya akan menghapus rapor yang pernah dibuat. Ubah status menjadi Pindah/Lulus lewat menu Edit sebagai gantinya.");
        }
        $siswa->user->delete();

        return back()->with('success', 'Siswa dihapus.');
    }

    public function importForm()
    {
        return view('admin.siswa.import', ['header' => SiswaExcel::HEADER]);
    }

    public function import(Request $request, SiswaExcel $excel)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'], ['file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.']);
        $hasil = $excel->import($request->file('file')->getRealPath(), $request->boolean('perbarui'));

        return redirect()->route('admin.siswa.import.form')->with('hasil_import', $hasil);
    }

    public function template(SiswaExcel $excel)
    {
        return $excel->template();
    }

    public function export(Request $request, SiswaExcel $excel)
    {
        return $excel->export($this->query($request)->get());
    }

    private function query(Request $request)
    {
        return Siswa::with('kelas')
            ->when($request->kelas_id, fn ($q, $k) => $q->where('kelas_id', $k))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('nama', 'like', "%$s%")->orWhere('nis', 'like', "%$s%")))
            ->orderBy('nama');
    }

    private function validated(Request $request, ?Siswa $siswa = null): array
    {
        return $request->validate([
            'nis' => 'required|string|max:20|unique:siswas,nis,'.$siswa?->id.'|unique:users,username,'.$siswa?->user_id,
            'nisn' => 'nullable|string|max:20|unique:siswas,nisn,'.$siswa?->id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'required|in:aktif,lulus,pindah',
            'tahun_lulus' => 'nullable|integer|min:2000|max:2100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ortu' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);
    }
}
