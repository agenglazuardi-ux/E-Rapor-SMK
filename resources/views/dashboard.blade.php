@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="hero-strip">
    <span class="tag" data-testid="tahun-ajaran-aktif">{{ $ta ? 'TA '.$ta->label : 'Belum ada Tahun Ajaran aktif' }}</span>
    <h2 class="mt-3 mb-1">Selamat datang, {{ auth()->user()->name }}</h2>
    <p class="mb-0" style="color:#b9cbc1">{{ config('erapor.sekolah.nama') }} · NPSN {{ config('erapor.sekolah.npsn') }}</p>
</div>

@if(auth()->user()->role === 'admin')
    <div class="row g-3 mb-4">
        @foreach($stats as $label => $val)
            <div class="col-6 col-xl-3"><div class="stat" data-testid="stat-{{ \Illuminate\Support\Str::slug($label) }}"><div class="stat-label">{{ $label }}</div><div class="stat-value">{{ $val }}</div></div></div>
        @endforeach
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Langkah Penyiapan Rapor</h2></div>
        <ol class="mb-0 lh-lg">
            <li>Atur <a href="{{ route('admin.tahun-ajaran.index') }}">Tahun Ajaran</a> aktif & data kepala sekolah.</li>
            <li>Lengkapi data <a href="{{ route('admin.jurusan.index') }}">Jurusan</a>, <a href="{{ route('admin.guru.index') }}">Guru</a>, <a href="{{ route('admin.kelas.index') }}">Kelas & Wali Kelas</a>, dan <a href="{{ route('admin.siswa.index') }}">Siswa</a>.</li>
            <li>Atur <a href="{{ route('admin.mapel.index') }}">Mata Pelajaran</a> beserta KKM, lalu tugaskan <a href="{{ route('admin.pengajar.index') }}">Guru Pengajar</a>.</li>
            <li>Guru menginput nilai, wali kelas mengisi absensi & catatan, kemudian rapor siap dicetak. <span class="text-muted">(Nilai terisi semester ini: <b data-testid="nilai-terisi-count">{{ $nilaiTerisi }}</b>)</span></li>
        </ol>
    </div>
@elseif(auth()->user()->role === 'guru')
    {{-- Pengingat wali kelas: catatan wali kelas semester aktif yang belum diisi --}}
    @foreach($kelasWali as $k)
        @if(($k->catatan_belum ?? 0) > 0)
            <div class="alert alert-warning d-flex align-items-center gap-3" data-testid="pengingat-catatan-{{ $k->id }}">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div class="flex-grow-1"><b>{{ $k->catatan_belum }} dari {{ $k->siswas_count }} siswa</b> di kelas <b>{{ $k->nama }}</b> belum ada catatan wali kelas (TA {{ $ta->label }}).</div>
                <a href="{{ route('wali.absensi', $k) }}" class="btn btn-sm btn-warning text-nowrap" data-testid="pengingat-isi-{{ $k->id }}">Isi Sekarang</a>
            </div>
        @endif
    @endforeach
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel h-100">
                <div class="panel-head"><h2>Mapel yang Anda ajar</h2><a href="{{ route('nilai.index') }}" class="btn btn-sm btn-primary" data-testid="dashboard-input-nilai-btn">Input Nilai</a></div>
                <ul class="list-group list-group-flush">
                    @forelse($pengajars as $p)
                        <li class="list-group-item d-flex justify-content-between px-0"><span>{{ $p->mapel->nama }}</span><span class="badge text-bg-light">{{ $p->kelas->nama }}</span></li>
                    @empty
                        <li class="list-group-item px-0 text-muted">Belum ada penugasan mengajar.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel h-100">
                <div class="panel-head"><h2>Kelas Perwalian</h2></div>
                @forelse($kelasWali as $k)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div><b>{{ $k->nama }}</b><div class="small text-muted">{{ $k->siswas_count }} siswa @if($ta)· catatan terisi {{ $k->siswas_count - ($k->catatan_belum ?? 0) }}/{{ $k->siswas_count }}@endif</div></div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('wali.absensi', $k) }}" class="btn btn-sm btn-light" data-testid="dashboard-absensi-{{ $k->id }}">Absensi & Catatan</a>
                            <a href="{{ route('wali.rekap', $k) }}" class="btn btn-sm btn-accent" data-testid="dashboard-rekap-{{ $k->id }}">Rekap</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Anda bukan wali kelas.</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    <div class="row g-3">
        <div class="col-md-4"><div class="stat"><div class="stat-label">Kelas</div><div class="stat-value" data-testid="siswa-kelas">{{ $siswa?->kelas?->nama ?? '-' }}</div></div></div>
        <div class="col-md-4"><div class="stat"><div class="stat-label">Rata-rata Nilai Semester Ini</div><div class="stat-value" data-testid="siswa-rata-rata">{{ $rataRata }}</div></div></div>
        <div class="col-md-4"><div class="stat"><div class="stat-label">Wali Kelas</div><div class="fw-bold fs-6 mt-2">{{ $siswa?->kelas?->waliKelas?->nama ?? '-' }}</div></div></div>
    </div>
    <a href="{{ route('rapor.saya') }}" class="btn btn-primary btn-lg mt-4" data-testid="dashboard-lihat-rapor-btn"><i class="bi bi-file-earmark-text me-2"></i>Lihat Rapor Saya</a>
@endif
@endsection