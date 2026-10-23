<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\RaporSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $ta = TahunAjaran::aktif();
        $data = ['ta' => $ta];

        if ($user->role === 'admin') {
            $data['stats'] = [
                'Siswa Aktif' => Siswa::where('status', 'aktif')->count(),
                'Guru' => Guru::count(),
                'Kelas' => Kelas::count(),
                'Mata Pelajaran' => Mapel::count(),
            ];
            $data['nilaiTerisi'] = $ta ? Nilai::where('tahun_ajaran_id', $ta->id)->whereNotNull('nilai_akhir')->count() : 0;
        } elseif ($user->role === 'guru') {
            $guru = $user->guru;
            $data['pengajars'] = $guru?->pengajars()->with('mapel', 'kelas')->get() ?? collect();
            $data['kelasWali'] = $guru?->kelasWali()->withCount('siswas')->get() ?? collect();

            // Pengingat: jumlah siswa di kelas perwalian yang catatan wali kelasnya belum diisi (semester aktif).
            if ($ta && $data['kelasWali']->isNotEmpty()) {
                $siswas = Siswa::whereIn('kelas_id', $data['kelasWali']->pluck('id'))->get(['id', 'kelas_id']);
                $sudahIsi = RaporSiswa::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $siswas->pluck('id'))
                    ->whereNotNull('catatan_wali')->where('catatan_wali', '!=', '')->pluck('siswa_id')->flip();
                foreach ($data['kelasWali'] as $k) {
                    $k->catatan_belum = $siswas->where('kelas_id', $k->id)->filter(fn ($s) => ! $sudahIsi->has($s->id))->count();
                }
            }
        } else {
            $siswa = $user->siswa()->with('kelas.waliKelas')->first();
            $data['siswa'] = $siswa;
            $data['rataRata'] = ($siswa && $ta) ? round(Nilai::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $ta->id)->avg('nilai_akhir') ?? 0, 2) : 0;
        }

        return view('dashboard', $data);
    }
}