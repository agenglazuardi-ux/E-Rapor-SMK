@extends('layouts.app')
@section('title', 'Absensi & Catatan · '.$kelas->nama)
@section('eyebrow', 'Wali Kelas · TA '.$ta->label)
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Rekap Ketidakhadiran & Catatan Wali Kelas</h2><a href="{{ route('wali.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a></div>
    <form method="POST" action="{{ route('wali.absensi.simpan', $kelas) }}" data-testid="absensi-form">
        @csrf
        <div class="table-responsive"><table class="table">
            <thead><tr><th>#</th><th>Siswa</th><th class="text-center">Sakit</th><th class="text-center">Izin</th><th class="text-center">Tanpa Ket.</th><th>Catatan Wali Kelas</th>@if($genap)<th style="min-width:200px">Keputusan Kenaikan</th>@endif</tr></thead>
            <tbody>
            @foreach($siswas as $i => $s)
                @php
                    $r = $rapors[$s->id] ?? null;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td><td><div class="fw-semibold">{{ $s->nama }}</div><small class="text-muted">NIS {{ $s->nis }} · NISN {{ $s->nisn ?? '-' }}</small></td>
                    @foreach(['sakit', 'izin', 'alpa'] as $f)
                        <td><input type="number" min="0" name="data[{{ $s->id }}][{{ $f }}]" value="{{ $r?->$f ?? 0 }}" class="form-control nilai-input mx-auto" data-testid="{{ $f }}-input-{{ $s->id }}"></td>
                    @endforeach
                    <td><textarea rows="2" name="data[{{ $s->id }}][catatan_wali]" class="form-control form-control-sm" data-testid="catatan-input-{{ $s->id }}">{{ $r?->catatan_wali }}</textarea></td>
                    @if($genap)
                    <td>
                        <select name="data[{{ $s->id }}][keputusan]" class="form-select form-select-sm keputusan-select mb-1" data-testid="keputusan-select-{{ $s->id }}">
                            <option value="">- Belum diputuskan -</option>
                            @if($tingkatTujuan)
                                <option value="naik" @selected($r?->keputusan === 'naik')>Naik kelas</option>
                            @endif
                            <option value="tinggal" @selected($r?->keputusan === 'tinggal')>Tinggal kelas</option>
                            @if($kelas->tingkat === 'XII')
                                <option value="lulus" @selected($r?->keputusan === 'lulus')>Lulus</option>
                            @endif
                        </select>
                        @if($tingkatTujuan)
                        <select name="data[{{ $s->id }}][kelas_tujuan]" class="form-select form-select-sm tujuan-select" data-testid="tujuan-select-{{ $s->id }}" @style(['display:none' => $r?->keputusan !== 'naik'])>
                            <option value="">- Kelas tujuan -</option>
                            @foreach($tujuanList as $t)
                                <option value="{{ $t->nama }}" @selected($r?->keputusan === 'naik' && $r?->kelas_tujuan === $t->nama)>{{ $t->nama }}</option>
                            @endforeach
                        </select>
                        @endif
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @if($genap)
            <p class="small text-muted">Keputusan kenaikan tampil di rapor bagian <b>D. Kenaikan Kelas</b> dan dipakai admin saat memproses kenaikan kelas.</p>
            @if(! $tingkatTujuan)
                <p class="small text-muted">Kelas XII hanya bisa <b>Lulus</b> atau <b>Tinggal</b>.</p>
            @elseif($tujuanList->isEmpty())
                <p class="small text-danger">Belum ada kelas tingkat {{ $tingkatTujuan }}; minta admin membuatnya dulu agar bisa memilih kelas tujuan.</p>
            @endif
        @endif
        <button class="btn btn-primary btn-lg" data-testid="simpan-absensi-button"><i class="bi bi-save me-1"></i>Simpan</button>
    </form>
</div>
@if($genap)
<script>
document.querySelectorAll('.keputusan-select').forEach(function (sel) {
    var tujuan = sel.parentElement.querySelector('.tujuan-select');
    if (!tujuan) return;
    function sinkron() {
        var naik = sel.value === 'naik';
        tujuan.style.display = naik ? '' : 'none';
        tujuan.required = naik;
        if (!naik) tujuan.value = '';
    }
    sel.addEventListener('change', sinkron);
    sinkron();
});
</script>
@endif
@endsection
