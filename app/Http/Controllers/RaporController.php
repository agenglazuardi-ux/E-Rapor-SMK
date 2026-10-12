<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\RaporSiswa;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class RaporController extends Controller
{
    public function saya(Request $request)
    {
        $siswa = $request->user()->siswa ?? abort(404, 'Data siswa tidak ditemukan.');
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->orderByDesc('semester')->get();
        $ta = $request->ta ? TahunAjaran::findOrFail($request->ta) : $this->taDefault($siswa);

        return view('rapor.saya', array_merge($this->data($siswa, $ta), compact('tahunAjarans')));
    }

    public function cetak(Request $request, Siswa $siswa)
    {
        $user = $request->user();
        $siswa->load('kelas');
        $ta = $request->ta ? TahunAjaran::findOrFail($request->ta) : TahunAjaran::aktifOrAbort();
        $data = $this->data($siswa, $ta);
        $nip = $user->guru?->nip;
        $allowed = match ($user->role) {
            'admin' => true,
            'guru' => ($siswa->kelas && $siswa->kelas->wali_kelas_id === $user->guru?->id) || ($nip && $data['info']['wali_nip'] === $nip),
            'siswa' => $siswa->user_id === $user->id,
            default => false,
        };
        abort_unless($allowed, 403, 'Anda tidak berhak mencetak rapor ini.');

        return view('rapor.cetak', $data);
    }

    private function taDefault(Siswa $siswa): TahunAjaran
    {
        $aktif = $siswa->status === 'aktif' ? TahunAjaran::aktif() : null;

        return $aktif
            ?? TahunAjaran::whereIn('id', $siswa->riwayat()->pluck('tahun_ajaran_id'))->orderByDesc('nama')->orderByDesc('semester')->first()
            ?? TahunAjaran::aktifOrAbort();
    }

    private function data(Siswa $siswa, TahunAjaran $ta): array
    {
        $siswa->load('kelas.jurusan', 'kelas.waliKelas');
        $nilais = Nilai::with('mapel')->where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $ta->id)->get()
            ->sortBy(fn ($n) => $n->mapel->urutan)
            ->groupBy(fn ($n) => $n->mapel->kelompok)
            ->sortBy(fn ($g, $k) => array_search($k, ['Umum', 'Kejuruan', 'Muatan Lokal']));
        $rapor = RaporSiswa::firstOrNew(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ta->id]);
        $info = $this->info($siswa, $ta);

        return compact('siswa', 'ta', 'nilais', 'rapor', 'info');
    }

    // Kelas & wali kelas siswa pada semester tersebut (dari riwayat), fallback ke kelas saat ini.
    private function info(Siswa $siswa, TahunAjaran $ta): array
    {
        $r = RiwayatKelas::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $ta->id)->first();
        $k = $siswa->kelas;
        $info = $r ? $r->only(['kelas_nama', 'tingkat', 'jurusan_nama', 'wali_nama', 'wali_nip']) : [
            'kelas_nama' => $k?->nama, 'tingkat' => $k?->tingkat, 'jurusan_nama' => $k?->jurusan?->nama,
            'wali_nama' => $k?->waliKelas?->nama, 'wali_nip' => $k?->waliKelas?->nip,
        ];
        $info['fase'] = $info['tingkat'] ? ($info['tingkat'] === 'X' ? 'E' : 'F') : '-';

        return $info;
    }
}
