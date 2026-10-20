<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor {{ $siswa->nama }} - {{ $ta->label }}</title>
    <style>
        @font-face { font-family: 'RaporFont'; font-weight: 400; font-style: normal; src: local('DejaVu Sans'), local('DejaVuSans'), url('{{ asset('fonts/DejaVuSans.ttf') }}') format('truetype'); }
        @font-face { font-family: 'RaporFont'; font-weight: 700; font-style: normal; src: local('DejaVu Sans Bold'), local('DejaVuSans-Bold'), url('{{ asset('fonts/DejaVuSans-Bold.ttf') }}') format('truetype'); }
        @font-face { font-family: 'RaporFont'; font-weight: 700; font-style: italic; src: local('DejaVu Sans Bold Oblique'), local('DejaVuSans-BoldOblique'), url('{{ asset('fonts/DejaVuSans-BoldOblique.ttf') }}') format('truetype'); }
        html { font-family: 'RaporFont', 'DejaVu Sans', Verdana, Tahoma, sans-serif; }
        /* Footer setiap halaman (muncul saat cetak/PDF di Chrome/Edge): kiri = nama siswa - kelas, kanan = nama e-rapor + halaman. */
        @page {
            size: A4; margin: 15mm 15mm 18mm;
            @bottom-left { content: "{!! str_replace(['"', chr(92), '<', '>', chr(13), chr(10)], '', mb_strtoupper($siswa->nama).' - '.($info['kelas_nama'] ?? '-')) !!}"; font: italic 700 9px 'RaporFont', 'DejaVu Sans', sans-serif; color: #000; }
            @bottom-right { content: "{!! str_replace(['"', chr(92), '<', '>', chr(13), chr(10)], '', 'e-Rapor '.config('erapor.sekolah.nama')) !!}  -  Halaman " counter(page); font: italic 700 9px 'RaporFont', 'DejaVu Sans', sans-serif; color: #000; }
        }
        body { font-family: 'RaporFont', 'DejaVu Sans', Verdana, Tahoma, sans-serif; font-size: 10.5px; color: #000; margin: 0; background: #e9e6df; }
        .sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 20px auto; padding: 15mm; box-sizing: border-box; box-shadow: 0 1px 6px rgba(0,0,0,.25); }
        .kop { display: flex; align-items: center; gap: 14px; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px; }
        .kop .logo { height: 72px; width: auto; max-width: 90px; object-fit: contain; flex-shrink: 0; }
        .kop h1 { font-size: 18px; margin: 0; text-transform: uppercase; } .kop p { margin: 2px 0; font-size: 11px; }
        h2.judul { text-align: center; font-size: 14px; margin: 6px 0 12px; text-transform: uppercase; letter-spacing: 1px; }
        .identitas { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 16px; }
        .identitas table, .identitas-mini { border-collapse: collapse; }
        .identitas td, .identitas-mini td { padding: 3px 4px 3px 0; vertical-align: top; }
        .identitas .kiri td:first-child { width: 150px; } .identitas .kanan td:first-child { width: 105px; }
        .identitas-mini { margin-bottom: 18px; } .identitas-mini td:first-child { width: 150px; }
        td.sp { width: 12px; }
        .rapor-sec { font-weight: bold; margin: 14px 0 6px; }
        .rapor-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .rapor-table th, .rapor-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
        .rapor-table th { background: #fff; text-align: center; vertical-align: middle; }
        .rapor-table .kelompok td { background: #fff; font-weight: bold; }
        .rapor-absen { width: 50%; } .rapor-absen td:first-child { width: 55%; }
        .rapor-box { border: 1px solid #000; padding: 6px 8px; margin-bottom: 14px; }
        .rapor-catatan { text-align: center; min-height: 40px; }
        .c { text-align: center; } .merah { color: #b00; } .desc { text-align: justify; line-height: 1.2; } .mt-1 { margin-top: 3px; }
        .ttd-blok { margin-top: 28px; }
        .ttd { display: grid; grid-template-columns: 60% 40%;font-weight: bold; }
        .ttd .space, .ttd-kepsek .space { height: 72px; } .ttd .nama, .ttd-kepsek .nama { font-weight: bold; text-decoration: underline; } .ttd .nip, .ttd-kepsek .nip { font-weight: bold; }
        .ttd .garis { border-bottom: 1px dotted #000; width: 55%; }
        .ttd-kepsek { width: 30%; margin: 54px 245px; text-align: left;font-weight: bold;}
        .footer-layar { display: flex; justify-content: space-between; gap: 12px; margin-top: 28px; padding-top: 6px; font-style: italic; font-weight: bold; font-size: 9px; }
        .toolbar { text-align: center; margin: 16px; font-family: sans-serif; }
        .toolbar button { background: #0f3d2e; color: #fff; border: 0; padding: 10px 22px; border-radius: 8px; font-size: 14px; cursor: pointer; }
        @media screen { .sheet { display: flex; flex-direction: column; } .footer-layar { margin-top: auto; } }
        @media print {
            .footer-layar { display: none; }
            body { background: #fff; } .sheet { margin: 0; padding: 0; width: auto; min-height: auto; box-shadow: none; } .toolbar { display: none; }
            /* Bagian B dst. (ekskul, ketidakhadiran, catatan, tanda tangan) dimulai di halaman baru. */
            .sheet + .sheet { break-before: page; page-break-before: always; }
            .rapor-table thead { display: table-header-group; }
            .rapor-table tr, .rapor-box, .ttd-blok, .ttd-kepsek, .identitas tr { break-inside: avoid; page-break-inside: avoid; }
            .rapor-table .kelompok, .rapor-sec { break-after: avoid; page-break-after: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()" data-testid="print-rapor-button">Cetak / Simpan PDF</button></div>

<div class="sheet" data-testid="rapor-sheet">
    {{-- Kop & judul disembunyikan agar sama dengan rapor sekolah. Aktifkan dengan 'tampil_kop' => true di config/erapor.php --}}
    @if(config('erapor.tampil_kop', false))
    <div class="kop">
        <img src="{{ asset('logo.png') }}" alt="Logo {{ config('erapor.sekolah.nama') }}" class="logo" data-testid="rapor-logo">
        <div>
            <h1>{{ config('erapor.sekolah.nama') }}</h1>
            <p>NPSN: {{ config('erapor.sekolah.npsn') }} · {{ config('erapor.sekolah.alamat') }}, {{ config('erapor.sekolah.kota') }}</p>
            <p>Telp. {{ config('erapor.sekolah.telepon') }} · {{ config('erapor.sekolah.email') }}</p>
        </div>
    </div>
    <h2 class="judul">Laporan Hasil Belajar (Rapor)</h2>
    @endif

    <div class="identitas">
        <table class="kiri">
            <tr><td>Nama Peserta Didik</td><td class="sp">:</td><td data-testid="rapor-nama-siswa">{{ mb_strtoupper($siswa->nama) }}</td></tr>
            <tr><td>Nomor Induk/NISN</td><td class="sp">:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</td></tr>
            <tr><td>Sekolah</td><td class="sp">:</td><td>{{ mb_strtoupper(config('erapor.sekolah.nama')) }}</td></tr>
            <tr><td>Alamat</td><td class="sp">:</td><td>{{ config('erapor.sekolah.alamat') }}</td></tr>
        </table>
        <table class="kanan">
            <tr><td>Kelas</td><td class="sp">:</td><td data-testid="rapor-kelas">{{ $info['kelas_nama'] ?? '-' }}</td></tr>
            <tr><td>Fase</td><td class="sp">:</td><td data-testid="rapor-fase">{{ $info['fase'] }}</td></tr>
            <tr><td>Semester</td><td class="sp">:</td><td data-testid="rapor-semester">{{ $ta->semester }}</td></tr>
            <tr><td>Tahun Pelajaran</td><td class="sp">:</td><td>{{ $ta->nama }}</td></tr>
        </table>
    </div>

    @include('rapor._body', ['bagian' => 'nilai'])
    <div class="footer-layar">
        <span>{{ mb_strtoupper($siswa->nama) }} - {{ $info['kelas_nama'] ?? '-' }}</span>
        <span>e-Rapor {{ config('erapor.sekolah.nama') }} - Halaman 1</span>
    </div>
</div>

<div class="sheet" data-testid="rapor-sheet-2">
    <table class="identitas-mini">
        <tr><td>Nama Peserta Didik</td><td class="sp">:</td><td>{{ mb_strtoupper($siswa->nama) }}</td></tr>
        <tr><td>Nomor Induk/NISN</td><td class="sp">:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</td></tr>
        <tr><td>Kelas</td><td class="sp">:</td><td>{{ $info['kelas_nama'] ?? '-' }}</td></tr>
        <tr><td>Tahun Pelajaran</td><td class="sp">:</td><td>{{ $ta->nama }}</td></tr>
        <tr><td>Semester</td><td class="sp">:</td><td>{{ $ta->semester }}</td></tr>
    </table>

    @include('rapor._body', ['bagian' => 'lain'])

    <div class="ttd-blok">
        <div class="ttd">
            <div>Orang Tua/Wali<br><br>&nbsp;<div class="space"></div><div class="garis"></div></div>
            <div>{{ config('erapor.sekolah.kota') }}, {{ ($ta->tanggal_rapor ?? now())->translatedFormat('d F Y') }}<br>Wali Kelas<div class="space"></div>
                <div class="nama">{{ $info['wali_nama'] ?? '..............................' }}</div>
                <div class="nip">{{ config('erapor.label_nip_wali', 'NIGKY') }} {{ $info['wali_nip'] ?? '-' }}</div></div>
        </div>
        <div class="ttd-kepsek">Mengetahui,<br>Kepala Sekolah<div class="space"></div>
            <div class="nama">{{ $ta->kepala_sekolah ?? '..............................' }}</div>
            <div class="nip">{{ config('erapor.label_nip_kepsek', 'NIGTY') }} {{ $ta->nip_kepsek ?? '-' }}</div></div>
    </div>
    <div class="footer-layar">
        <span>{{ mb_strtoupper($siswa->nama) }} - {{ $info['kelas_nama'] ?? '-' }}</span>
        <span>e-Rapor {{ config('erapor.sekolah.nama') }} - Halaman 2</span>
    </div>
</div>
</body>
</html>