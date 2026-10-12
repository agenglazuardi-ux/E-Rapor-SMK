@extends('layouts.app')
@section('title', $pengajar->mapel->nama.' · '.$pengajar->kelas->nama)
@section('eyebrow', 'Input Nilai · TA '.$ta->label)
@section('content')
<div class="panel">
    <div class="panel-head">
        <div><h2>Nilai Akhir & Capaian Kompetensi</h2><small class="text-muted">KKM: <b>{{ $pengajar->mapel->kkm }}</b>. Deskripsi akan dicetak sebagai "Menunjukkan penguasaan yang baik dalam ..." dan "Perlu peningkatan dalam ...".</small></div>
        <a href="{{ route('nilai.index') }}" class="btn btn-light" data-testid="back-to-nilai-list"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <form method="POST" action="{{ route('nilai.update', $pengajar) }}" data-testid="nilai-form">
        @csrf
        <div class="table-responsive"><table class="table">
            <thead><tr><th>#</th><th>Siswa</th><th class="text-center">Nilai Akhir</th><th>Capaian Tertinggi (dikuasai)</th><th>Capaian Terendah (perlu peningkatan)</th></tr></thead>
            <tbody>
            @forelse($siswas as $i => $s)
                @php($n = $nilais[$s->id] ?? null)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><div class="fw-semibold">{{ $s->nama }}</div><small class="text-muted">{{ $s->nis }}</small></td>
                    <td class="text-center"><input type="number" min="0" max="100" name="nilai[{{ $s->id }}][nilai_akhir]" value="{{ old("nilai.$s->id.nilai_akhir", $n?->nilai_akhir) }}" class="form-control nilai-input mx-auto" data-testid="nilai-akhir-input-{{ $s->id }}"></td>
                    <td><textarea rows="2" name="nilai[{{ $s->id }}][capaian_tertinggi]" class="form-control form-control-sm" data-testid="capaian-tinggi-input-{{ $s->id }}">{{ old("nilai.$s->id.capaian_tertinggi", $n?->capaian_tertinggi) }}</textarea></td>
                    <td><textarea rows="2" name="nilai[{{ $s->id }}][capaian_terendah]" class="form-control form-control-sm" data-testid="capaian-rendah-input-{{ $s->id }}">{{ old("nilai.$s->id.capaian_terendah", $n?->capaian_terendah) }}</textarea></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada siswa di kelas ini.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($siswas->count())<button class="btn btn-primary btn-lg" data-testid="simpan-nilai-button"><i class="bi bi-save me-1"></i>Simpan Nilai</button>@endif
    </form>
</div>
@endsection
