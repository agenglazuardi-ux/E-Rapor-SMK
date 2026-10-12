@extends('layouts.app')
@section('title', $item->exists ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:720px">
    <form method="POST" action="{{ $item->exists ? route('admin.tahun-ajaran.update', $item) : route('admin.tahun-ajaran.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Tahun Ajaran</label><input name="nama" class="form-control" placeholder="2025/2026" value="{{ old('nama', $item->nama) }}" required data-testid="ta-nama-input"></div>
            <div class="col-md-6"><label class="form-label">Semester</label>
                <select name="semester" class="form-select" data-testid="ta-semester-select">
                    @foreach(['Ganjil', 'Genap'] as $s)<option @selected(old('semester', $item->semester) === $s)>{{ $s }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">Tanggal Rapor</label><input type="date" name="tanggal_rapor" class="form-control" value="{{ old('tanggal_rapor', $item->tanggal_rapor?->format('Y-m-d')) }}" data-testid="ta-tanggal-input"></div>
            <div class="col-md-6"><label class="form-label">Kepala Sekolah</label><input name="kepala_sekolah" class="form-control" value="{{ old('kepala_sekolah', $item->kepala_sekolah) }}" data-testid="ta-kepsek-input"></div>
            <div class="col-md-6"><label class="form-label">NIP Kepala Sekolah</label><input name="nip_kepsek" class="form-control" value="{{ old('nip_kepsek', $item->nip_kepsek) }}" data-testid="ta-nip-kepsek-input"></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="ta-submit-button">Simpan</button><a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
