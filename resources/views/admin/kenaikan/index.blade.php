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
        @php
            $adaKeputusan = $keputusan->isNotEmpty();
        @endphp
        @if($adaKeputusan)
            @php
                $belum = $asal->siswas->filter(fn ($s) => ! $keputusan->has($s->id))->count();
            @endphp
            <div class="alert alert-info py-2 small" data-testid="kenaikan-info-keputusan"><i class="bi bi-check2-square me-1"></i>Centang otomatis mengikuti keputusan wali kelas (rapor {{ $taGenap->label }}): hanya yang <b>Naik</b>/<b>Lulus</b> dicentang.
                @if($belum)
                    <b class="text-danger">{{ $belum }} siswa belum diputuskan</b> sehingga tidak dicentang.
                @endif
                Siswa dengan kelas tujuan berbeda baru dicentang saat tujuan yang sama dipilih; proses per tujuan.</div>
        @elseif($taGenap)
            <div class="alert alert-warning py-2 small">Wali kelas {{ $asal->nama }} belum mengisi keputusan kenaikan di rapor {{ $taGenap->label }}, jadi semua siswa dicentang. Periksa manual.</div>
        @endif
        <p class="small text-muted">Siswa yang <b>tidak dicentang</b> dianggap tinggal kelas dan tetap berada di {{ $asal->nama }}.</p>
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th style="width:60px"></th><th>NIS</th><th>NISN</th><th>Nama Siswa</th><th>L/P</th>@if($adaKeputusan)<th>Keputusan Wali Kelas</th>@endif</tr></thead>
            <tbody>
            @forelse($asal->siswas as $s)
                @php
                    $kp = $keputusan[$s->id] ?? null;
                @endphp
                @php
                    $dataTujuan = $kp?->keputusan === 'lulus' ? 'lulus' : ($kp?->keputusan === 'naik' ? $idByNama[$kp->kelas_tujuan] ?? '' : '');
                @endphp
                @php
                    $centang = $adaKeputusan ? in_array($kp?->keputusan, ['naik', 'lulus'], true) && $dataTujuan !== '' && (string) $dataTujuan === (string) $preTujuan : true;
                @endphp
                <tr><td><input type="checkbox" class="form-check-input cek-siswa" name="naik[]" value="{{ $s->id }}" @checked($centang) data-status="{{ $kp?->keputusan }}" data-tujuan="{{ $dataTujuan }}" data-testid="kenaikan-siswa-{{ $s->id }}"></td>
                    <td class="font-monospace small">{{ $s->nis }}</td><td class="font-monospace small">{{ $s->nisn ?? '-' }}</td><td class="fw-semibold">{{ $s->nama }}</td><td>{{ $s->jenis_kelamin }}</td>
                    @if($adaKeputusan)
                    <td data-testid="kenaikan-keputusan-{{ $s->id }}">
                        @if($kp?->keputusan === 'naik')
                            <span class="badge text-bg-success">Naik ke {{ $kp->kelas_tujuan }}</span>
                        @elseif($kp?->keputusan === 'lulus')
                            <span class="badge text-bg-success">Lulus</span>
                        @elseif($kp?->keputusan === 'tinggal')
                            <span class="badge text-bg-secondary">Tinggal kelas</span>
                        @else
                            <span class="badge text-bg-warning">Belum diputuskan</span>
                        @endif
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted text-center py-4">Tidak ada siswa di kelas ini.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>3. Tujuan</h2></div>
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Kelas Tujuan</label>
                <select name="tujuan" class="form-select" required data-testid="kenaikan-tujuan-select" onchange="document.getElementById('salin-wrap').style.display = this.value === 'lulus' ? 'none' : ''; terapkanTujuan(this.value)">
                    <option value="">- Pilih -</option>
                    @if($asal->tingkat === 'XII')
                        <option value="lulus" @selected($preTujuan === 'lulus')>LULUS (Alumni)</option>
                    @endif
                    @php
                        $tingkatTujuan = ['X' => 'XI', 'XI' => 'XII'][$asal->tingkat] ?? null;
                    @endphp
                    @foreach($kelasList->where('id', '!=', $asal->id)->where('tingkat', $tingkatTujuan) as $k)
                        <option value="{{ $k->id }}" @selected((string) $preTujuan === (string) $k->id)>{{ $k->nama }} · {{ $k->jurusan->kode }} · Wali: {{ $k->waliKelas?->nama ?? '-' }}</option>
                    @endforeach
                </select>
                <small class="text-muted">@if($tingkatTujuan)Hanya kelas tingkat {{ $tingkatTujuan }} yang ditampilkan. @endif Kelas tujuan belum ada? Buat dulu di menu <a href="{{ route('admin.kelas.create') }}">Kelas & Wali</a>.</small>
            </div>
            <div class="col-md-7" id="salin-wrap" @if($asal->tingkat === 'XII') style="display:none" @endif>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="salin_pengajar" value="1" id="salin" data-testid="kenaikan-salin-pengajar-checkbox">
                    <label class="form-check-label" for="salin">Salin penugasan guru pengajar dari {{ $asal->nama }} ({{ $asal->pengajars->count() }} mapel) ke kelas tujuan. Mapel yang sudah punya guru di kelas tujuan tidak diubah.</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary btn-lg mt-4" @disabled(! $ta || $ta->semester !== 'Ganjil') data-testid="kenaikan-submit-button"><i class="bi bi-arrow-up-circle me-1"></i>Proses Kenaikan Kelas</button>
    </div>
</form>
<script>
// Centang ulang siswa yang diputuskan naik/lulus sesuai kelas tujuan yang dipilih (hanya bila wali kelas sudah memutuskan).
function terapkanTujuan(v) {
    document.querySelectorAll('.cek-siswa').forEach(function (c) {
        if (c.dataset.status === 'naik' || c.dataset.status === 'lulus') c.checked = c.dataset.tujuan === v;
    });
}
</script>
@endif
@endsection
