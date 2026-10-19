<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekstrakurikulers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ekstrakurikuler_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->string('keterangan', 500)->nullable();
            $table->timestamps();
            // Nama indeks dipendekkan karena nama otomatis melebihi batas 64 karakter MySQL.
            $table->unique(['ekstrakurikuler_id', 'siswa_id', 'tahun_ajaran_id'], 'ekskul_siswa_ta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_siswas');
        Schema::dropIfExists('ekstrakurikulers');
    }
};
