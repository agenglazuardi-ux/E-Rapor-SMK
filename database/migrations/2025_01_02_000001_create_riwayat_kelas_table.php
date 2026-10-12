<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('kelas_nama', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('jurusan_nama')->nullable();
            $table->string('wali_nama')->nullable();
            $table->string('wali_nip', 30)->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'lulus', 'pindah'])->default('aktif')->after('kelas_id');
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
        Schema::table('siswas', fn (Blueprint $table) => $table->dropColumn(['status', 'tahun_lulus']));
    }
};
