@extends('layouts.app')
@section('title', $item->exists ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:640px">
    <form method="POST" action="{{ $item->exists ? route('admin.jurusan.update', $item) : route('admin.jurusan.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Kode</label><input name="kode" class="form-control" value="{{ old('kode', $item->kode) }}" placeholder="TKJ" required data-testid="jurusan-kode-input"></div>
        <div class="mb-3"><label class="form-label">Nama Jurusan</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" required data-testid="jurusan-nama-input"></div>
        <div class="d-flex gap-2"><button class="btn btn-primary" data-testid="jurusan-submit-button">Simpan</button><a href="{{ route('admin.jurusan.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
