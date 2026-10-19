<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaporSiswa extends Model
{
    protected $table = 'rapor_siswas';

    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'sakit', 'izin', 'alpa', 'catatan_wali'];

    // Hanya baris yang berisi absensi (> 0) atau catatan wali; baris default berisi 0 tidak dihitung.
    public function scopeTerisi($query)
    {
        return $query->where(fn ($q) => $q->where('sakit', '>', 0)->orWhere('izin', '>', 0)
            ->orWhere('alpa', '>', 0)->orWhereNotNull('catatan_wali'));
    }
}
