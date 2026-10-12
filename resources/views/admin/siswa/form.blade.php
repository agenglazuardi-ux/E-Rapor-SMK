@extends('layouts.app')
@section('title', $item->exists ? 'Edit Siswa' : 'Tambah Siswa')
@section('eyebrow', 'Data Master')
@section('content')
<div class="panel" style="max-width:860px">
    <form method="POST" action="{{ $item->exists ? route('admin.siswa.update', $item) : route('admin.siswa.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">NIS (username)</label><input name="nis" class="form-control" value="{{ old('nis', $item->nis) }}" required data-testid="siswa-nis-input"></div>
            <div class="col-md-4"><label class="form-label">NISN</label><input name="nisn" class="form-control" value="{{ old('nisn', $item->nisn) }}" data-testid="siswa-nisn-input"></div>
            <div class="col-md-4"><label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" data-testid="siswa-kelas-select"><option value="">- Pilih -</option>
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(old('kelas_id', $item->kelas_id) == $k->id)>{{ $k->nama }}</option>@endforeach
                </select></div>
            <div class="col-md-8"><label class="form-label">Nama Lengkap</label><input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" required data-testid="siswa-nama-input"></div>
            <div class="col-md-4"><label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select" data-testid="siswa-jk-select">
                    <option value="L" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'P')>Perempuan</option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Tempat Lahir</label><input name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $item->tempat_lahir) }}" data-testid="siswa-tempat-lahir-input"></div>
            <div class="col-md-6"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $item->tanggal_lahir?->format('Y-m-d')) }}" data-testid="siswa-tanggal-lahir-input"></div>
            <div class="col-md-6"><label class="form-label">Nama Orang Tua / Wali</label><input name="nama_ortu" class="form-control" value="{{ old('nama_ortu', $item->nama_ortu) }}" data-testid="siswa-ortu-input"></div>
            <div class="col-md-6"><label class="form-label">Password {{ $item->exists ? '(kosongkan jika tidak diubah)' : '(kosong = NIS)' }}</label><input type="password" name="password" class="form-control" data-testid="siswa-password-input"></div>
            <div class="col-md-6"><label class="form-label">Status</label>
                <select name="status" class="form-select" data-testid="siswa-status-select">
                    @foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah / Keluar'] as $v => $l)<option value="{{ $v }}" @selected(old('status', $item->status ?? 'aktif') === $v)>{{ $l }}</option>@endforeach
                </select>
                <small class="text-muted">Status Lulus/Pindah otomatis mengosongkan kelas.</small></div>
            <div class="col-md-6"><label class="form-label">Tahun Lulus</label><input type="number" name="tahun_lulus" class="form-control" value="{{ old('tahun_lulus', $item->tahun_lulus) }}" placeholder="2026" data-testid="siswa-tahun-lulus-input"></div>
            <div class="col-12"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2" data-testid="siswa-alamat-input">{{ old('alamat', $item->alamat) }}</textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" data-testid="siswa-submit-button">Simpan</button><a href="{{ route('admin.siswa.index') }}" class="btn btn-light">Batal</a></div>
    </form>
</div>
@endsection
