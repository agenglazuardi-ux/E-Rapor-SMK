<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkstrakurikulerSiswa extends Model
{
    protected $table = 'ekstrakurikuler_siswas';

    protected $fillable = ['ekstrakurikuler_id', 'siswa_id', 'tahun_ajaran_id', 'keterangan'];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
