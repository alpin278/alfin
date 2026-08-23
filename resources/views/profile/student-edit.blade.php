<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pengaturan Profil — DTE VirtualLab</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>
  <!-- Universal Shared Navigation -->
  @include('partials.navbar')

  <!-- Breadcrumb Navigation -->
  <nav class="breadcrumb-container" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
      <li class="breadcrumb-separator">/</li>
      <li class="breadcrumb-item active" aria-current="page">Pengaturan Profil</li>
    </ol>
  </nav>

  <!-- Main Profile Content -->
  <main class="profile-page-container">
    <div class="profile-top-header">
      <a href="{{ route('beranda') }}" class="btn-profile-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        <span>Kembali ke Beranda</span>
      </a>

      <h1 class="profile-page-title">
        <span style="color: var(--color-primary-light); display: inline-flex;">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </span>
        <span>PENGATURAN PROFIL SAYA</span>
      </h1>
      <p class="profile-page-desc">Kelola data profil, Nomor Induk Mahasiswa (NIM), dan keamanan akun praktikum Anda.</p>
    </div>

    <div class="profile-cards-stack">
      @include('profile.partials.update-profile-information-form')
      @include('profile.partials.update-password-form')
      @include('profile.partials.delete-user-form')
    </div>
  </main>
</body>
</html>