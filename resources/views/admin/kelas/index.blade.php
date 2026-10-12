@extends('layouts.app')
@section('title', 'Kelas & Wali Kelas')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Daftar Kelas</h2><a href="{{ route('admin.kelas.create') }}" class="btn btn-primary" data-testid="add-kelas-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a></div>
    <div class="table-responsive"><table class="table table-hover" data-testid="kelas-table">
        <thead><tr><th>Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Wali Kelas</th><th>Siswa</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="fw-bold">{{ $it->nama }}</td><td>{{ $it->tingkat }}</td><td>{{ $it->jurusan->nama }}</td>
                <td>{{ $it->waliKelas?->nama ?? '-' }}</td><td>{{ $it->siswas_count }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.kelas.edit', $it), 'delete' => route('admin.kelas.destroy', $it), 'tid' => 'kelas-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
