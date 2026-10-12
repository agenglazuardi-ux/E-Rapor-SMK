<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswas';

    protected $fillable = ['user_id', 'kelas_id', 'status', 'tahun_lulus', 'nis', 'nisn', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'nama_ortu'];

    protected $casts = ['tanggal_lahir' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatKelas::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }
}
