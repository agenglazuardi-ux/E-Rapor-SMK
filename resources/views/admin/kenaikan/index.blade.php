@extends('layouts.app')
@section('title', 'Kenaikan Kelas & Kelulusan')
@section('eyebrow', $ta ? 'TA aktif '.$ta->label : 'Data Master')
@section('content')
@if(! $ta || $ta->semester !== 'Ganjil')
    <div class="alert alert-warning" data-testid="kenaikan-warning"><i class="bi bi-info-circle me-1"></i>
        Kenaikan kelas diproses di <b>awal tahun ajaran baru</b>. Buat & aktifkan dulu Tahun Ajaran baru semester <b>Ganjil</b> di menu <a href="{{ route('admin.tahun-ajaran.index') }}">Tahun Ajaran</a>.
        Riwayat kelas semester lama otomatis dibekukan sehingga rapor lama tetap menampilkan kelas & wali kelas yang benar.
    </div>
@endif

<div class="panel">
    <div class="panel-head"><h2>1. Pilih Kelas Asal</h2></div>
    <form class="d-flex gap-2 flex-wrap">
        <select name="kelas_asal" class="form-select w-auto" onchange="this.form.submit()" data-testid="kenaikan-kelas-asal-select">
            <option value="">- Pilih kelas -</option>
            @foreach($kelasList as $k)<option value="{{ $k->id }}" @selected($asal?->id === $k->id)>{{ $k->nama }} ({{ $k->siswas_count }} siswa)</option>@endforeach
        </select>
    </form>
</div>

@if($asal)
<form method="POST" action="{{ route('admin.kenaikan.proses') }}" data-testid="kenaikan-form" onsubmit="return confirm('Proses kenaikan kelas untuk siswa yang dicentang?')">
    @csrf
    <input type="hidden" name="kelas_asal" value="{{ $asal->id }}">
    <div class="panel">
        <div class="panel-head">
            <h2>2. Centang Siswa yang Naik / Lulus</h2>
            <div class="form-check"><input type="checkbox" class="form-check-input" id="cek-semua" checked onchange="document.querySelectorAll('.cek-siswa').forEach(c => c.checked = this.checked)" data-testid="kenaikan-cek-semua"><label class="form-check-label" for="cek-semua">Pilih semua</label></div>
        </div>
        <p class="small text-muted">Siswa yang <b>tidak dicentang</b> dianggap tinggal kelas dan tetap berada di {{ $asal->nama }}.</p>
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th style="width:60px"></th><th>NIS</th><th>Nama Siswa</th><th>L/P</th></tr></thead>
            <tbody>
            @forelse($asal->siswas as $s)
                <tr><td><input type="checkbox" class="form-check-input cek-siswa" name="naik[]" value="{{ $s->id }}" checked data-testid="kenaikan-siswa-{{ $s->id }}"></td>
                    <td class="font-monospace small">{{ $s->nis }}</td><td class="fw-semibold">{{ $s->nama }}</td><td>{{ $s->jenis_kelamin }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-4">Tidak ada siswa di kelas ini.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>3. Tujuan</h2></div>
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Kelas Tujuan</label>
                <select name="tujuan" class="form-select" required data-testid="kenaikan-tujuan-select" onchange="document.getElementById('salin-wrap').style.display = this.value === 'lulus' ? 'none' : ''">
                    <option value="">- Pilih -</option>
                    @if($asal->tingkat === 'XII')<option value="lulus">LULUS (Alumni)</option>@endif
                    @foreach($kelasList->where('id', '!=', $asal->id) as $k)<option value="{{ $k->id }}">{{ $k->nama }} · {{ $k->jurusan->kode }} · Wali: {{ $k->waliKelas?->nama ?? '-' }}</option>@endforeach
                </select>
                <small class="text-muted">Kelas tujuan belum ada? Buat dulu di menu <a href="{{ route('admin.kelas.create') }}">Kelas & Wali</a>.</small>
            </div>
            <div class="col-md-7" id="salin-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="salin_pengajar" value="1" id="salin" data-testid="kenaikan-salin-pengajar-checkbox">
                    <label class="form-check-label" for="salin">Salin penugasan guru pengajar dari {{ $asal->nama }} ({{ $asal->pengajars->count() }} mapel) ke kelas tujuan. Mapel yang sudah punya guru di kelas tujuan tidak diubah.</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary btn-lg mt-4" @disabled(! $ta || $ta->semester !== 'Ganjil') data-testid="kenaikan-submit-button"><i class="bi bi-arrow-up-circle me-1"></i>Proses Kenaikan Kelas</button>
    </div>
</form>
@endif
@endsection
