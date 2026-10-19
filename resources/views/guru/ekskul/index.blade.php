@extends('layouts.app')
@section('title', 'Ekstrakurikuler')
@section('eyebrow', $ta ? 'TA '.$ta->label : 'Ekstrakurikuler')
@section('content')
@unless($ta)<div class="alert alert-warning">Belum ada Tahun Ajaran aktif. Hubungi admin.</div>@endunless
<div class="row g-3">
    @forelse($items as $e)
        <div class="col-md-6 col-xl-4">
            <div class="stat h-100 d-flex flex-column" data-testid="ekskul-card-{{ $e->id }}">
                <h3 class="h5 fw-bold mb-3">{{ $e->nama }}</h3>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <small class="text-muted" data-testid="ekskul-anggota-{{ $e->id }}">{{ $e->anggotas_count }} anggota</small>
                    @if($ta)<a href="{{ route('ekskul.edit', $e) }}" class="btn btn-primary btn-sm" data-testid="kelola-ekskul-btn-{{ $e->id }}">Kelola <i class="bi bi-arrow-right"></i></a>@endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="panel text-muted">Anda belum ditugaskan sebagai pembina ekstrakurikuler. Hubungi admin.</div></div>
    @endforelse
</div>
@endsection
