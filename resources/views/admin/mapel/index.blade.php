@extends('layouts.app')
@section('title', 'Mata Pelajaran')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Daftar Mata Pelajaran & KKM</h2><a href="{{ route('admin.mapel.create') }}" class="btn btn-primary" data-testid="add-mapel-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a></div>
    <div class="table-responsive"><table class="table table-hover" data-testid="mapel-table">
        <thead><tr><th>Kode</th><th>Mata Pelajaran</th><th>Kelompok</th><th>KKM</th><th>Urutan</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="fw-bold">{{ $it->kode }}</td><td>{{ $it->nama }}</td><td><span class="badge text-bg-light">{{ $it->kelompok }}</span></td>
                <td class="fw-bold">{{ $it->kkm }}</td><td>{{ $it->urutan }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.mapel.edit', $it), 'delete' => route('admin.mapel.destroy', $it), 'tid' => 'mapel-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
