<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Studi Kasus Problem-Based Learning (PBL) — Fluxus">
  <title>Studi Kasus (PBL) — Fluxus</title>

  <!-- Google Fonts: Space Grotesk, Inter, & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="{{ asset('css/studi-kasus.css') }}">
</head>
<body>
  <!-- Universal Shared Navigation (Topbar, Mobile Drawer, and Avatar Dropdown) -->
  @include('partials.navbar')

  <!-- 2. Breadcrumb Navigation -->
  <nav class="breadcrumb-container" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
      <li class="breadcrumb-separator">/</li>
      <li class="breadcrumb-item active" aria-current="page">Studi Kasus (PBL)</li>
    </ol>
  </nav>

  <!-- 3. Content -->
  <main class="cases-page-container">
    <div class="cases-header-section">
      <h1 class="cases-page-title">STUDI KASUS PEMBELAJARAN (PBL)</h1>
      <p class="cases-page-desc">
        Selesaikan permasalahan kelistrikan nyata (<em>Troubleshooting & Analysis</em>) dengan menganalisis rangkaian dan melakukan pengukuran langsung di simulator.
      </p>
    </div>

    <div class="cases-list-grid">
      @forelse($caseStudies as $index => $case)
      <div class="case-card">
        <div class="case-card-top">
          <div class="case-meta-group">
            <span class="case-number">KASUS {{ sprintf('%02d', $index + 1) }}</span>
            @if($case->creator)
              <span class="case-difficulty" style="background: var(--color-bg-surface-soft, #eaf2ff); color: var(--color-primary, #2563eb); border: 1px solid #bfdbfe;">
                {{ $case->creator->name }}
              </span>
            @else
              <span class="case-difficulty diff-easy">Praktikum Fluxus</span>
            @endif
          </div>
          <div class="case-icon-wrapper">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path>
              <path d="M9 18h6"></path>
              <path d="M10 22h4"></path>
            </svg>
          </div>
        </div>
        <h3 class="case-title">{{ $case->title }}</h3>
        <p class="case-desc">
          {{ $case->description }}
        </p>
        <a href="{{ route('simulasi', ['case_study_id' => $case->id, 'from' => 'studi-kasus']) }}" class="btn-start-case">
          <span>Mulai Kasus {{ sprintf('%02d', $index + 1) }}</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
        </a>
      </div>
      @empty
      <div style="grid-column: 1 / -1; text-align: center; padding: 48px 24px; background: #ffffff; border: 1px dashed var(--color-border, #dce5f0); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.04));">
        <p style="color: var(--color-text-secondary, #64748b); font-size: 1rem; margin-bottom: 16px;">Belum ada studi kasus yang tersedia saat ini.</p>
        @if(Auth::check() && Auth::user()->role === 'admin')
          <a href="{{ route('simulasi', ['from' => 'studi-kasus']) }}" class="btn-start-case" style="display: inline-flex; width: auto; padding: 10px 24px;">
            <span>Buka Simulator & Buat Kasus Baru</span>
          </a>
        @endif
      </div>
      @endforelse
    </div>
  </main>

  <!-- Footer -->
  <footer class="home-footer">
    <div class="footer-brand">Virtual Laboratory Pengukuran Listrik</div>
    <div>Problem-Based Learning (PBL) untuk Penelitian & Pembelajaran SMK — Universitas Negeri Padang</div>
  </footer>
</body>

</html>
