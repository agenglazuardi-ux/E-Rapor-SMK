@extends('layouts.app')
@section('title', 'Data Guru')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head">
        <h2>Daftar Guru</h2>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / NIP" data-testid="guru-search-input"><button class="btn btn-light" data-testid="guru-search-btn"><i class="bi bi-search"></i></button></form>
            <a href="{{ route('admin.guru.create') }}" class="btn btn-primary" data-testid="add-guru-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover" data-testid="guru-table">
        <thead><tr><th>NIP</th><th>Nama</th><th>L/P</th><th>Telepon</th><th>Mengajar</th><th>Wali Kelas</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="font-monospace small">{{ $it->nip }}</td><td class="fw-semibold">{{ $it->nama }}</td><td>{{ $it->jenis_kelamin }}</td><td>{{ $it->telepon ?? '-' }}</td>
                <td>{{ $it->pengajars_count }} mapel</td><td>{{ $it->kelasWali->pluck('nama')->join(', ') ?: '-' }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.guru.edit', $it), 'delete' => route('admin.guru.destroy', $it), 'tid' => 'guru-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $items->links('pagination::bootstrap-5') }}
</div>
@endsection
