<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilais';

    protected $fillable = ['siswa_id', 'mapel_id', 'tahun_ajaran_id', 'nilai_akhir', 'capaian_tertinggi', 'capaian_terendah'];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
