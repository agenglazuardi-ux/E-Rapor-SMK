<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $ta = TahunAjaran::aktif();
        $pengajars = Pengajar::with('mapel', 'kelas.siswas')->where('guru_id', $request->user()->guru?->id ?? 0)->get();

        $terisi = [];
        if ($ta) {
            foreach ($pengajars as $p) {
                $terisi[$p->id] = Nilai::where('mapel_id', $p->mapel_id)->where('tahun_ajaran_id', $ta->id)
                    ->whereNotNull('nilai_akhir')->whereIn('siswa_id', $p->kelas->siswas()->pluck('id'))->count();
            }
        }

        return view('guru.nilai.index', compact('pengajars', 'ta', 'terisi'));
    }

    public function edit(Request $request, Pengajar $pengajar)
    {
        $this->authorizePengajar($request, $pengajar);
        $ta = TahunAjaran::aktifOrAbort();
        $pengajar->load('mapel', 'kelas');
        $siswas = $pengajar->kelas->siswas;
        $nilais = Nilai::where('mapel_id', $pengajar->mapel_id)->where('tahun_ajaran_id', $ta->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))->get()->keyBy('siswa_id');

        return view('guru.nilai.input', compact('pengajar', 'ta', 'siswas', 'nilais'));
    }

    public function update(Request $request, Pengajar $pengajar)
    {
        $this->authorizePengajar($request, $pengajar);
        $ta = TahunAjaran::aktifOrAbort();
        $request->validate([
            'nilai' => 'array',
            'nilai.*.nilai_akhir' => 'nullable|integer|min:0|max:100',
            'nilai.*.capaian_tertinggi' => 'nullable|string|max:1000',
            'nilai.*.capaian_terendah' => 'nullable|string|max:1000',
        ], [], ['nilai.*.nilai_akhir' => 'nilai akhir']);

        $siswaIds = $pengajar->kelas->siswas()->pluck('id')->all();
        foreach ($request->input('nilai', []) as $siswaId => $row) {
            if (! in_array((int) $siswaId, $siswaIds, true)) {
                continue;
            }
            Nilai::updateOrCreate(
                ['siswa_id' => $siswaId, 'mapel_id' => $pengajar->mapel_id, 'tahun_ajaran_id' => $ta->id],
                [
                    'nilai_akhir' => $row['nilai_akhir'] ?? null,
                    'capaian_tertinggi' => $row['capaian_tertinggi'] ?? null,
                    'capaian_terendah' => $row['capaian_terendah'] ?? null,
                ]
            );
        }

        \App\Models\RiwayatKelas::sinkron($pengajar->kelas->siswas()->get(), $ta);

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    private function authorizePengajar(Request $request, Pengajar $pengajar): void
    {
        abort_unless($pengajar->guru_id === $request->user()->guru?->id, 403, 'Anda bukan pengajar mapel ini.');
    }
}
