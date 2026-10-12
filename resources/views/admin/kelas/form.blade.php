@extends('layouts.app')
@section('title', $item->exists ? 'Edit Kelas' : 'Tambah Kelas')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:720px">
    <form method="POST" action="{{ $item->exists ? route('admin.kelas.update', $item) : route('admin.kelas.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama Kelas</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" placeholder="X TKJ 1" required data-testid="kelas-nama-input"></div>
            <div class="col-md-6"><label class="form-label">Tingkat</label>
                <select name="tingkat" class="form-select" data-testid="kelas-tingkat-select">
                    @foreach(['X', 'XI', 'XII'] as $t)<option @selected(old('tingkat', $item->tingkat) === $t)>{{ $t }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">Jurusan</label>
                <select name="jurusan_id" class="form-select" required data-testid="kelas-jurusan-select">
                    @foreach($jurusans as $j)<option value="{{ $j->id }}" @selected(old('jurusan_id', $item->jurusan_id) == $j->id)>{{ $j->kode }} - {{ $j->nama }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">Wali Kelas</label>
                <select name="wali_kelas_id" class="form-select" data-testid="kelas-wali-select">
                    <option value="">- Belum ditentukan -</option>
                    @foreach($gurus as $g)<option value="{{ $g->id }}" @selected(old('wali_kelas_id', $item->wali_kelas_id) == $g->id)>{{ $g->nama }}</option>@endforeach
                </select></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="kelas-submit-button">Simpan</button><a href="{{ route('admin.kelas.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
