<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KenaikanController extends Controller
{
    private const TINGKAT_BERIKUTNYA = ['X' => 'XI', 'XI' => 'XII'];

    public function index(Request $request)
    {
        $ta = TahunAjaran::aktif();
        $kelasList = Kelas::with('jurusan', 'waliKelas')->withCount('siswas')->orderBy('tingkat')->orderBy('nama')->get();
        $asal = $request->kelas_asal ? Kelas::with('siswas', 'pengajars')->find($request->kelas_asal) : null;

        return view('admin.kenaikan.index', compact('ta', 'kelasList', 'asal'));
    }

    public function proses(Request $request)
    {
        $ta = TahunAjaran::aktifOrAbort();
        if ($ta->semester !== 'Ganjil') {
            return back()->with('error', 'Aktifkan dulu Tahun Ajaran baru (semester Ganjil) sebelum memproses kenaikan kelas.');
        }
        $data = $request->validate([
            'kelas_asal' => 'required|exists:kelas,id',
            'tujuan' => 'required',
            'naik' => 'array',
            'naik.*' => 'integer',
        ], ['tujuan.required' => 'Pilih kelas tujuan atau Lulus.']);

        $asal = Kelas::with('pengajars')->findOrFail($data['kelas_asal']);
        $lulus = $data['tujuan'] === 'lulus';
        if ($lulus && $asal->tingkat !== 'XII') {
            return back()->with('error', 'Kelulusan hanya untuk siswa kelas XII.');
        }
        $tujuan = null;
        if (! $lulus) {
            $tingkatTujuan = self::TINGKAT_BERIKUTNYA[$asal->tingkat] ?? null;
            if (! $tingkatTujuan) {
                return back()->with('error', 'Siswa kelas XII hanya dapat diluluskan, bukan dinaikkan ke kelas lain.');
            }
            $tujuan = Kelas::where('id', '!=', $asal->id)->findOrFail($data['tujuan']);
            if ($tujuan->tingkat !== $tingkatTujuan) {
                return back()->with('error', "Siswa kelas {$asal->tingkat} hanya dapat dinaikkan ke kelas tingkat {$tingkatTujuan}.");
            }
        }
        $siswas = $asal->siswas()->get();
        $naikIds = collect($data['naik'] ?? [])->map(fn ($v) => (int) $v)->intersect($siswas->pluck('id'));

        if ($naikIds->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang dicentang.');
        }

        $sudahDinilai = Siswa::whereIn('id', Nilai::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $naikIds)->pluck('siswa_id'))->pluck('nama');
        if ($sudahDinilai->isNotEmpty()) {
            return back()->with('error', "Tahun ajaran aktif ({$ta->label}) sudah berisi nilai untuk: {$sudahDinilai->join(', ')}. Kenaikan kelas harus diproses di awal tahun ajaran baru, sebelum guru menginput nilai.");
        }

        DB::transaction(function () use ($siswas, $naikIds, $lulus, $tujuan, $ta, $asal, $request) {
            RiwayatKelas::bekukan($siswas);
            $perubahan = $lulus
                ? ['kelas_id' => null, 'status' => 'lulus', 'tahun_lulus' => (int) substr($ta->nama, 0, 4)]
                : ['kelas_id' => $tujuan->id];
            Siswa::whereIn('id', $naikIds)->update($perubahan);

            if ($lulus) {
                RiwayatKelas::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $naikIds)->delete();
            }
            RiwayatKelas::sinkron(Siswa::whereIn('id', $siswas->pluck('id'))->get(), $ta);

            if (! $lulus && $request->boolean('salin_pengajar')) {
                foreach ($asal->pengajars as $p) {
                    Pengajar::firstOrCreate(['mapel_id' => $p->mapel_id, 'kelas_id' => $tujuan->id], ['guru_id' => $p->guru_id]);
                }
            }
        });

        $tinggal = $siswas->count() - $naikIds->count();
        $pesan = $lulus
            ? "{$naikIds->count()} siswa dinyatakan LULUS"
            : "{$naikIds->count()} siswa naik ke {$tujuan->nama}";
        $pesan .= $tinggal ? ", {$tinggal} siswa tinggal di {$asal->nama}." : '.';

        return redirect()->route('admin.kenaikan.index')->with('success', $pesan);
    }
}