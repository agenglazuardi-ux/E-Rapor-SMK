<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajarans';

    protected $fillable = ['nama', 'semester', 'aktif', 'tanggal_rapor', 'kepala_sekolah', 'nip_kepsek'];

    protected $casts = ['aktif' => 'boolean', 'tanggal_rapor' => 'date'];

    public static function aktif(): ?self
    {
        return static::where('aktif', true)->first();
    }

    public static function aktifOrAbort(): self
    {
        return static::aktif() ?? abort(403, 'Belum ada Tahun Ajaran aktif. Hubungi admin.');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->nama} - {$this->semester}";
    }
}
