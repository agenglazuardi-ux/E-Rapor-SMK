@extends('layouts.app')
@section('title', 'Tahun Ajaran')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Daftar Tahun Ajaran & Semester</h2><a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary" data-testid="add-tahun-ajaran-btn"><i class="bi bi-plus-lg me-1"></i>Tambah</a></div>
    <div class="table-responsive"><table class="table table-hover" data-testid="tahun-ajaran-table">
        <thead><tr><th>Tahun Ajaran</th><th>Semester</th><th>Tgl Rapor</th><th>Kepala Sekolah</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $it)
            <tr>
                <td class="fw-bold">{{ $it->nama }}</td><td>{{ $it->semester }}</td>
                <td>{{ $it->tanggal_rapor?->translatedFormat('d F Y') ?? '-' }}</td><td>{{ $it->kepala_sekolah ?? '-' }}</td>
                <td>
                    @if($it->aktif)
                        <span class="badge badge-tuntas" data-testid="ta-aktif-badge-{{ $it->id }}">Aktif</span>
                    @else
                        <form method="POST" action="{{ route('admin.tahun-ajaran.aktifkan', $it) }}">@csrf<button class="btn btn-sm btn-accent" data-testid="aktifkan-ta-{{ $it->id }}">Aktifkan</button></form>
                    @endif
                </td>
                <td>@include('partials.actions', ['edit' => route('admin.tahun-ajaran.edit', $it), 'delete' => route('admin.tahun-ajaran.destroy', $it), 'tid' => 'ta-'.$it->id])</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
