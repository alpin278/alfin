<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Panel Admin') — DTE VirtualLab</title>

  <!-- Google Fonts Poppins & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

  <!-- Core Design Tokens & Admin Dashboard Styles -->
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
  <div class="admin-layout-wrapper">
    <!-- Mobile Sidebar Backdrop -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" onclick="toggleAdminSidebar(false)"></div>

    <!-- 1. LEFT SIDEBAR (STICKY DESKTOP, DRAWER MOBILE) -->
    <aside class="admin-sidebar" id="adminSidebar">
      <!-- Sidebar Header -->
      <div class="admin-sidebar-header">
        <a href="{{ route('beranda') }}" class="admin-brand" title="Beranda DTE VirtualLab">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="admin-brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
          <span style="display:none; color: #facc15; font-size: 1.3rem;">⚡</span>
          <div class="admin-brand-text">
            <span class="admin-brand-name">DTE VirtualLab</span>
            <span class="admin-badge-role">PANEL ADMIN</span>
          </div>
        </a>
        <button class="btn-sidebar-close" onclick="toggleAdminSidebar(false)" aria-label="Tutup Menu">✕</button>
      </div>

      <!-- Vertical Navigation Menu -->
      <div class="admin-sidebar-body">
        <div class="admin-nav-section-title">MENU UTAMA</div>
        <nav class="admin-vertical-nav">
          <!-- Kelola Modul -->
          <a href="{{ route('admin.modules.index') }}" onclick="toggleAdminSidebar(false)" class="admin-nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
            <svg class="nav-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span>Kelola Modul</span>
          </a>

          <!-- Progress Siswa -->
          <a href="{{ route('dashboard.progress') }}" onclick="toggleAdminSidebar(false)" class="admin-nav-link {{ request()->routeIs('dashboard.progress') ? 'active' : '' }}">
            <svg class="nav-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"></line>
              <line x1="12" y1="20" x2="12" y2="4"></line>
              <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <span>Daftar Modul Siswa</span>
          </a>

          <!-- Laporan Masuk -->
          @php
            $pendingReportsCount = \App\Models\ReportSubmission::where('status', 'submitted')->count();
          @endphp
          <a href="{{ route('admin.laporan.index') }}" onclick="toggleAdminSidebar(false)" class="admin-nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <svg class="nav-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span style="flex: 1;">Laporan Masuk</span>
            @if($pendingReportsCount > 0)
              <span class="badge-pending-count" style="background-color: #f59e0b; color: #0f172a; font-size: 0.72rem; font-weight: 800; padding: 2px 7px; border-radius: 9999px; line-height: 1; box-shadow: 0 0 8px rgba(245, 158, 11, 0.5);">{{ $pendingReportsCount }}</span>
            @endif
          </a>
        </nav>
      </div>

      <!-- Sidebar Footer (Switch to Student View) -->
      <div class="admin-sidebar-footer">
        <a href="{{ route('materi') }}" onclick="toggleAdminSidebar(false)" class="admin-footer-link" title="Beralih ke tampilan materi pembelajaran siswa">
          <svg class="nav-svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
          </svg>
          <span>🎓 Lihat sebagai Mahasiswa</span>
        </a>
      </div>
    </aside>

    <!-- 2. RIGHT MAIN CONTENT AREA -->
    <div class="admin-main-wrapper">
      <!-- TOPBAR -->
      <header class="admin-topbar">
        <div class="topbar-left">
          <!-- Mobile Hamburger Trigger -->
          <button class="btn-hamburger" onclick="toggleAdminSidebar(true)" aria-label="Buka Menu Sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>

          <!-- Breadcrumb Navigation -->
          <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            @yield('breadcrumb')
          </nav>
        </div>

        <!-- Topbar Right: Avatar Profile & Dropdown -->
        <div class="topbar-right">
          <div class="admin-user-profile-menu" id="adminUserDropdownContainer">
            <button class="btn-avatar-trigger" onclick="toggleUserDropdown(event)" aria-expanded="false" id="avatarTriggerBtn" title="Menu Akun Admin">
              <div class="user-avatar-circle">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
              </div>
              <div class="user-info-text desktop-only">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <span class="user-role-badge">Administrator</span>
              </div>
              <svg class="chevron-svg desktop-only" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>

            <!-- Dropdown Menu -->
            <div class="admin-user-dropdown" id="adminUserDropdown">
              <div class="dropdown-header">
                <p class="dropdown-name">{{ Auth::user()->name }}</p>
                <p class="dropdown-email">{{ Auth::user()->email }}</p>
                <span class="role-pill">Administrator</span>
              </div>
              <div class="dropdown-divider"></div>
              <a href="{{ route('profile.edit') }}" class="dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Pengaturan Profil</span>
              </a>
              <a href="{{ route('materi') }}" class="dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                <span>Halaman Mahasiswa</span>
              </a>
              <div class="dropdown-divider"></div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item dropdown-logout">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                  <span>Keluar / Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Body Content -->
      <main class="admin-content-area">
        @yield('content')
      </main>
    </div>
  </div>

  <!-- Interactive Scripts -->
  <script>
    // 1. Mobile Sidebar Drawer Toggle
    function toggleAdminSidebar(open) {
      const sidebar = document.getElementById('adminSidebar');
      const backdrop = document.getElementById('adminSidebarBackdrop');
      if (open) {
        sidebar?.classList.add('open');
        backdrop?.classList.add('active');
        document.body.style.overflow = 'hidden';
      } else {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('active');
        document.body.style.overflow = '';
      }
    }

    // 2. User Avatar Dropdown Toggle
    function toggleUserDropdown(e) {
      if (e) {
        e.stopPropagation();
      }
      const dropdown = document.getElementById('adminUserDropdown');
      const trigger = document.getElementById('avatarTriggerBtn');
      if (!dropdown) return;
      const isShown = dropdown.classList.toggle('show');
      trigger?.setAttribute('aria-expanded', isShown ? 'true' : 'false');
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('adminUserDropdown');
      const container = document.getElementById('adminUserDropdownContainer');
      if (dropdown && dropdown.classList.contains('show')) {
        if (container && !container.contains(e.target)) {
          dropdown.classList.remove('show');
          document.getElementById('avatarTriggerBtn')?.setAttribute('aria-expanded', 'false');
        }
      }
    });
  </script>
</body>
</html>
