@extends('layouts.app')
@section('title', 'Guru Pengajar')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head">
        <h2>Penugasan Guru per Mapel & Kelas</h2>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2"><select name="kelas_id" class="form-select" onchange="this.form.submit()" data-testid="pengajar-filter-kelas"><option value="">Semua kelas</option>
                @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>@endforeach
            </select></form>
            <a href="{{ route('admin.pengajar.create') }}" class="btn btn-primary" data-testid="add-pengajar-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover" data-testid="pengajar-table">
        <thead><tr><th>Kelas</th><th>Mata Pelajaran</th><th>Guru</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="fw-bold">{{ $it->kelas->nama }}</td><td>{{ $it->mapel->nama }}</td><td>{{ $it->guru->nama }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.pengajar.edit', $it), 'delete' => route('admin.pengajar.destroy', $it), 'tid' => 'pengajar-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
