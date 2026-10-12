<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · E-Rapor {{ config('erapor.sekolah.nama') }}</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/erapor.css') }}" rel="stylesheet">
</head>
<body>
@php($u = auth()->user())
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('logo.png') }}" alt="Logo {{ config('erapor.sekolah.nama') }}" class="brand-mark" style="width:auto;max-width:48px;height:48px;background:transparent;border-radius:0;object-fit:contain;flex-shrink:0;">
            <div>
                <div class="brand-title">E-Rapor SMK</div>
                <div class="brand-sub">{{ config('erapor.sekolah.nama') }}</div>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" data-testid="nav-dashboard"><i class="bi bi-grid-1x2"></i> Dashboard</a>
            @if($u->role === 'admin')
                <div class="nav-label">Data Master</div>
                <a href="{{ route('admin.tahun-ajaran.index') }}" class="{{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}" data-testid="nav-tahun-ajaran"><i class="bi bi-calendar3"></i> Tahun Ajaran</a>
                <a href="{{ route('admin.jurusan.index') }}" class="{{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}" data-testid="nav-jurusan"><i class="bi bi-diagram-3"></i> Jurusan</a>
                <a href="{{ route('admin.guru.index') }}" class="{{ request()->routeIs('admin.guru.*') ? 'active' : '' }}" data-testid="nav-guru"><i class="bi bi-person-badge"></i> Guru</a>
                <a href="{{ route('admin.kelas.index') }}" class="{{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}" data-testid="nav-kelas"><i class="bi bi-door-open"></i> Kelas & Wali</a>
                <a href="{{ route('admin.siswa.index') }}" class="{{ request()->routeIs('admin.siswa.*') && ! request()->routeIs('admin.siswa.import*') ? 'active' : '' }}" data-testid="nav-siswa"><i class="bi bi-people"></i> Siswa</a>
                <a href="{{ route('admin.mapel.index') }}" class="{{ request()->routeIs('admin.mapel.*') ? 'active' : '' }}" data-testid="nav-mapel"><i class="bi bi-journal-bookmark"></i> Mata Pelajaran</a>
                <a href="{{ route('admin.pengajar.index') }}" class="{{ request()->routeIs('admin.pengajar.*') ? 'active' : '' }}" data-testid="nav-pengajar"><i class="bi bi-easel"></i> Guru Pengajar</a>
                <a href="{{ route('admin.siswa.import.form') }}" class="{{ request()->routeIs('admin.siswa.import*') ? 'active' : '' }}" data-testid="nav-import-siswa"><i class="bi bi-file-earmark-excel"></i> Import Siswa (Excel)</a>
                <a href="{{ route('admin.kenaikan.index') }}" class="{{ request()->routeIs('admin.kenaikan.*') ? 'active' : '' }}" data-testid="nav-kenaikan"><i class="bi bi-arrow-up-circle"></i> Kenaikan Kelas</a>
                <div class="nav-label">Rapor</div>
                <a href="{{ route('wali.index') }}" class="{{ request()->routeIs('wali.*') ? 'active' : '' }}" data-testid="nav-rapor-kelas"><i class="bi bi-printer"></i> Rekap & Cetak Rapor</a>
            @elseif($u->role === 'guru')
                <div class="nav-label">Penilaian</div>
                <a href="{{ route('nilai.index') }}" class="{{ request()->routeIs('nilai.*') ? 'active' : '' }}" data-testid="nav-input-nilai"><i class="bi bi-pencil-square"></i> Input Nilai</a>
                <div class="nav-label">Wali Kelas</div>
                <a href="{{ route('wali.index') }}" class="{{ request()->routeIs('wali.*') ? 'active' : '' }}" data-testid="nav-wali-kelas"><i class="bi bi-person-workspace"></i> Kelas Perwalian</a>
            @else
                <div class="nav-label">Akademik</div>
                <a href="{{ route('rapor.saya') }}" class="{{ request()->routeIs('rapor.saya') ? 'active' : '' }}" data-testid="nav-rapor-saya"><i class="bi bi-file-earmark-text"></i> Rapor Saya</a>
            @endif
            <div class="nav-label">Akun</div>
            <a href="{{ route('profil.password') }}" class="{{ request()->routeIs('profil.*') ? 'active' : '' }}" data-testid="nav-ganti-password"><i class="bi bi-key"></i> Ganti Password</a>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <button class="btn btn-sm btn-light d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')" data-testid="toggle-sidebar"><i class="bi bi-list"></i></button>
            <div class="page-heading">
                <span class="eyebrow">@yield('eyebrow', 'Sistem Informasi Akademik')</span>
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            <div class="user-chip">
                <div class="text-end">
                    <div class="fw-semibold" data-testid="current-user-name">{{ $u->name }}</div>
                    <div class="role-badge role-{{ $u->role }}" data-testid="current-user-role">{{ ucfirst($u->role) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-icon" title="Keluar" data-testid="logout-button"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </header>
        <section class="content">
            @include('partials.alerts')
            @yield('content')
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>