<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    protected $table = 'ekstrakurikulers';

    protected $fillable = ['nama', 'guru_id'];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function anggotas()
    {
        return $this->hasMany(EkstrakurikulerSiswa::class);
    }
}
