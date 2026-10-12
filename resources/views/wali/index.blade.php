@extends('layouts.app')
@section('title', auth()->user()->role === 'admin' ? 'Rekap & Cetak Rapor' : 'Kelas Perwalian')
@section('eyebrow', $ta ? 'TA '.$ta->label : 'Wali Kelas')
@section('content')
@unless($ta)<div class="alert alert-warning">Belum ada Tahun Ajaran aktif.</div>@endunless
@forelse($kelasList as $k)
    <div class="panel" data-testid="wali-kelas-panel-{{ $k->id }}">
        <div class="panel-head">
            <div><h2>{{ $k->nama }}</h2><small class="text-muted">{{ $k->jurusan->nama }} · Wali: {{ $k->waliKelas?->nama ?? '-' }} · {{ $k->siswas->count() }} siswa</small></div>
            @if($ta)
            <div class="d-flex gap-2">
                <a href="{{ route('wali.absensi', $k) }}" class="btn btn-light" data-testid="absensi-btn-{{ $k->id }}"><i class="bi bi-calendar-check me-1"></i>Absensi & Catatan</a>
                <a href="{{ route('wali.rekap', $k) }}" class="btn btn-accent" data-testid="rekap-btn-{{ $k->id }}"><i class="bi bi-table me-1"></i>Rekap Nilai</a>
            </div>
            @endif
        </div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>NIS</th><th>Nama Siswa</th><th class="text-end">Rapor</th></tr></thead>
            <tbody>
            @forelse($k->siswas as $s)
                <tr><td class="font-monospace small">{{ $s->nis }}</td><td>{{ $s->nama }}</td>
                    <td class="text-end">@if($ta)<a href="{{ route('rapor.cetak', $s) }}" target="_blank" class="btn btn-sm btn-outline-secondary" data-testid="cetak-rapor-btn-{{ $s->id }}"><i class="bi bi-printer me-1"></i>Cetak</a>@endif</td></tr>
            @empty
                <tr><td colspan="3" class="text-muted text-center">Belum ada siswa.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
@empty
    <div class="panel text-muted">Tidak ada kelas {{ auth()->user()->role === 'guru' ? 'yang Anda walikan' : '' }}.</div>
@endforelse
@endsection
