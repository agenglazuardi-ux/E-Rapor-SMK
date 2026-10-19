@extends('layouts.app')
@section('title', 'Ekstrakurikuler')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Daftar Ekstrakurikuler</h2><a href="{{ route('admin.ekstrakurikuler.create') }}" class="btn btn-primary" data-testid="add-ekstrakurikuler-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a></div>
    <div class="table-responsive"><table class="table table-hover" data-testid="ekstrakurikuler-table">
        <thead><tr><th>Kegiatan Ekstrakurikuler</th><th>Guru Pembina</th><th>Anggota{{ $ta ? ' (TA '.$ta->label.')' : '' }}</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="fw-semibold">{{ $it->nama }}</td><td>{{ $it->guru?->nama ?? '-' }}</td><td>{{ $it->anggotas_count }} siswa</td>
                <td>@include('partials.actions', ['edit' => route('admin.ekstrakurikuler.edit', $it), 'delete' => route('admin.ekstrakurikuler.destroy', $it), 'tid' => 'ekstrakurikuler-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
