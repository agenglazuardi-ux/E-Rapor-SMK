<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'gurus';

    protected $fillable = ['user_id', 'nip', 'nama', 'jenis_kelamin', 'telepon'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengajars()
    {
        return $this->hasMany(Pengajar::class);
    }

    public function ekstrakurikulers()
    {
        return $this->hasMany(Ekstrakurikuler::class);
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }
}
