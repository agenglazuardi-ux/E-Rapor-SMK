@extends('layouts.app')
@section('title', 'Ganti Password')
@section('content')
<div class="panel" style="max-width:520px">
    <form method="POST" action="{{ route('profil.password.update') }}">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Password Lama</label><input type="password" name="password_lama" class="form-control" required data-testid="password-lama-input"></div>
        <div class="mb-3"><label class="form-label">Password Baru</label><input type="password" name="password" class="form-control" required data-testid="password-baru-input"></div>
        <div class="mb-4"><label class="form-label">Ulangi Password Baru</label><input type="password" name="password_confirmation" class="form-control" required data-testid="password-konfirmasi-input"></div>
        <button class="btn btn-primary" data-testid="password-submit-button">Simpan Password</button>
    </form>
</div>
@endsection
