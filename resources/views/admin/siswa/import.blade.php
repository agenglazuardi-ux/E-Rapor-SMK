@extends('layouts.app')
@section('title', 'Import Siswa dari Excel')
@section('eyebrow', 'Data Master')
@section('content')
@if($hasil = session('hasil_import'))
    <div class="panel" data-testid="import-result">
        <div class="panel-head"><h2>Hasil Import</h2></div>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="stat"><div class="stat-label">Siswa baru</div><div class="stat-value" data-testid="import-baru">{{ $hasil['baru'] }}</div></div></div>
            <div class="col-md-4"><div class="stat"><div class="stat-label">Diperbarui</div><div class="stat-value" data-testid="import-diperbarui">{{ $hasil['diperbarui'] }}</div></div></div>
            <div class="col-md-4"><div class="stat"><div class="stat-label">Gagal</div><div class="stat-value" style="color:#b42318" data-testid="import-gagal">{{ count($hasil['gagal']) }}</div></div></div>
        </div>
        @if($hasil['gagal'])
            <div class="alert alert-danger mb-0" data-testid="import-errors"><b>Baris yang gagal (perbaiki lalu import ulang):</b>
                <ul class="mb-0 mt-2 ps-3">@foreach($hasil['gagal'] as $g)<li>{{ $g }}</li>@endforeach</ul>
            </div>
        @endif
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel h-100">
            <div class="panel-head"><h2>Unggah File</h2></div>
            <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data" data-testid="import-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">File Excel (.xlsx / .xls / .csv, maks 10 MB)</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required data-testid="import-file-input">
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="perbarui" value="1" id="perbarui" checked data-testid="import-perbarui-checkbox">
                    <label class="form-check-label" for="perbarui">Perbarui data jika NIS sudah terdaftar (kalau tidak dicentang, baris dengan NIS yang sama dilewati)</label>
                </div>
                <button class="btn btn-primary btn-lg" data-testid="import-submit-button" onclick="this.innerHTML='Memproses...';this.form.submit();this.disabled=true;"><i class="bi bi-upload me-1"></i>Import Sekarang</button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel h-100">
            <div class="panel-head"><h2>Format Kolom</h2><a href="{{ route('admin.siswa.import.template') }}" class="btn btn-accent btn-sm" data-testid="download-template-btn"><i class="bi bi-download me-1"></i>Unduh Template</a></div>
            <ol class="small mb-3">@foreach($header as $h)<li>{{ $h }}</li>@endforeach</ol>
            <ul class="small text-muted mb-0">
                <li>Baris 1 adalah judul kolom, data mulai baris 2.</li>
                <li><b>NIS, Nama, L/P</b> wajib diisi. L/P boleh ditulis L/P atau Laki-laki/Perempuan.</li>
                <li>Nama kelas harus sama persis dengan data Kelas (lihat sheet "Daftar Kelas" di template).</li>
                <li>Tanggal lahir: format tanggal Excel, atau teks 2010-05-17 / 17/05/2010.</li>
                <li>Akun login siswa dibuat otomatis: username & password = NIS.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
