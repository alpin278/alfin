<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Fluxus V2 — Smart Numbering. Virtual Laboratory for Basic Electronics">
  <title>Fluxus — Simulator Rangkaian Listrik</title>

  <!-- Google Fonts: Space Grotesk, Inter, & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

  <!-- CSS Architecture -->
  <link rel="stylesheet" href="{{ asset('css/reset.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/workspace.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/instruments.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">
</head>

<body>
  <div class="app-container" id="app">
    <!-- 1. Header Toolbar (3-Group Flexbox Architecture) -->
    <header class="app-header">
      <!-- GRUP KIRI: Tombol Kembali + Logo Brand (Rapat & Rapi) -->
      <div class="header-left-group">
        @php
          $from = request('from');
          if (!$from && request()->has('case_study_id')) {
            $from = 'studi-kasus';
          }

          $backUrl = match($from) {
            'materi' => route('materi'),
            'studi-kasus' => route('studi-kasus'),
            default => route('beranda')
          };

          $backTitle = match($from) {
            'materi' => 'Kembali ke Materi Pembelajaran',
            'studi-kasus' => 'Kembali ke Daftar Studi Kasus',
            default => 'Kembali ke Beranda / Dasbor'
          };
        @endphp
        <a href="{{ $backUrl }}" id="btn-back-nav" class="btn-tool-icon" style="text-decoration: none; color: #38bdf8; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.3);" title="{{ $backTitle }}">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
        </a>
        <a href="{{ route('beranda') }}" class="brand-logo" title="Beranda Fluxus">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="brand-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
          <span style="color: var(--color-primary-yellow); font-size: 1.3rem; display: none;">⚡</span>
          <span class="brand-title">Fluxus</span>
        </a>
      </div>

      <!-- GRUP TENGAH: Info Konteks / Breadcrumb (Opsional) -->
      <div class="header-center-group">
        @if(request()->has('case_study_id'))
          <span class="header-context-badge">📖 Studi Kasus PBL</span>
        @elseif(request('from') === 'materi')
          <span class="header-context-badge">⚡ Praktik Mandiri</span>
        @endif
      </div>

      <!-- GRUP KANAN: SEMUA Tombol Aksi (Satu Container Flex Sejajar Rapi) -->
      <div class="header-right-group">
        <!-- Undo & Redo -->
        <button class="btn-tool-icon" id="btn-undo" title="Kembalikan (Undo / Ctrl+Z)">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 14 4 9 9 4"></polyline>
            <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
          </svg>
        </button>
        <button class="btn-tool-icon" id="btn-redo" title="Ulangi (Redo / Ctrl+Y)">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 14 20 9 15 4"></polyline>
            <path d="M4 20v-7a4 4 0 0 1 4-4h12"></path>
          </svg>
        </button>

        <!-- Reset Rangkaian -->
        <button class="btn-tool-icon" id="btn-reset-circuit" title="Reset / Kosongkan Rangkaian">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
          </svg>
        </button>

        <!-- Pusatkan Layar -->
        <button class="btn-tool-icon" id="btn-fit-screen" title="Pusatkan Layar (Center / Fit Screen)">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 2v20M2 12h20" />
          </svg>
        </button>

        <!-- Simpan Studi Kasus (Khusus Admin) -->
        @if(Auth::check() && Auth::user()->role === 'admin')
        <button class="btn-tool-icon highlight-admin" id="btn-save-as-case" title="Simpan sebagai Studi Kasus (Admin)">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
          </svg>
        </button>
        @endif

        <!-- Zoom Stepper Controls -->
        <div class="zoom-stepper">
          <button class="btn-step" id="btn-zoom-minus" title="Perkecil">−</button>
          <span class="zoom-text" id="zoom-value">100%</span>
          <button class="btn-step" id="btn-zoom-plus" title="Perbesar">+</button>
        </div>

        <!-- Screenshot Rangkaian -->
        <button class="btn-tool-icon" id="btn-screenshot" title="Ambil Gambar Rangkaian">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
          </svg>
        </button>

        <!-- Tombol Buat Laporan Praktikum (Tersedia & Terintegrasi Rapi) -->
        <button class="btn-tool highlight-report" id="btn-create-report" title="Buat Laporan Praktikum" style="display: none;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
          </svg>
          <span>Buat Laporan</span>
        </button>

        <!-- Link Navigasi Modul & Studi Kasus -->
        <a href="{{ route('materi') }}" class="btn-tool-icon highlight-study" style="text-decoration: none;" title="Daftar Modul Pembelajaran">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
          </svg>
        </a>
        <a href="{{ route('studi-kasus') }}" class="btn-tool-icon highlight-study" style="text-decoration: none;" title="Daftar Studi Kasus (PBL)">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        </a>

        <!-- Info / Panduan -->
        <button class="btn-tool-icon" id="btn-help" title="Panduan Penggunaan">?</button>
      </div>
    </header>

    <!-- 2. Main Work Area -->
    <main class="app-main">
      <!-- Left Sidebar: Component Palette List -->
      <aside class="sidebar-palette" id="sidebar-palette">
        <div class="palette-top">
          <div class="sidebar-heading">KOMPONEN</div>
          <div class="palette-search">
            <input type="text" class="search-input" id="palette-search-input" placeholder="🔍 Cari komponen...">
          </div>
        </div>

        <div class="palette-content" id="palette-list">
          <!-- SUMBER -->
          <div class="category-group">
            <div class="category-title">SUMBER</div>
            <div class="component-grid">
              <div class="component-card" data-component-type="battery" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="10" x="2" y="7" rx="2"></rect><line x1="22" x2="22" y1="11" y2="13"></line></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Baterai DC</span>
                  <span class="component-item-sub">12V Sumber Daya</span>
                </div>
              </div>
            </div>
          </div>

          <!-- KONTROL -->
          <div class="category-group">
            <div class="category-title">KONTROL</div>
            <div class="component-grid">
              <div class="component-card" data-component-type="switch_spst" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><circle cx="6" cy="12" r="2"></circle><circle cx="18" cy="12" r="2"></circle></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Saklar Rocker</span>
                  <span class="component-item-sub">ON/OFF Industrial</span>
                </div>
              </div>
            </div>
          </div>

          <!-- BEBAN -->
          <div class="category-group">
            <div class="category-title">BEBAN</div>
            <div class="component-grid">
              <div class="component-card" data-component-type="lamp" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Lampu Pijar</span>
                  <span class="component-item-sub">12V / 20W</span>
                </div>
              </div>
              <div class="component-card" data-component-type="led" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="6"></circle><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">LED Merah</span>
                  <span class="component-item-sub">2V / 20mA</span>
                </div>
              </div>
              <div class="component-card" data-component-type="motor_dc" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Motor DC</span>
                  <span class="component-item-sub">12V / 3000 RPM</span>
                </div>
              </div>
            </div>
          </div>

          <!-- PASIF & DISKRET -->
          <div class="category-group">
            <div class="category-title">PASIF & DISKRET</div>
            <div class="component-grid">
              <div class="component-card" data-component-type="resistor" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h3l2-6 4 12 4-12 2 6h5"></path></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Resistor</span>
                  <span class="component-item-sub">220 Ω</span>
                </div>
              </div>
              <div class="component-card" data-component-type="diode" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="6 4 18 12 6 20 6 4"></polygon><line x1="18" y1="4" x2="18" y2="20"></line></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Dioda 1N4007</span>
                  <span class="component-item-sub">Vf: 0.7V Silikon</span>
                </div>
              </div>
            </div>
          </div>

          <!-- INSTRUMEN -->
          <div class="category-group">
            <div class="category-title">INSTRUMEN</div>
            <div class="component-grid">
              <div class="component-card" data-component-type="multimeter" draggable="true">
                <div class="component-card-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
                </div>
                <div class="component-card-info">
                  <span class="component-item-name">Multimeter</span>
                  <span class="component-item-sub">Volt / Ohm Meter</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Center: Workspace Canvas -->
      <section class="workspace-wrapper" id="workspace-container">
        <!-- Floating Live Simulation Metrics Banner -->
        <div class="simulation-metrics-banner side-metrics" id="sim-metrics-ribbon">
          <div class="metric-item">
            <span class="metric-label">Tegangan:</span>
            <span class="metric-val" id="metric-voltage">0.0 V</span>
          </div>
          <div class="metric-divider"></div>
          <div class="metric-item">
            <span class="metric-label">Arus:</span>
            <span class="metric-val" id="metric-current">0.00 A</span>
          </div>
          <div class="metric-divider"></div>
          <div class="metric-item">
            <span class="metric-label">Daya:</span>
            <span class="metric-val" id="metric-power">0.00 W</span>
          </div>
          <div class="metric-divider"></div>
          <div class="metric-item">
            <span class="metric-label">Status:</span>
            <span class="metric-status-badge status-standby" id="metric-status">Standby</span>
          </div>
        </div>

        <!-- Canvas Viewport -->
        <div class="workspace-canvas" id="workspace-canvas">
          <div class="canvas-transform-layer" id="canvas-layer">
            <!-- SVG Cables Layer -->
            <svg class="cables-svg-layer" id="svg-cable-layer">
              <defs>
                <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                  <feGaussianBlur stdDeviation="3" result="blur" />
                  <feComposite in="SourceGraphic" in2="blur" operator="over" />
                </filter>
              </defs>
              <g id="wires-group"></g>
              <path id="wire-preview" class="circuit-wire-preview" d="" style="display: none;"></path>
            </svg>

            <!-- Components Layer -->
            <div class="components-layer" id="components-layer"></div>
          </div>
        </div>

        <!-- Floating Bottom Workspace Toolbar (Desktop) -->
        <div class="workspace-bottom-toolbar">
          <button class="bottom-tool-btn sim-btn" id="btn-bottom-run-sim" title="Jalankan / Hentikan Simulasi Listrik">
            <svg class="sim-play-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
          </button>
          <div class="bottom-tool-divider"></div>
          <button class="bottom-tool-btn active" id="tool-select" title="Pilih Komponen (Select)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M4 2l16 11-8 2-4 8L4 2z" />
            </svg>
          </button>
          <button class="bottom-tool-btn" id="tool-pan" title="Geser Papan (Pan)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path
                d="M18 11V6a2 2 0 00-4 0v5M14 10V4a2 2 0 00-4 0v7M10 10.5V6a2 2 0 00-4 0v8a7 7 0 0014 0v-3a2 2 0 00-4 0" />
            </svg>
          </button>
          <button class="bottom-tool-btn" id="tool-zoom-in" title="Perbesar (Zoom In)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <line x1="21" y1="21" x2="16.65" y2="16.65" />
              <line x1="11" y1="8" x2="11" y2="14" />
              <line x1="8" y1="11" x2="14" y2="11" />
            </svg>
          </button>
          <button class="bottom-tool-btn" id="tool-zoom-out" title="Perkecil (Zoom Out)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <line x1="21" y1="21" x2="16.65" y2="16.65" />
              <line x1="8" y1="11" x2="14" y2="11" />
            </svg>
          </button>
          <button class="bottom-tool-btn" id="tool-fit" title="Pusatkan (Fit Screen)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3" />
            </svg>
          </button>
          <button class="bottom-tool-btn danger" id="tool-delete" title="Hapus Komponen / Kabel Terpilih (Delete)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6" />
              <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
          </button>
        </div>
      </section>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
      <button class="nav-item active" id="nav-btn-components">
        <span class="nav-item-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
        </span>
        <span>Komponen</span>
      </button>
      <button class="nav-item" id="nav-btn-wires">
        <span class="nav-item-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </span>
        <span>Kabel</span>
      </button>
      <button class="nav-item" id="nav-btn-meter">
        <span class="nav-item-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
        </span>
        <span>Alat Ukur</span>
      </button>
      <button class="nav-item" id="nav-btn-sim">
        <span class="nav-item-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
        </span>
        <span>Simulasi</span>
      </button>
    </nav>
  </div>

  <!-- JavaScript Entry Point -->
  <script type="module" src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
</body>

</html>
