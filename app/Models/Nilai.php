<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilais';

    protected $fillable = ['siswa_id', 'mapel_id', 'tahun_ajaran_id', 'nilai_akhir', 'capaian_tertinggi', 'capaian_terendah'];

    // Hanya baris yang benar-benar berisi nilai/capaian (form yang disimpan kosong ikut membuat baris kosong).
    public function scopeTerisi($query)
    {
        return $query->where(fn ($q) => $q->whereNotNull('nilai_akhir')
            ->orWhereNotNull('capaian_tertinggi')->orWhereNotNull('capaian_terendah'));
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
