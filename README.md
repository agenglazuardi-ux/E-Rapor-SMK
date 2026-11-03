# E-Rapor SMK — Sistem Informasi Akademik (Laravel 12 + MySQL)

Aplikasi E-Rapor untuk SMK berbasis **Kurikulum Merdeka**: kelola data master, input nilai akhir + deskripsi capaian kompetensi, absensi & catatan wali kelas, ekstrakurikuler, rekap (leger) nilai kelas, kenaikan kelas, serta cetak rapor per semester.

## Fitur

| Peran | Fitur |
|---|---|
| **Admin** | Tahun ajaran & semester (aktifkan), jurusan, guru, kelas + wali kelas, siswa, mata pelajaran (kelompok Umum/Kejuruan/Muatan Lokal + KKM), penugasan guru pengajar, ekstrakurikuler + pembina, rekap & cetak rapor semua kelas |
| **Guru** | Input nilai akhir & capaian kompetensi (tertinggi/terendah) untuk mapel & kelas yang diajar; kelola anggota & keterangan ekstrakurikuler yang dibina |
| **Wali Kelas** (guru) | Input absensi (sakit/izin/alpa) & catatan wali kelas, keputusan naik/tinggal/lulus (semester Genap), leger nilai kelas (jumlah, rata-rata, ranking, nilai < KKM ditandai merah), cetak rapor siswa |
| **Siswa** | Lihat rapor per semester & cetak / simpan PDF (alumni tetap bisa login melihat rapor lama) |
| **Admin & Guru** | Chat internal antar admin/guru (pesan 1-ke-1, penanda belum dibaca) |
| **Admin – Import/Export** | Import ratusan siswa sekaligus dari Excel (.xlsx/.xls/.csv) + template, export data siswa ke Excel |
| **Admin – Kenaikan Kelas** | Naikkan siswa per kelas sekaligus, centang/hapus centang untuk tinggal kelas, kelulusan kelas XII (status Lulus/Alumni), salin penugasan guru ke kelas baru |

Fitur lain: login dengan username (NIP/NIS), pembatasan percobaan login (5x → kunci 15 menit), ganti password, rapor format A4 siap cetak (kop sekolah, identitas, fase E/F, nilai & capaian kompetensi, ekstrakurikuler, absensi, tanda tangan orang tua, wali kelas, kepala sekolah).

## Kebutuhan
- PHP **8.2+** (ekstensi: pdo_mysql, mbstring, xml, curl, zip, **gd**, bcmath, intl). Di XAMPP aktifkan `extension=gd` dan `extension=zip` di `php.ini`.
- Composer 2
- MySQL 5.7+/8 atau MariaDB 10.4+ (XAMPP/Laragon juga bisa)

## Instalasi
```bash
# 1. Ekstrak lalu masuk folder
cd erapor-smk

# 2. Install dependensi
composer install

# 3. Salin konfigurasi
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# 4. Buat database kosong bernama erapor_smk di MySQL / phpMyAdmin,
#    lalu sesuaikan DB_DATABASE / DB_USERNAME / DB_PASSWORD di file .env

# 5. Buat tabel + data contoh
php artisan migrate --seed

# 6. Jalankan
php artisan serve
```
Buka http://127.0.0.1:8000

> **Gunakan `php artisan migrate`, bukan import `database/erapor_smk.sql`.** Berkas SQL tersebut adalah dump lama dan belum memuat tabel/kolom terbaru (ekstrakurikuler, chat, keputusan kenaikan kelas). Karena `SESSION_DRIVER=database`, tabel `sessions` juga harus ada (dibuat oleh migrasi).

## Akun Default (dari seeder)
| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Guru (Wali X TKJ 1) | `198501012010011001` | `guru123` |
| Guru (Wali X RPL 1) | `198703152011012002` | `guru123` |
| Guru | `199002202015031003` | `guru123` |
| Siswa | `2025001` s/d `2025005` | `siswa123` |

Guru/siswa baru yang ditambahkan admin: username = NIP/NIS, password default = NIP/NIS (bila dikosongkan). **Segera ganti password admin dan password default lainnya setelah instalasi.**

