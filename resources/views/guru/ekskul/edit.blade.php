@extends('layouts.app')
@section('title', $ekstrakurikuler->nama)
@section('eyebrow', 'Ekstrakurikuler · TA '.$ta->label)
@section('content')
<div class="panel mb-4">
    <div class="panel-head">
        <div><h2>Anggota & Keterangan</h2><small class="text-muted">Keterangan dicetak di rapor pada bagian Ekstrakurikuler, misalnya "Aktif dan disiplin dalam setiap kegiatan". Centang "Keluarkan" untuk menghapus anggota saat disimpan.</small></div>
        <a href="{{ route('ekskul.index') }}" class="btn btn-light" data-testid="back-to-ekskul-list"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <form method="POST" action="{{ route('ekskul.update', $ekstrakurikuler) }}" data-testid="ekskul-form">
        @csrf @method('PUT')
        <div class="table-responsive"><table class="table">
            <thead><tr><th>#</th><th>Siswa</th><th>Kelas</th><th>Keterangan</th><th class="text-center">Keluarkan</th></tr></thead>
            <tbody>
            @forelse($anggotas as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><div class="fw-semibold">{{ $a->siswa->nama }}</div><small class="text-muted">{{ $a->siswa->nis }}</small></td>
                    <td>{{ $a->siswa->kelas?->nama ?? '-' }}</td>
                    <td><input name="keterangan[{{ $a->siswa_id }}]" value="{{ old("keterangan.$a->siswa_id", $a->keterangan) }}" maxlength="500" class="form-control" data-testid="ekskul-keterangan-input-{{ $a->siswa_id }}"></td>
                    <td class="text-center"><input type="checkbox" class="form-check-input" name="hapus[]" value="{{ $a->siswa_id }}" data-testid="ekskul-hapus-{{ $a->siswa_id }}"></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada anggota pada semester ini. Tambahkan dari bagian di bawah.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($anggotas->count())<button class="btn btn-primary" data-testid="simpan-ekskul-button"><i class="bi bi-save me-1"></i>Simpan</button>@endif
    </form>
</div>

<div class="panel">
    <div class="panel-head"><div><h2>Tambah Anggota</h2><small class="text-muted">Pilih kelas, lalu centang siswa yang mengikuti kegiatan ini.</small></div></div>
    <form method="GET" action="{{ route('ekskul.edit', $ekstrakurikuler) }}" class="mb-3" style="max-width:360px">
        <select name="kelas" class="form-select" onchange="this.form.submit()" data-testid="ekskul-kelas-select">
            <option value="">- Pilih kelas -</option>
            @foreach($kelasList as $k)<option value="{{ $k->id }}" @selected($kelasId === $k->id)>{{ $k->nama }}</option>@endforeach
        </select>
    </form>
    @if($kelasId)
        <form method="POST" action="{{ route('ekskul.tambah', $ekstrakurikuler) }}" data-testid="ekskul-tambah-form">
            @csrf
            <input type="hidden" name="kelas" value="{{ $kelasId }}">
            @if($calon->count())
                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="pilih-semua" data-testid="ekskul-pilih-semua"><label class="form-check-label fw-semibold" for="pilih-semua">Pilih semua</label></div>
                <div class="row g-1 mb-3">
                    @foreach($calon as $s)
                        <div class="col-md-6 col-xl-4"><div class="form-check">
                            <input class="form-check-input cb-calon" type="checkbox" name="siswa_id[]" value="{{ $s->id }}" id="calon-{{ $s->id }}" data-testid="ekskul-calon-{{ $s->id }}">
                            <label class="form-check-label" for="calon-{{ $s->id }}">{{ $s->nama }} <small class="text-muted">{{ $s->nis }}</small></label>
                        </div></div>
                    @endforeach
                </div>
                <button class="btn btn-primary" data-testid="tambah-anggota-button"><i class="bi bi-plus-lg me-1"></i>Tambahkan yang dicentang</button>
            @else
                <p class="text-muted mb-0">Semua siswa aktif di kelas ini sudah menjadi anggota, atau kelas belum memiliki siswa.</p>
            @endif
        </form>
    @endif
</div>
@push('scripts')
<script>
document.getElementById('pilih-semua')?.addEventListener('change', e => document.querySelectorAll('.cb-calon').forEach(c => c.checked = e.target.checked));
</script>
@endpush
@endsection
