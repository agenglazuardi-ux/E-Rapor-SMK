<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatPercakapan extends Model
{
    protected $table = 'chat_percakapan';

    protected $fillable = ['user_a_id', 'user_b_id'];

    public function pesan()
    {
        return $this->hasMany(ChatPesan::class, 'percakapan_id');
    }

    // Urutan pasangan dinormalisasi agar A-B dan B-A menunjuk percakapan yang sama.
    private static function urut(int $x, int $y): array
    {
        return $x < $y ? [$x, $y] : [$y, $x];
    }

    public static function cari(int $x, int $y): ?self
    {
        [$a, $b] = self::urut($x, $y);

        return static::where('user_a_id', $a)->where('user_b_id', $b)->first();
    }

    public static function antara(int $x, int $y): self
    {
        [$a, $b] = self::urut($x, $y);

        return static::firstOrCreate(['user_a_id' => $a, 'user_b_id' => $b]);
    }
}
