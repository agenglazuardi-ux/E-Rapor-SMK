<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RaporController;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/profil/password', [ProfilController::class, 'edit'])->name('profil.password');
    Route::put('/profil/password', [ProfilController::class, 'update'])->name('profil.password.update');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('tahun-ajaran', Admin\TahunAjaranController::class)->except('show')->parameters(['tahun-ajaran' => 'tahunAjaran']);
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', [Admin\TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');
        Route::resource('jurusan', Admin\JurusanController::class)->except('show');
        Route::resource('guru', Admin\GuruController::class)->except('show');
        Route::resource('kelas', Admin\KelasController::class)->except('show')->parameters(['kelas' => 'kelas']);
        Route::get('siswa/import', [Admin\SiswaController::class, 'importForm'])->name('siswa.import.form');
        Route::post('siswa/import', [Admin\SiswaController::class, 'import'])->name('siswa.import');
        Route::get('siswa/import/template', [Admin\SiswaController::class, 'template'])->name('siswa.import.template');
        Route::get('siswa/export', [Admin\SiswaController::class, 'export'])->name('siswa.export');
        Route::resource('siswa', Admin\SiswaController::class)->except('show');
        Route::get('kenaikan-kelas', [Admin\KenaikanController::class, 'index'])->name('kenaikan.index');
        Route::post('kenaikan-kelas', [Admin\KenaikanController::class, 'proses'])->name('kenaikan.proses');
        Route::resource('mapel', Admin\MapelController::class)->except('show');
        Route::resource('pengajar', Admin\PengajarController::class)->except('show');
        Route::resource('ekstrakurikuler', Admin\EkstrakurikulerController::class)->except('show')->parameters(['ekstrakurikuler' => 'ekstrakurikuler']);
    });

    Route::middleware('role:guru')->group(function () {
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/{pengajar}', [NilaiController::class, 'edit'])->name('nilai.edit');
        Route::post('/nilai/{pengajar}', [NilaiController::class, 'update'])->name('nilai.update');

        Route::get('/ekskul', [EkskulController::class, 'index'])->name('ekskul.index');
        Route::get('/ekskul/{ekstrakurikuler}', [EkskulController::class, 'edit'])->name('ekskul.edit');
        Route::put('/ekskul/{ekstrakurikuler}', [EkskulController::class, 'update'])->name('ekskul.update');
        Route::post('/ekskul/{ekstrakurikuler}/tambah', [EkskulController::class, 'tambah'])->name('ekskul.tambah');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/wali-kelas', [WaliKelasController::class, 'index'])->name('wali.index');
        Route::get('/wali-kelas/{kelas}/absensi', [WaliKelasController::class, 'absensi'])->name('wali.absensi');
        Route::post('/wali-kelas/{kelas}/absensi', [WaliKelasController::class, 'simpanAbsensi'])->name('wali.absensi.simpan');
        Route::get('/wali-kelas/{kelas}/rekap', [WaliKelasController::class, 'rekap'])->name('wali.rekap');

        // Chat pribadi khusus admin & guru.
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/kontak', [ChatController::class, 'kontak'])->name('chat.kontak');
        Route::get('/chat/belum-dibaca', [ChatController::class, 'belumDibaca'])->name('chat.belum');
        Route::get('/chat/{lawan}/pesan', [ChatController::class, 'pesan'])->name('chat.pesan');
        Route::post('/chat/{lawan}/pesan', [ChatController::class, 'kirim'])->middleware('throttle:60,1')->name('chat.kirim');
    });

    Route::get('/rapor-saya', [RaporController::class, 'saya'])->middleware('role:siswa')->name('rapor.saya');
    Route::get('/rapor/{siswa}/cetak', [RaporController::class, 'cetak'])->name('rapor.cetak');
});
