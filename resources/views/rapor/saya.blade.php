@extends('layouts.app')
@section('title', 'Rapor Saya')
@section('eyebrow', 'TA '.$ta->label)
@section('content')
<style>
    .rapor-table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:.9rem; }
    .rapor-table th, .rapor-table td { border:1px solid var(--line); padding:8px 10px; }
    .rapor-table th { background:var(--forest); color:#fff; font-weight:600; text-align:center; }
    .rapor-table .kelompok td { background:#f5f2ea; font-weight:700; }
    .rapor-table .c { text-align:center; } .rapor-table .merah { color:#b42318; }
    .rapor-grid { display:grid; grid-template-columns:1fr 2fr; gap:16px; }
</style>
<div class="panel">
    <div class="panel-head">
        <div><h2>{{ $siswa->nama }}</h2><small class="text-muted">NIS {{ $siswa->nis }} · Kelas {{ $info['kelas_nama'] ?? '-' }} · Wali Kelas {{ $info['wali_nama'] ?? '-' }}@if($siswa->status !== 'aktif') · <span class="badge text-bg-warning">{{ ucfirst($siswa->status) }} {{ $siswa->tahun_lulus }}</span>@endif</small></div>
        <div class="d-flex gap-2">
            <form><select name="ta" class="form-select" onchange="this.form.submit()" data-testid="rapor-ta-select">
                @foreach($tahunAjarans as $t)<option value="{{ $t->id }}" @selected($t->id === $ta->id)>{{ $t->label }}</option>@endforeach
            </select></form>
            <a href="{{ route('rapor.cetak', ['siswa' => $siswa, 'ta' => $ta->id]) }}" target="_blank" class="btn btn-primary text-nowrap" data-testid="cetak-rapor-saya-btn"><i class="bi bi-printer me-1"></i>Cetak Rapor</a>
        </div>
    </div>
    @include('rapor._body')
</div>
@endsection
