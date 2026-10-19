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

        return view('wali.absensi', compact('kelas', 'ta', 'siswas', 'rapors'));
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
        ]);

        $siswaIds = $kelas->siswas()->pluck('id')->all();
        foreach ($request->input('data', []) as $siswaId => $row) {
            if (! in_array((int) $siswaId, $siswaIds, true)) {
                continue;
            }
            RaporSiswa::updateOrCreate(
                ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $ta->id],
                [
                    'sakit' => (int) ($row['sakit'] ?? 0),
                    'izin' => (int) ($row['izin'] ?? 0),
                    'alpa' => (int) ($row['alpa'] ?? 0),
                    'catatan_wali' => $row['catatan_wali'] ?? null,
                ]
            );
        }

        RiwayatKelas::sinkron($kelas->siswas()->get(), $ta);

        return back()->with('success', 'Absensi & catatan wali kelas disimpan.');
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