<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';

    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'kelas_id', 'kelas_nama', 'tingkat', 'jurusan_nama', 'wali_nama', 'wali_nip'];

    public static function snapshot(Kelas $kelas): array
    {
        $kelas->loadMissing('jurusan', 'waliKelas');

        return [
            'kelas_id' => $kelas->id,
            'kelas_nama' => $kelas->nama,
            'tingkat' => $kelas->tingkat,
            'jurusan_nama' => $kelas->jurusan?->nama,
            'wali_nama' => $kelas->waliKelas?->nama,
            'wali_nip' => $kelas->waliKelas?->nip,
        ];
    }

    // Simpan kelas siswa saat ini untuk tahun ajaran (default: aktif).
    public static function sinkron(iterable $siswas, ?TahunAjaran $ta = null, bool $timpa = true): void
    {
        $ta ??= TahunAjaran::aktif();
        if (! $ta) {
            return;
        }
        foreach ($siswas as $s) {
            $s->loadMissing('kelas');
            if (! $s->kelas) {
                continue;
            }
            $key = ['siswa_id' => $s->id, 'tahun_ajaran_id' => $ta->id];
            $timpa ? static::updateOrCreate($key, static::snapshot($s->kelas)) : static::firstOrCreate($key, static::snapshot($s->kelas));
        }
    }

    // Buat riwayat untuk semester yang sudah punya nilai/absensi tapi belum tercatat kelasnya.
    public static function bekukan(iterable $siswas): void
    {
        $siswas = collect($siswas)->filter(fn ($s) => $s->kelas_id);
        if ($siswas->isEmpty()) {
            return;
        }
        $ids = $siswas->pluck('id');
        // toBase(): hasil get() adalah Eloquent Collection; diff() miliknya mengharuskan isi berupa Model,
        // sedangkan di sini isinya string "siswa-tahun". Ubah ke Collection biasa agar tidak error.
        $pairs = Nilai::whereIn('siswa_id', $ids)->select('siswa_id', 'tahun_ajaran_id')->distinct()->get()->toBase()
            ->concat(RaporSiswa::whereIn('siswa_id', $ids)->select('siswa_id', 'tahun_ajaran_id')->get()->toBase())
            ->map(fn ($r) => $r->siswa_id.'-'.$r->tahun_ajaran_id)->unique();
        $ada = static::whereIn('siswa_id', $ids)->get()->toBase()->map(fn ($r) => $r->siswa_id.'-'.$r->tahun_ajaran_id);
        $byId = $siswas->keyBy('id');
        foreach ($pairs->diff($ada) as $pair) {
            [$siswaId, $taId] = explode('-', $pair);
            $s = $byId[(int) $siswaId];
            $s->loadMissing('kelas');
            static::create(['siswa_id' => $s->id, 'tahun_ajaran_id' => (int) $taId] + static::snapshot($s->kelas));
        }
    }
}