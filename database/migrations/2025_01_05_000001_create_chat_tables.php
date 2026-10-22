<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu percakapan pribadi per pasangan user (user_a_id selalu < user_b_id).
        Schema::create('chat_percakapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_a_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_b_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_a_id', 'user_b_id']);
        });

        Schema::create('chat_pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('percakapan_id')->constrained('chat_percakapan')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->text('isi');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
            $table->index(['percakapan_id', 'id']);
            $table->index(['percakapan_id', 'pengirim_id', 'dibaca_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_pesan');
        Schema::dropIfExists('chat_percakapan');
    }
};
