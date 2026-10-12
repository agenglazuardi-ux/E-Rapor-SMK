@extends('layouts.app')
@section('title', $item->exists ? 'Edit Guru' : 'Tambah Guru')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:760px">
    <form method="POST" action="{{ $item->exists ? route('admin.guru.update', $item) : route('admin.guru.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">NIP (dipakai sebagai username)</label><input name="nip" class="form-control" value="{{ old('nip', $item->nip) }}" required data-testid="guru-nip-input"></div>
            <div class="col-md-6"><label class="form-label">Nama Lengkap & Gelar</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" required data-testid="guru-nama-input"></div>
            <div class="col-md-6"><label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select" data-testid="guru-jk-select">
                    <option value="L" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'P')>Perempuan</option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Telepon</label><input name="telepon" class="form-control" value="{{ old('telepon', $item->telepon) }}" data-testid="guru-telepon-input"></div>
            <div class="col-md-6"><label class="form-label">Password {{ $item->exists ? '(kosongkan jika tidak diubah)' : '(kosong = NIP)' }}</label><input type="password" name="password" class="form-control" data-testid="guru-password-input"></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="guru-submit-button">Simpan</button><a href="{{ route('admin.guru.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
