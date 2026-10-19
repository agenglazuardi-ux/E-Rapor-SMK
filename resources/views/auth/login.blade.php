<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · E-Rapor {{ config('erapor.sekolah.nama') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/erapor.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="login-art">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('logo.png') }}" alt="Logo {{ config('erapor.sekolah.nama') }}" class="brand-mark" style="width:auto;max-width:48px;height:48px;background:transparent;border-radius:0;object-fit:contain;flex-shrink:0;">
           <div><div class="brand-title">E-Rapor SMK</div><div class="brand-sub">{{ config('erapor.sekolah.nama') }}</div></div>
        </div>
        <div style="position:relative">
            <span class="eyebrow" style="color:#9fb5a9">Kurikulum Merdeka</span>
            <h1 class="mt-2">Nilai, capaian, dan rapor <span>dalam satu tempat.</span></h1>
            <p class="mt-3" style="color:#b9cbc1;max-width:440px">Sistem informasi akademik untuk admin, guru, wali kelas, dan siswa. Input nilai akhir, deskripsi capaian kompetensi, rekap kelas, hingga cetak rapor semester.</p>
        </div>
        <small style="color:#7f998c">&copy; {{ date('Y') }} {{ config('erapor.sekolah.nama') }}</small>
    </div>
    <div class="login-form">
        <div class="login-card">
            <h2 class="fw-bold" style="color:var(--forest)">Masuk</h2>
            <!-- <p class="text-muted mb-4">Gunakan NIP (guru), NIS (siswa), atau username admin.</p> -->
            @include('partials.alerts')
            <form method="POST" action="{{ route('login.post') }}" data-testid="login-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control form-control-lg" required autofocus data-testid="login-username-input">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required data-testid="login-password-input">
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" data-testid="login-remember-checkbox">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button class="btn btn-primary btn-lg w-100" data-testid="login-submit-button">Masuk <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
