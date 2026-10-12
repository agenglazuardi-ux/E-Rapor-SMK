<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaporSiswa extends Model
{
    protected $table = 'rapor_siswas';

    protected $fillable = ['siswa_id', 'tahun_ajaran_id', 'sakit', 'izin', 'alpa', 'catatan_wali'];
}
