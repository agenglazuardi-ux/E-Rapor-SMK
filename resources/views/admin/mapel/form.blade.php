@extends('layouts.app')
@section('title', $item->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:720px">
    <form method="POST" action="{{ $item->exists ? route('admin.mapel.update', $item) : route('admin.mapel.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Kode</label><input name="kode" class="form-control" value="{{ old('kode', $item->kode) }}" required data-testid="mapel-kode-input"></div>
            <div class="col-md-8"><label class="form-label">Nama Mata Pelajaran</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" required data-testid="mapel-nama-input"></div>
            <div class="col-md-4"><label class="form-label">Kelompok</label>
                <select name="kelompok" class="form-select" data-testid="mapel-kelompok-select">
                    @foreach(\App\Models\Mapel::KELOMPOK as $k)<option @selected(old('kelompok', $item->kelompok) === $k)>{{ $k }}</option>@endforeach
                </select></div>
            <div class="col-md-4"><label class="form-label">KKM</label><input type="number" min="0" max="100" name="kkm" class="form-control" value="{{ old('kkm', $item->kkm) }}" required data-testid="mapel-kkm-input"></div>
            <div class="col-md-4"><label class="form-label">Urutan di Rapor</label><input type="number" min="1" name="urutan" class="form-control" value="{{ old('urutan', $item->urutan) }}" required data-testid="mapel-urutan-input"></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="mapel-submit-button">Simpan</button><a href="{{ route('admin.mapel.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
