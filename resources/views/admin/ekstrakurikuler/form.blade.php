@extends('layouts.app')
@section('title', $item->exists ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:640px">
    <form method="POST" action="{{ $item->exists ? route('admin.ekstrakurikuler.update', $item) : route('admin.ekstrakurikuler.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Nama Kegiatan</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" placeholder="Pramuka" required data-testid="ekstrakurikuler-nama-input"></div>
        <div class="mb-3"><label class="form-label">Guru Pembina</label>
            <select name="guru_id" class="form-select" required data-testid="ekstrakurikuler-guru-select">
                <option value="">- Pilih guru -</option>
                @foreach($gurus as $g)<option value="{{ $g->id }}" @selected(old('guru_id', $item->guru_id) == $g->id)>{{ $g->nama }}</option>@endforeach
            </select>
            <div class="form-text">Guru ini yang akan mengisi anggota dan keterangan ekstrakurikuler.</div>
        </div>
        <div class="d-flex gap-2"><button class="btn btn-primary" data-testid="ekstrakurikuler-submit-button">Simpan</button><a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