## Identitas Sekolah
Atur di `.env`:
```
SEKOLAH_NAMA="SMK Negeri 1 Contoh"
SEKOLAH_NPSN=12345678
SEKOLAH_ALAMAT="Jl. Pendidikan No. 1"
SEKOLAH_KOTA=Jakarta
SEKOLAH_TELEPON="(021) 000000"
SEKOLAH_EMAIL=info@smkn1contoh.sch.id
```
Nama & NIP kepala sekolah serta tanggal rapor diatur per Tahun Ajaran (menu Admin → Tahun Ajaran). Setelah mengubah `.env`, jalankan `php artisan config:clear`.

Logo sekolah memakai `public/logo.png` (kop rapor, sidebar, dan favicon).

## Alur Penggunaan
1. Admin membuat & **mengaktifkan** Tahun Ajaran/Semester.
2. Admin mengisi Jurusan → Guru → Kelas (pilih wali kelas) → Siswa → Mata Pelajaran (KKM) → Ekstrakurikuler (pilih guru pembina).
3. Admin menugaskan **Guru Pengajar** (guru – mapel – kelas).
4. Guru login → **Input Nilai** (nilai akhir 0–100 + capaian kompetensi).
5. Pembina ekstrakurikuler → **Ekstrakurikuler** → tambah anggota & isi keterangan.
6. Wali kelas → **Kelas Perwalian** → Absensi & Catatan, Rekap Nilai, Cetak Rapor.
7. Siswa login → **Rapor Saya** → Cetak / Simpan PDF (lewat dialog print browser).

## Import Siswa dari Excel
Menu **Admin → Import Siswa (Excel)** → *Unduh Template* → isi data (satu baris = satu siswa) → unggah.
- Kolom: NIS, NISN, Nama Lengkap, L/P, Kelas, Tempat Lahir, Tanggal Lahir, Alamat, Nama Orang Tua/Wali.
- Wajib: NIS, Nama, L/P. Nama kelas harus sama dengan data Kelas (lihat sheet *Daftar Kelas* di template).
- Akun login dibuat otomatis (username & password = NIS). Jika NIS sudah ada, datanya diperbarui (bisa dimatikan).
- Baris yang salah tidak menggagalkan seluruh import; daftar barisnya ditampilkan agar bisa diperbaiki.
- Menu **Siswa → Export Excel** mengunduh data siswa sesuai filter.

## Ekstrakurikuler
- Admin membuat daftar ekstrakurikuler dan menunjuk guru pembina.
- Pembina menambah anggota per tahun ajaran dan mengisi keterangan (maks. 500 karakter) untuk tiap siswa; anggota bisa dikeluarkan dengan mencentangnya.
- Ekstrakurikuler yang diikuti tampil di rapor siswa pada semester tersebut.

## Chat Internal
Menu **Chat** tersedia untuk admin dan guru (siswa tidak memiliki akses).
- Percakapan 1-ke-1 antar admin/guru, dengan penanda pesan belum dibaca di menu.
- Halaman chat memperbarui pesan dengan polling berkala; pesan disimpan sebagai teks biasa di database (`chat_percakapan`, `chat_pesan`), jadi jangan dipakai untuk informasi rahasia.
- Pengiriman dibatasi 60 pesan per menit, maksimal 2000 karakter per pesan.

## Kenaikan Kelas & Kelulusan (akhir tahun ajaran)
1. Pastikan semua nilai & rapor semester Genap sudah selesai. Wali kelas mengisi **keputusan** (naik/tinggal/lulus) di halaman Absensi & Catatan semester Genap; ini menjadi pilihan awal saat proses kenaikan.
2. Buat kelas tingkat berikutnya bila belum ada (mis. XI TKJ 1) beserta wali kelasnya. **Jangan mengganti nama kelas lama** (kelas tujuan keputusan wali kelas disimpan sebagai nama kelas).
3. **Tahun Ajaran** → buat & **Aktifkan** tahun ajaran baru semester **Ganjil** (mis. 2026/2027 Ganjil).
   Saat diaktifkan, riwayat kelas semester lama otomatis dibekukan. Kenaikan kelas hanya bisa diproses saat semester aktif adalah **Ganjil**; jika yang aktif Genap, proses ditolak.
