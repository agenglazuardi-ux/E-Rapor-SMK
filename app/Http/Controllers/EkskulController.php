<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class EkskulController extends Controller
{
    public function index(Request $request)
    {
        $ta = TahunAjaran::aktif();
        $items = Ekstrakurikuler::where('guru_id', $request->user()->guru?->id ?? 0)
            ->withCount(['anggotas' => fn ($q) => $q->where('tahun_ajaran_id', $ta?->id ?? 0)])
            ->orderBy('nama')->get();

        return view('guru.ekskul.index', compact('items', 'ta'));
    }

    public function edit(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($request, $ekstrakurikuler);
        $ta = TahunAjaran::aktifOrAbort();
        $anggotas = EkstrakurikulerSiswa::with('siswa.kelas')
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)->where('tahun_ajaran_id', $ta->id)->get()
            ->sortBy(fn ($a) => $a->siswa->nama)->values();

        $kelasList = Kelas::orderBy('nama')->get();
        $kelasId = $request->integer('kelas');
        $calon = collect();
        if ($kelasId) {
            $calon = Siswa::where('status', 'aktif')->where('kelas_id', $kelasId)
                ->whereNotIn('id', $anggotas->pluck('siswa_id'))->orderBy('nama')->get();
        }

        return view('guru.ekskul.edit', compact('ekstrakurikuler', 'ta', 'anggotas', 'kelasList', 'kelasId', 'calon'));
    }

    // Simpan keterangan semua anggota + keluarkan anggota yang dicentang.
    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($request, $ekstrakurikuler);
        $ta = TahunAjaran::aktifOrAbort();
        $request->validate([
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string|max:500',
            'hapus' => 'array',
            'hapus.*' => 'integer',
        ], [], ['keterangan.*' => 'keterangan']);

        $anggotas = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tahun_ajaran_id', $ta->id)->get()->keyBy('siswa_id');

        foreach ($request->input('keterangan', []) as $siswaId => $keterangan) {
            $anggotas->get((int) $siswaId)?->update(['keterangan' => $keterangan]);
        }

        $hapus = array_map('intval', $request->input('hapus', []));
        if ($hapus) {
            EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                ->where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $hapus)->delete();
        }

        return back()->with('success', 'Data anggota ekstrakurikuler berhasil disimpan.');
    }

    // Tambah anggota dari daftar siswa satu kelas.
    public function tambah(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($request, $ekstrakurikuler);
        $ta = TahunAjaran::aktifOrAbort();
        $data = $request->validate([
            'siswa_id' => 'required|array|min:1',
            'siswa_id.*' => 'integer',
        ], ['siswa_id.required' => 'Pilih minimal satu siswa.', 'siswa_id.min' => 'Pilih minimal satu siswa.']);

        $siswas = Siswa::where('status', 'aktif')->whereNotNull('kelas_id')->whereIn('id', $data['siswa_id'])->get();
        foreach ($siswas as $s) {
            EkstrakurikulerSiswa::firstOrCreate([
                'ekstrakurikuler_id' => $ekstrakurikuler->id,
                'siswa_id' => $s->id,
                'tahun_ajaran_id' => $ta->id,
            ]);
        }

        return redirect()->route('ekskul.edit', ['ekstrakurikuler' => $ekstrakurikuler, 'kelas' => $request->kelas])
            ->with('success', "{$siswas->count()} siswa ditambahkan ke {$ekstrakurikuler->nama}.");
    }

    private function authorizePembina(Request $request, Ekstrakurikuler $ekstrakurikuler): void
    {
        abort_unless($ekstrakurikuler->guru_id === $request->user()->guru?->id, 403, 'Anda bukan pembina ekstrakurikuler ini.');
    }
}
