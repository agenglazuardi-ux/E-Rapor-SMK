<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = ['nama', 'tingkat', 'jurusan_id', 'wali_kelas_id'];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class)->orderBy('nama');
    }

    public function pengajars()
    {
        return $this->hasMany(Pengajar::class);
    }
}
