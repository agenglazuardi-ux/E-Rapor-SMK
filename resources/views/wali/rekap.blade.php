@extends('layouts.app')
@section('title', 'Rekap Nilai · '.$kelas->nama)
@section('eyebrow', 'Leger Nilai · TA '.$ta->label)
@section('content')
<div class="panel">
    <div class="panel-head">
        <div><h2>Leger Nilai Akhir Kelas</h2><small class="text-muted">{{ $kelas->jurusan->nama }} · Wali Kelas: {{ $kelas->waliKelas?->nama ?? '-' }} · Nilai merah = di bawah KKM.</small></div>
        <div class="d-flex gap-2"><form><select name="ta" class="form-select" onchange="this.form.submit()" data-testid="rekap-ta-select">
                @foreach($tahunAjarans as $t)<option value="{{ $t->id }}" @selected($t->id === $ta->id)>{{ $t->label }}</option>@endforeach
            </select></form><a href="{{ route('wali.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a><button onclick="window.print()" class="btn btn-accent" data-testid="print-rekap-btn"><i class="bi bi-printer me-1"></i>Cetak Leger</button></div>
    </div>
    <div class="table-responsive"><table class="table table-bordered table-sm text-center small" data-testid="rekap-table">
        <thead><tr><th>No</th><th class="text-start">Nama Siswa</th>
            @foreach($mapels as $m)<th title="{{ $m->nama }}">{{ $m->kode }}</th>@endforeach
            <th>Jumlah</th><th>Rata-rata</th><th>Rank</th><th>Rapor</th></tr></thead>
        <tbody>
        @forelse($rows as $i => $r)
            <tr data-testid="rekap-row-{{ $r['siswa']->id }}">
                <td>{{ $i + 1 }}</td><td class="text-start fw-semibold">{{ $r['siswa']->nama }}</td>
                @foreach($mapels as $m)
                    @php($v = $r['nilai'][$m->id] ?? null)
                    <td class="{{ $v !== null && $v < $m->kkm ? 'text-danger fw-bold' : '' }}">{{ $v ?? '-' }}</td>
                @endforeach
                <td class="fw-bold">{{ $r['jumlah'] }}</td><td>{{ $r['rata'] }}</td>
                <td><span class="badge text-bg-dark" data-testid="rank-{{ $r['siswa']->id }}">{{ $rank[$r['siswa']->id] }}</span></td>
                <td><a href="{{ route('rapor.cetak', ['siswa' => $r['siswa'], 'ta' => $ta->id]) }}" target="_blank" data-testid="rekap-cetak-{{ $r['siswa']->id }}"><i class="bi bi-printer"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="{{ $mapels->count() + 6 }}" class="text-muted py-3">Tidak ada data siswa pada semester ini.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr class="fw-bold"><td colspan="2" class="text-end">Rata-rata Mapel</td>
            @foreach($mapels as $m)<td>{{ $rataMapel[$m->id] }}</td>@endforeach<td colspan="4"></td></tr></tfoot>
    </table></div>
    <div class="small text-muted mt-2">@foreach($mapels as $m)<span class="me-3"><b>{{ $m->kode }}</b>: {{ $m->nama }}</span>@endforeach</div>
</div>
@endsection
