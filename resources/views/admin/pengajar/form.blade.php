@extends('layouts.app')
@section('title', $item->exists ? 'Edit Penugasan' : 'Tambah Penugasan Guru')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:720px">
    <form method="POST" action="{{ $item->exists ? route('admin.pengajar.update', $item) : route('admin.pengajar.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required data-testid="pengajar-kelas-select">
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(old('kelas_id', $item->kelas_id) == $k->id)>{{ $k->nama }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">Mata Pelajaran</label>
                <select name="mapel_id" class="form-select" required data-testid="pengajar-mapel-select">
                    @foreach($mapels as $m)<option value="{{ $m->id }}" @selected(old('mapel_id', $item->mapel_id) == $m->id)>{{ $m->nama }}</option>@endforeach
                </select></div>
            <div class="col-12"><label class="form-label">Guru Pengajar</label>
                <select name="guru_id" class="form-select" required data-testid="pengajar-guru-select">
                    @foreach($gurus as $g)<option value="{{ $g->id }}" @selected(old('guru_id', $item->guru_id) == $g->id)>{{ $g->nama }} ({{ $g->nip }})</option>@endforeach
                </select></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="pengajar-submit-button">Simpan</button><a href="{{ route('admin.pengajar.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
