@extends('layouts.app')
@section('title', 'Absensi & Catatan · '.$kelas->nama)
@section('eyebrow', 'Wali Kelas · TA '.$ta->label)
@section('content')
<div class="panel">
    <div class="panel-head"><h2>Rekap Ketidakhadiran & Catatan Wali Kelas</h2><a href="{{ route('wali.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a></div>
    <form method="POST" action="{{ route('wali.absensi.simpan', $kelas) }}" data-testid="absensi-form">
        @csrf
        <div class="table-responsive"><table class="table">
            <thead><tr><th>#</th><th>Siswa</th><th class="text-center">Sakit</th><th class="text-center">Izin</th><th class="text-center">Tanpa Ket.</th><th>Catatan Wali Kelas</th></tr></thead>
            <tbody>
            @foreach($siswas as $i => $s)
                @php($r = $rapors[$s->id] ?? null)
                <tr>
                    <td>{{ $i + 1 }}</td><td><div class="fw-semibold">{{ $s->nama }}</div><small class="text-muted">{{ $s->nis }}</small></td>
                    @foreach(['sakit', 'izin', 'alpa'] as $f)
                        <td><input type="number" min="0" name="data[{{ $s->id }}][{{ $f }}]" value="{{ $r?->$f ?? 0 }}" class="form-control nilai-input mx-auto" data-testid="{{ $f }}-input-{{ $s->id }}"></td>
                    @endforeach
                    <td><textarea rows="2" name="data[{{ $s->id }}][catatan_wali]" class="form-control form-control-sm" data-testid="catatan-input-{{ $s->id }}">{{ $r?->catatan_wali }}</textarea></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <button class="btn btn-primary btn-lg" data-testid="simpan-absensi-button"><i class="bi bi-save me-1"></i>Simpan</button>
    </form>
</div>
@endsection
