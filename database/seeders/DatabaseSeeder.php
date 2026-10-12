<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\RaporSiswa;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['username' => 'admin'], ['name' => 'Administrator', 'role' => 'admin', 'password' => 'admin123', 'email' => 'admin@sekolah.sch.id']);

        $ta = TahunAjaran::updateOrCreate(['nama' => '2025/2026', 'semester' => 'Ganjil'], [
            'aktif' => true, 'tanggal_rapor' => '2025-12-19', 'kepala_sekolah' => 'Drs. H. Ahmad Fauzi, M.Pd.', 'nip_kepsek' => '196805121994031005',
        ]);
        TahunAjaran::updateOrCreate(['nama' => '2025/2026', 'semester' => 'Genap'], ['aktif' => false, 'kepala_sekolah' => 'Drs. H. Ahmad Fauzi, M.Pd.', 'nip_kepsek' => '196805121994031005']);
        TahunAjaran::updateOrCreate(['nama' => '2026/2027', 'semester' => 'Ganjil'], ['aktif' => false, 'kepala_sekolah' => 'Drs. H. Ahmad Fauzi, M.Pd.', 'nip_kepsek' => '196805121994031005']);

        $jurusan = [];
        foreach ([['TKJ', 'Teknik Komputer dan Jaringan'], ['RPL', 'Rekayasa Perangkat Lunak'], ['AKL', 'Akuntansi dan Keuangan Lembaga']] as [$k, $n]) {
            $jurusan[$k] = Jurusan::updateOrCreate(['kode' => $k], ['nama' => $n]);
        }

        $mapelData = [
            ['PAI', 'Pendidikan Agama dan Budi Pekerti', 'Umum', 1],
            ['PP', 'Pendidikan Pancasila', 'Umum', 2],
            ['BIN', 'Bahasa Indonesia', 'Umum', 3],
            ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'Umum', 4],
            ['SEJ', 'Sejarah', 'Umum', 5],
            ['SB', 'Seni Budaya', 'Umum', 6],
            ['MTK', 'Matematika', 'Kejuruan', 1],
            ['BIG', 'Bahasa Inggris', 'Kejuruan', 2],
            ['INF', 'Informatika', 'Kejuruan', 3],
            ['IPAS', 'Projek Ilmu Pengetahuan Alam dan Sosial', 'Kejuruan', 4],
            ['DDPK', 'Dasar-dasar Program Keahlian', 'Kejuruan', 5],
            ['MULOK', 'Bahasa Daerah', 'Muatan Lokal', 1],
        ];
        $mapels = collect($mapelData)->map(fn ($m) => Mapel::updateOrCreate(['kode' => $m[0]], ['nama' => $m[1], 'kelompok' => $m[2], 'urutan' => $m[3], 'kkm' => 75]));

        $guruData = [
            ['198501012010011001', 'Budi Santoso, S.Kom.', 'L'],
            ['198703152011012002', 'Siti Rahmawati, S.Pd.', 'P'],
            ['199002202015031003', 'Andi Pratama, S.T.', 'L'],
        ];
        $gurus = collect($guruData)->map(function ($g) {
            $u = User::updateOrCreate(['username' => $g[0]], ['name' => $g[1], 'role' => 'guru', 'password' => 'guru123']);

            return Guru::updateOrCreate(['nip' => $g[0]], ['user_id' => $u->id, 'nama' => $g[1], 'jenis_kelamin' => $g[2]]);
        });

        $kelas = Kelas::updateOrCreate(['nama' => 'X TKJ 1'], ['tingkat' => 'X', 'jurusan_id' => $jurusan['TKJ']->id, 'wali_kelas_id' => $gurus[0]->id]);
        Kelas::updateOrCreate(['nama' => 'X RPL 1'], ['tingkat' => 'X', 'jurusan_id' => $jurusan['RPL']->id, 'wali_kelas_id' => $gurus[1]->id]);
        Kelas::updateOrCreate(['nama' => 'XI TKJ 1'], ['tingkat' => 'XI', 'jurusan_id' => $jurusan['TKJ']->id, 'wali_kelas_id' => $gurus[2]->id]);

        foreach ($mapels as $i => $m) {
            Pengajar::updateOrCreate(['mapel_id' => $m->id, 'kelas_id' => $kelas->id], ['guru_id' => $gurus[$i % 3]->id]);
        }

        $siswaData = [
            ['2025001', '0091234501', 'Ahmad Rizky Maulana', 'L'],
            ['2025002', '0091234502', 'Dewi Lestari', 'P'],
            ['2025003', '0091234503', 'Fajar Nugroho', 'L'],
            ['2025004', '0091234504', 'Intan Permatasari', 'P'],
            ['2025005', '0091234505', 'Rizal Hidayat', 'L'],
        ];
        foreach ($siswaData as $idx => $s) {
            $u = User::updateOrCreate(['username' => $s[0]], ['name' => $s[2], 'role' => 'siswa', 'password' => 'siswa123']);
            $siswa = Siswa::updateOrCreate(['nis' => $s[0]], [
                'user_id' => $u->id, 'nisn' => $s[1], 'nama' => $s[2], 'jenis_kelamin' => $s[3], 'kelas_id' => $kelas->id,
                'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2009-0'.($idx + 1).'-15', 'alamat' => 'Jl. Melati No. '.($idx + 1), 'nama_ortu' => 'Orang Tua '.$s[2],
            ]);
            foreach ($mapels as $m) {
                Nilai::updateOrCreate(['siswa_id' => $siswa->id, 'mapel_id' => $m->id, 'tahun_ajaran_id' => $ta->id], [
                    'nilai_akhir' => 72 + (($idx * 7 + $m->id * 3) % 25),
                    'capaian_tertinggi' => 'memahami konsep dasar '.strtolower($m->nama).' dan menerapkannya dalam kegiatan pembelajaran',
                    'capaian_terendah' => 'menganalisis permasalahan lanjutan pada materi '.strtolower($m->nama),
                ]);
            }
            RaporSiswa::updateOrCreate(['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ta->id], [
                'sakit' => $idx % 3, 'izin' => $idx % 2, 'alpa' => 0,
                'catatan_wali' => 'Pertahankan prestasi belajarmu dan tingkatkan kedisiplinan.',
            ]);
        }

        RiwayatKelas::sinkron(Siswa::whereNotNull('kelas_id')->get(), $ta);
    }
}
