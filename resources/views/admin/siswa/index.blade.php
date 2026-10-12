@extends('layouts.app')
@section('title', 'Data Siswa')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head">
        <h2>Daftar Siswa</h2>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.siswa.import.form') }}" class="btn btn-accent" data-testid="import-siswa-btn"><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel</a>
            <a href="{{ route('admin.siswa.export', request()->query()) }}" class="btn btn-light" data-testid="export-siswa-btn"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
            <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary" data-testid="add-siswa-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
        </div>
    </div>
    <form class="d-flex gap-2 flex-wrap mb-3">
        <select name="kelas_id" class="form-select w-auto" data-testid="siswa-filter-kelas"><option value="">Semua kelas</option>
            @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>@endforeach
        </select>
        <select name="status" class="form-select w-auto" data-testid="siswa-filter-status"><option value="">Semua status</option>
            @foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah/Keluar'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach
        </select>
        <input name="q" value="{{ request('q') }}" class="form-control w-auto" placeholder="Cari nama / NIS" data-testid="siswa-search-input">
        <button class="btn btn-light" data-testid="siswa-search-btn"><i class="bi bi-search"></i> Filter</button>
    </form>
    <div class="table-responsive"><table class="table table-hover" data-testid="siswa-table">
        <thead><tr><th>NIS</th><th>NISN</th><th>Nama</th><th>L/P</th><th>Kelas</th><th>Status</th><th>TTL</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr><td class="font-monospace small">{{ $it->nis }}</td><td class="font-monospace small">{{ $it->nisn ?? '-' }}</td><td class="fw-semibold">{{ $it->nama }}</td><td>{{ $it->jenis_kelamin }}</td>
                <td>{{ $it->kelas?->nama ?? '-' }}</td>
                <td><span class="badge {{ $it->status === 'aktif' ? 'badge-tuntas' : 'text-bg-warning' }}" data-testid="siswa-status-{{ $it->id }}">{{ ucfirst($it->status) }}{{ $it->tahun_lulus ? ' '.$it->tahun_lulus : '' }}</span></td>
                <td class="small">{{ $it->tempat_lahir }}{{ $it->tanggal_lahir ? ', '.$it->tanggal_lahir->format('d-m-Y') : '' }}</td>
                <td>@include('partials.actions', ['edit' => route('admin.siswa.edit', $it), 'delete' => route('admin.siswa.destroy', $it), 'tid' => 'siswa-'.$it->id])</td></tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $items->links('pagination::bootstrap-5') }}
</div>
@endsection