4. **Kenaikan Kelas** → pilih kelas asal → hapus centang siswa yang tinggal kelas → pilih kelas tujuan
   (atau **LULUS** untuk kelas XII) → opsional *salin penugasan guru* → **Proses**.
5. Ulangi untuk setiap kelas (mulai dari kelas XII agar kelas tujuan kosong lebih dulu).

Kenaikan ditolak jika semester aktif sudah berisi nilai siswa tersebut (agar riwayat tidak tertimpa), jadi lakukan kenaikan **sebelum** guru mulai menginput nilai Ganjil.
Rapor & rekap semester lama selalu menampilkan kelas, fase, dan wali kelas **pada semester itu** (tabel `riwayat_kelas`).
Status siswa juga bisa diubah manual (Aktif / Lulus / Pindah) di form Edit Siswa.

## Update dari versi sebelumnya
Salin file baru, lalu jalankan:
```bash
composer install
php artisan migrate
php artisan config:clear
```

## Pemecahan Masalah
| Pesan / gejala | Penyebab & solusi |
|---|---|
| `Table '...sessions' doesn't exist` (SQLSTATE 42S02) | Tabel belum dibuat. Jalankan `php artisan migrate`. Jika error 1932 ("doesn't exist in engine") muncul di XAMPP, tabel InnoDB rusak: hapus database lalu buat ulang dan jalankan `php artisan migrate --seed`. Sementara bisa ganti `SESSION_DRIVER=file` dan `CACHE_STORE=file` di `.env`. |
| Kenaikan kelas ditolak: "Aktifkan dulu Tahun Ajaran baru (semester Ganjil)" | Tahun ajaran aktif masih Genap. Aktifkan tahun ajaran Ganjil yang baru lebih dulu. |
| Perubahan `.env` tidak berpengaruh | Jalankan `php artisan config:clear`. |

## Struktur Penting
```
app/Http/Controllers/Admin/*                       CRUD data master
app/Http/Controllers/NilaiController.php           input nilai guru
app/Http/Controllers/WaliKelasController.php       absensi, catatan, keputusan, leger
app/Http/Controllers/RaporController.php           rapor siswa & cetak
app/Http/Controllers/EkskulController.php          anggota & keterangan ekstrakurikuler (pembina)
app/Http/Controllers/ChatController.php            chat admin/guru
app/Http/Controllers/Admin/KenaikanController.php  kenaikan kelas & kelulusan
app/Services/SiswaExcel.php                        import/export/template Excel
app/Models/RiwayatKelas.php                        riwayat kelas per semester
app/Http/Middleware/RoleMiddleware.php             hak akses per peran
app/Models/*                                       Eloquent model
config/erapor.php                                  identitas sekolah
database/migrations/*                              skema tabel MySQL
database/seeders/DatabaseSeeder.php                data contoh
resources/views/**                                 tampilan Blade (Bootstrap 5)
routes/web.php                                     daftar route
```

## Tabel Database
`users`, `tahun_ajarans`, `jurusans`, `gurus`, `kelas`, `siswas`, `mapels`, `pengajars`, `nilais`, `rapor_siswas` (absensi, catatan wali, keputusan & kelas tujuan), `riwayat_kelas` (kelas & wali per semester), `ekstrakurikulers`, `ekstrakurikuler_siswas`, `chat_percakapan`, `chat_pesan`, serta tabel bawaan Laravel (`sessions`, `cache`, `jobs`).

## Catatan Keamanan
- Jangan mengunggah file `.env` ke repositori atau membagikannya; gunakan `.env.example` sebagai acuan.
- Untuk penggunaan sebenarnya (production): set `APP_ENV=production`, `APP_DEBUG=false`, dan gunakan user database khusus (bukan `root`).
- Ganti semua password default dari seeder.

## BEST REGARDS
## MUHAMMAD AGENG LAZUARDI
