# E-Rapor SMK — Sistem Informasi Akademik (Laravel 12 + MySQL)

Aplikasi E-Rapor untuk SMK berbasis **Kurikulum Merdeka**: kelola data master, input nilai akhir + deskripsi capaian kompetensi, absensi & catatan wali kelas, rekap (leger) nilai kelas, serta cetak rapor per semester.

## Fitur
| Peran | Fitur |
|---|---|
| **Admin** | Tahun ajaran & semester (aktifkan), jurusan, guru, kelas + wali kelas, siswa, mata pelajaran (kelompok Umum/Kejuruan/Muatan Lokal + KKM), penugasan guru pengajar, rekap & cetak rapor semua kelas |
| **Guru** | Input nilai akhir & capaian kompetensi (tertinggi/terendah) untuk mapel & kelas yang diajar |
| **Wali Kelas** (guru) | Input absensi (sakit/izin/alpa) & catatan wali kelas, leger nilai kelas (jumlah, rata-rata, ranking, nilai < KKM ditandai merah), cetak rapor siswa |
| **Siswa** | Lihat rapor per semester & cetak / simpan PDF (alumni tetap bisa login melihat rapor lama) |
| **Admin – Import/Export** | Import ratusan siswa sekaligus dari Excel (.xlsx/.xls/.csv) + template, export data siswa ke Excel |
| **Admin – Kenaikan Kelas** | Naikkan siswa per kelas sekaligus, centang/hapus centang untuk tinggal kelas, kelulusan kelas XII (status Lulus/Alumni), salin penugasan guru ke kelas baru |

Fitur lain: login dengan username (NIP/NIS), pembatasan percobaan login (5x → kunci 15 menit), ganti password, rapor format A4 siap cetak (kop sekolah, identitas, fase E/F, tanda tangan orang tua, wali kelas, kepala sekolah).

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
#    lalu sesuaikan DB_USERNAME / DB_PASSWORD di file .env

# 5. Buat tabel + data contoh
php artisan migrate --seed

# 6. Jalankan
php artisan serve
```
Buka http://127.0.0.1:8000

> Alternatif tanpa migrate: import `database/erapor_smk.sql` lewat phpMyAdmin (sudah berisi struktur + data contoh), lalu lanjut langkah 6.

## Akun Default (dari seeder)
| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Guru (Wali X TKJ 1) | `198501012010011001` | `guru123` |
| Guru (Wali X RPL 1) | `198703152011012002` | `guru123` |
| Guru | `199002202015031003` | `guru123` |
| Siswa | `2025001` s/d `2025005` | `siswa123` |

Guru/siswa baru yang ditambahkan admin: username = NIP/NIS, password default = NIP/NIS (bila dikosongkan). **Segera ganti password admin setelah instalasi.**

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
Nama & NIP kepala sekolah serta tanggal rapor diatur per Tahun Ajaran (menu Admin → Tahun Ajaran).

## Alur Penggunaan
1. Admin membuat & **mengaktifkan** Tahun Ajaran/Semester.
2. Admin mengisi Jurusan → Guru → Kelas (pilih wali kelas) → Siswa → Mata Pelajaran (KKM).
3. Admin menugaskan **Guru Pengajar** (guru – mapel – kelas).
4. Guru login → **Input Nilai** (nilai akhir 0–100 + capaian kompetensi).
5. Wali kelas → **Kelas Perwalian** → Absensi & Catatan, Rekap Nilai, Cetak Rapor.
6. Siswa login → **Rapor Saya** → Cetak / Simpan PDF (lewat dialog print browser).

## Import Siswa dari Excel
Menu **Admin → Import Siswa (Excel)** → *Unduh Template* → isi data (satu baris = satu siswa) → unggah.
- Kolom: NIS, NISN, Nama Lengkap, L/P, Kelas, Tempat Lahir, Tanggal Lahir, Alamat, Nama Orang Tua/Wali.
- Wajib: NIS, Nama, L/P. Nama kelas harus sama dengan data Kelas (lihat sheet *Daftar Kelas* di template).
- Akun login dibuat otomatis (username & password = NIS). Jika NIS sudah ada, datanya diperbarui (bisa dimatikan).
- Baris yang salah tidak menggagalkan seluruh import; daftar barisnya ditampilkan agar bisa diperbaiki.
- Menu **Siswa → Export Excel** mengunduh data siswa sesuai filter.

## Kenaikan Kelas & Kelulusan (akhir tahun ajaran)
1. Pastikan semua nilai & rapor semester Genap sudah selesai.
2. Buat kelas tingkat berikutnya bila belum ada (mis. XI TKJ 1) beserta wali kelasnya. **Jangan mengganti nama kelas lama.**
3. **Tahun Ajaran** → buat & **Aktifkan** tahun ajaran baru semester **Ganjil** (mis. 2026/2027 Ganjil).
   Saat diaktifkan, riwayat kelas semester lama otomatis dibekukan.
4. **Kenaikan Kelas** → pilih kelas asal → hapus centang siswa yang tinggal kelas → pilih kelas tujuan
   (atau **LULUS** untuk kelas XII) → opsional *salin penugasan guru* → **Proses**.
5. Ulangi untuk setiap kelas (mulai dari kelas XII agar kelas tujuan kosong lebih dulu).

Kenaikan ditolak jika semester aktif sudah berisi nilai siswa tersebut (agar riwayat tidak tertimpa).
Rapor & rekap semester lama selalu menampilkan kelas, fase, dan wali kelas **pada semester itu** (tabel `riwayat_kelas`).
Status siswa juga bisa diubah manual (Aktif / Lulus / Pindah) di form Edit Siswa.

## Update dari versi sebelumnya
Salin file baru, lalu jalankan:
```bash
composer install
php artisan migrate
```

## Struktur Penting
```
app/Http/Controllers/Admin/*                   CRUD data master
app/Http/Controllers/NilaiController.php       input nilai guru
app/Http/Controllers/WaliKelasController.php   absensi, catatan, leger
app/Http/Controllers/RaporController.php       rapor siswa & cetak
app/Http/Controllers/Admin/KenaikanController.php  kenaikan kelas & kelulusan
app/Services/SiswaExcel.php                    import/export/template Excel
app/Models/RiwayatKelas.php                    riwayat kelas per semester
app/Http/Middleware/RoleMiddleware.php         hak akses per peran
app/Models/*                                   Eloquent model
database/migrations/*                          skema tabel MySQL
database/seeders/DatabaseSeeder.php            data contoh
resources/views/**                             tampilan Blade (Bootstrap 5)
routes/web.php                                 daftar route
```

## Tabel Database
`users`, `tahun_ajarans`, `jurusans`, `gurus`, `kelas`, `siswas`, `mapels`, `pengajars`, `nilais`, `rapor_siswas` (absensi + catatan wali), `riwayat_kelas` (kelas & wali per semester), serta tabel bawaan Laravel (`sessions`, `cache`, `jobs`).


## BEST REGARDS
## MUHAMMAD AGENG LAZUARDI
