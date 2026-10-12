@extends('layouts.app')
@section('title', 'Jurusan / Program Keahlian')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Daftar Jurusan</h2><a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary" data-testid="add-jurusan-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a></div>
    <table class="table table-hover" data-testid="jurusan-table">
        <thead><tr><th>Kode</th><th>Nama Jurusan</th><th>Jumlah Kelas</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="fw-bold">{{ $it->kode }}</td><td>{{ $it->nama }}</td><td>{{ $it->kelas_count }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.jurusan.edit', $it), 'delete' => route('admin.jurusan.destroy', $it), 'tid' => 'jurusan-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
