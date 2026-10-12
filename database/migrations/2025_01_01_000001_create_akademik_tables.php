<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->boolean('aktif')->default(false);
            $table->date('tanggal_rapor')->nullable();
            $table->string('kepala_sekolah')->nullable();
            $table->string('nip_kepsek', 30)->nullable();
            $table->timestamps();
            $table->unique(['nama', 'semester']);
        });

        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip', 30)->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('telepon', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->foreignId('jurusan_id')->constrained('jurusans')->restrictOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ortu')->nullable();
            $table->timestamps();
        });

        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 15)->unique();
            $table->string('nama');
            $table->enum('kelompok', ['Umum', 'Kejuruan', 'Muatan Lokal']);
            $table->unsignedTinyInteger('kkm')->default(75);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('pengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['mapel_id', 'kelas_id']);
        });

        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai_akhir')->nullable();
            $table->text('capaian_tertinggi')->nullable();
            $table->text('capaian_terendah')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'mapel_id', 'tahun_ajaran_id']);
        });

        Schema::create('rapor_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('alpa')->default(0);
            $table->text('catatan_wali')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });
    }

    public function down(): void
    {
        foreach (['rapor_siswas', 'nilais', 'pengajars', 'mapels', 'siswas', 'kelas', 'gurus', 'jurusans', 'tahun_ajarans'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
