<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatPesan extends Model
{
    protected $table = 'chat_pesan';

    protected $fillable = ['percakapan_id', 'pengirim_id', 'isi', 'dibaca_at'];

    protected function casts(): array
    {
        return ['dibaca_at' => 'datetime'];
    }

    public function percakapan()
    {
        return $this->belongsTo(ChatPercakapan::class, 'percakapan_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}
