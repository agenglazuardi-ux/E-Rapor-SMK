@extends('layouts.app')
@section('title', 'Input Nilai')
@section('eyebrow', $ta ? 'TA '.$ta->label : 'Penilaian')
@section('content')
@unless($ta)<div class="alert alert-warning">Belum ada Tahun Ajaran aktif. Hubungi admin.</div>@endunless
<div class="row g-3">
    @forelse($pengajars as $p)
        @php($jml = $p->kelas->siswas->count())
        @php($isi = $terisi[$p->id] ?? 0)
        <div class="col-md-6 col-xl-4">
            <div class="stat h-100 d-flex flex-column" data-testid="pengajar-card-{{ $p->id }}">
                <span class="badge text-bg-light align-self-start mb-2">{{ $p->mapel->kelompok }} · KKM {{ $p->mapel->kkm }}</span>
                <h3 class="h5 fw-bold mb-1">{{ $p->mapel->nama }}</h3>
                <div class="text-muted mb-3">Kelas {{ $p->kelas->nama }} · {{ $jml }} siswa</div>
                <div class="progress mb-3" style="height:8px"><div class="progress-bar" style="width:{{ $jml ? round($isi / $jml * 100) : 0 }}%;background:var(--amber)"></div></div>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <small class="text-muted" data-testid="pengajar-progress-{{ $p->id }}">{{ $isi }}/{{ $jml }} terisi</small>
                    @if($ta)<a href="{{ route('nilai.edit', $p) }}" class="btn btn-primary btn-sm" data-testid="input-nilai-btn-{{ $p->id }}">Input Nilai <i class="bi bi-arrow-right"></i></a>@endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="panel text-muted">Anda belum ditugaskan mengajar. Hubungi admin.</div></div>
    @endforelse
</div>
@endsection
