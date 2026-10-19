<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor {{ $siswa->nama }} - {{ $ta->label }}</title>
    <style>
        @page { size: A4; margin: 15mm 15mm 18mm; }
        body { font-family: 'Times New Roman', serif; font-size: 12px; color: #000; margin: 0; background: #e9e6df; }
        .sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 20px auto; padding: 15mm; box-sizing: border-box; }
        .kop { display: flex; align-items: center; gap: 14px; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px; }
        .kop .logo { height: 72px; width: auto; max-width: 90px; object-fit: contain; flex-shrink: 0; }
        .kop h1 { font-size: 18px; margin: 0; text-transform: uppercase; } .kop p { margin: 2px 0; font-size: 11px; }
        h2.judul { text-align: center; font-size: 14px; margin: 6px 0 12px; text-transform: uppercase; letter-spacing: 1px; }
        .identitas { width: 100%; margin-bottom: 12px; } .identitas td { padding: 2px 4px; vertical-align: top; }
        .rapor-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .rapor-table th, .rapor-table td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; }
        .rapor-table th { background: #eee; text-align: center; }
        .rapor-table .kelompok td { background: #f3f3f3; font-weight: bold; }
        .c { text-align: center; } .fw-bold { font-weight: bold; } .merah { color: #b00; } .desc { text-align: justify; } .mt-1 { margin-top: 4px; }
        .rapor-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 14px; }
        .ttd { display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; margin-top: 24px; }
        .ttd .space { height: 64px; } .ttd .nama { font-weight: bold; text-decoration: underline; }
        .toolbar { text-align: center; margin: 16px; font-family: sans-serif; }
        .toolbar button { background: #0f3d2e; color: #fff; border: 0; padding: 10px 22px; border-radius: 8px; font-size: 14px; cursor: pointer; }
        @media print {
            body { background: #fff; } .sheet { margin: 0; padding: 0; width: auto; min-height: auto; } .toolbar { display: none; }
            /* Jaga agar baris, tabel kecil, dan tanda tangan tidak terpotong di tengah halaman. */
            .rapor-table thead { display: table-header-group; }
            .rapor-table tr, .rapor-grid, .ttd-blok, .identitas tr { break-inside: avoid; page-break-inside: avoid; }
            .rapor-table .kelompok { break-after: avoid; page-break-after: avoid; }
            .rapor-table[data-testid="rapor-ekskul-table"] { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()" data-testid="print-rapor-button">Cetak / Simpan PDF</button></div>
<div class="sheet" data-testid="rapor-sheet">
    <div class="kop">
        <img src="{{ asset('logo.png') }}" alt="Logo {{ config('erapor.sekolah.nama') }}" class="logo" data-testid="rapor-logo">
        
        <div>
            <h1>{{ config('erapor.sekolah.nama') }}</h1>
            <p>NPSN: {{ config('erapor.sekolah.npsn') }} · {{ config('erapor.sekolah.alamat') }}, {{ config('erapor.sekolah.kota') }}</p>
            <p>Telp. {{ config('erapor.sekolah.telepon') }} · {{ config('erapor.sekolah.email') }}</p>
        </div>
    </div>
    <h2 class="judul">Laporan Hasil Belajar (Rapor)</h2>
    <table class="identitas">
        <tr><td style="width:18%">Nama Peserta Didik</td><td style="width:2%">:</td><td style="width:38%" data-testid="rapor-nama-siswa"><b>{{ $siswa->nama }}</b></td>
            <td style="width:16%">Kelas</td><td style="width:2%">:</td><td data-testid="rapor-kelas">{{ $info['kelas_nama'] ?? '-' }}</td></tr>
        <tr><td>NIS / NISN</td><td>:</td><td>{{ $siswa->nis }} / {{ $siswa->nisn ?? '-' }}</td><td>Fase</td><td>:</td><td data-testid="rapor-fase">{{ $info['fase'] }}</td></tr>
        <tr><td>Program Keahlian</td><td>:</td><td>{{ $info['jurusan_nama'] ?? '-' }}</td><td>Semester</td><td>:</td><td data-testid="rapor-semester">{{ $ta->semester === 'Ganjil' ? '1 (Ganjil)' : '2 (Genap)' }}</td></tr>
        <tr><td>Nama Sekolah</td><td>:</td><td>{{ config('erapor.sekolah.nama') }}</td><td>Tahun Ajaran</td><td>:</td><td>{{ $ta->nama }}</td></tr>
    </table>

    @include('rapor._body')

    <div class="ttd-blok">
    <div class="ttd">
        <div>Mengetahui,<br>Orang Tua/Wali<div class="space"></div><div class="nama">{{ $siswa->nama_ortu ?? '..............................' }}</div></div>
        <div></div>
        <div>{{ config('erapor.sekolah.kota') }}, {{ ($ta->tanggal_rapor ?? now())->translatedFormat('d F Y') }}<br>Wali Kelas<div class="space"></div>
            <div class="nama">{{ $info['wali_nama'] ?? '..............................' }}</div><div>NIP. {{ $info['wali_nip'] ?? '-' }}</div></div>
    </div>
    <div class="ttd" style="grid-template-columns:1fr">
        <div>Mengetahui,<br>Kepala Sekolah<div class="space"></div><div class="nama">{{ $ta->kepala_sekolah ?? '..............................' }}</div><div>NIP. {{ $ta->nip_kepsek ?? '-' }}</div></div>
    </div>
    </div>
</div>
</body>
</html>
