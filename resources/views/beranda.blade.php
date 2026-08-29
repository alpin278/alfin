<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Virtual Laboratory Pengukuran Listrik — Media Pembelajaran Interaktif Dasar Teknik Elektro">
  <title>Fluxus — Laboratorium Pengukuran Listrik</title>

  <!-- Google Fonts: Space Grotesk, Inter, & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

  <!-- Home Page Styles -->
  <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>
  <!-- Universal Shared Navigation (Topbar, Mobile Drawer, and Avatar Dropdown) -->
  @include('partials.navbar')

  <!-- 2. Hero Section -->
  <section class="hero-section">
    <div class="hero-pill">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.5 2v17.5c0 1.4-1.1 2.5-2.5 2.5h0c-1.4 0-2.5-1.1-2.5-2.5V2"></path>
        <path d="M8.5 2h7"></path>
        <path d="M14.5 16h-5"></path>
      </svg>
      <span>Media Pembelajaran Interaktif (Pre-Laboratory)</span>
    </div>
    <h1 class="hero-title">LABORATORIUM PENGUKURAN LISTRIK</h1>
    <h2 class="hero-subtitle">Belajar konsep, melakukan simulasi, dan memecahkan masalah pengukuran listrik secara interaktif.</h2>
    <p class="hero-desc">
      Virtual Laboratory ini dirancang sebagai media pembelajaran untuk membantu siswa memahami konsep pengukuran listrik, cara kerja instrumen ukur, dan analisis rangkaian sebelum melaksanakan praktikum di laboratorium nyata.
    </p>
  </section>

  <!-- 3. Three Main Activity Cards -->
  <section class="main-activities-section">
    <div class="activity-grid">
      <!-- CARD 1: MATERI -->
      <div class="activity-card">
        <div class="card-icon-wrapper">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
            <path d="M6 6h10"></path>
            <path d="M6 10h10"></path>
          </svg>
        </div>
        <h3 class="card-title">MATERI</h3>
        <p class="card-desc">
          Pelajari konsep dasar pengukuran listrik, besaran tegangan, hambatan, cara pembacaan gelang resistor, dan panduan penggunaan multimeter sebelum melakukan simulasi.
        </p>
        <a href="{{ route('materi') }}" class="card-action-btn">
          <span>Mulai Belajar</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
          </svg>
        </a>
      </div>

      <!-- CARD 2: SIMULASI (FEATURED) -->
      <div class="activity-card featured">
        <span class="card-badge-top">Utama</span>
        <div class="card-icon-wrapper">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
          </svg>
        </div>
        <h3 class="card-title">SIMULASI</h3>
        <p class="card-desc">
          Praktikkan penyusunan rangkaian dan pengukuran listrik secara langsung melalui Virtual Laboratory dengan sistem pengkabelan cerdas, multimeter interaktif, dan beban dinamis.
        </p>
        <a href="{{ route('simulasi') }}" class="card-action-btn">
          <span>Mulai Simulasi</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
          </svg>
        </a>
      </div>

      <!-- CARD 3: STUDI KASUS -->
      <div class="activity-card">
        <div class="card-icon-wrapper">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19.439 7.85c0-1.57.802-2.54 2.14-2.54 1.34 0 2.14.97 2.14 2.54 0 1.58-.8 2.55-2.14 2.55-1.338 0-2.14-.97-2.14-2.55Z"></path>
            <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"></path>
            <circle cx="12" cy="13" r="3"></circle>
          </svg>
        </div>
        <h3 class="card-title">STUDI KASUS</h3>
        <p class="card-desc">
          Gunakan konsep dan hasil pengukuran untuk memecahkan permasalahan nyata dalam rangkaian listrik berbasis pembelajaran <em>Problem-Based Learning</em> (PBL).
        </p>
        <a href="{{ route('studi-kasus') }}" class="card-action-btn">
          <span>Mulai Kasus</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- 4. Learning Topics Overview (Centered Content) -->
  <section class="features-section">
    <div class="section-header">
      <h3 class="section-title">Indikator Pembelajaran Utama</h3>
      <p class="section-desc">Materi dan simulasi dirancang terstruktur sesuai kurikulum kejuruan teknik elektro.</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon-wrapper">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
          </svg>
        </div>
        <div class="feature-title">Tegangan & Arus</div>
        <div class="feature-sub">Pemahaman beda potensial, sumber daya DC, dan aliran arus dalam sirkuit tertutup.</div>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrapper">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12h3l2-6 4 12 4-12 2 6h5"></path>
          </svg>
        </div>
        <div class="feature-title">Hambatan & Resistor</div>
        <div class="feature-sub">Hukum Ohm (V = I · R) dan pembacaan kode warna 4 gelang resistor standar EIA.</div>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrapper">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="3" rx="2"></rect>
            <path d="M3 9h18"></path>
            <path d="M9 21V9"></path>
          </svg>
        </div>
        <div class="feature-title">Multimeter Digital</div>
        <div class="feature-sub">Pengukuran voltmeter (paralel), amperemeter (seri), dan ohmmeter dengan benar.</div>
      </div>

      <div class="feature-box">
        <div class="feature-icon-wrapper">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
          </svg>
        </div>
        <div class="feature-title">Beban & Komponen</div>
        <div class="feature-sub">Karakteristik Lampu Pijar, LED, Dioda Semikonduktor, Motor Listrik, dan Saklar.</div>
      </div>
    </div>
  </section>

  <!-- 5. Footer -->
  <footer class="home-footer">
    <div class="footer-brand">Virtual Laboratory Pengukuran Listrik</div>
    <div>Media pembelajaran interaktif untuk SMK & Mahasiswa Teknik Elektro — Universitas Negeri Padang</div>
  </footer>
</body>

</html>
