<!-- Mobile Drawer Backdrop -->
<div class="student-drawer-backdrop" id="studentDrawerBackdrop" onclick="toggleStudentDrawer(false)"></div>

<!-- Mobile Drawer Menu -->
<aside class="student-drawer" id="studentDrawer">
  <div class="student-drawer-header">
    <a href="{{ route('beranda') }}" class="brand-logo" style="text-decoration: none;">
      <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="brand-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
      <span style="color: #facc15; font-size: 1.3rem; display: none;">⚡</span>
      <span class="brand-title">Fluxus</span>
    </a>
    <button onclick="toggleStudentDrawer(false)" style="background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; padding: 4px;" aria-label="Tutup Menu">✕</button>
  </div>

  <nav class="student-drawer-nav">
    <a href="{{ route('beranda') }}" class="drawer-link {{ request()->routeIs('beranda') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
      <span>Beranda</span>
    </a>
    <a href="{{ route('materi') }}" class="drawer-link {{ request()->routeIs('materi*') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
      <span>Materi Pembelajaran</span>
    </a>
    <a href="{{ route('simulasi') }}" class="drawer-link {{ request()->routeIs('simulasi*') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
      <span>Simulator Praktikum</span>
    </a>
    <a href="{{ route('studi-kasus') }}" class="drawer-link {{ request()->routeIs('studi-kasus*') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
      <span>Studi Kasus (PBL)</span>
    </a>
  </nav>

  @auth
    <div style="border-top: 1px solid #334155; padding-top: 16px; margin-top: auto; display: flex; flex-direction: column; gap: 10px;">
      <!-- Clickable Profile Area -->
      <a href="{{ route('profile.edit') }}" onclick="toggleStudentDrawer(false)" style="display: flex; align-items: center; gap: 10px; text-decoration: none; padding: 6px; border-radius: 8px; transition: background 0.15s;" onmouseover="this.style.background='rgba(30, 41, 59, 0.7)'" onmouseout="this.style.background='transparent'" title="Buka Pengaturan Profil">
        <div class="user-avatar-sm">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
        <div style="min-width: 0; flex: 1;">
          <div style="font-size: 0.85rem; font-weight: 700; color: #f8fafc; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Auth::user()->name }}</div>
          <div style="font-size: 0.72rem; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Auth::user()->email }}</div>
        </div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>

      @if(Auth::user()->role !== 'admin')
        <a href="{{ route('laporan.saya') }}" class="drawer-link {{ request()->routeIs('laporan.saya') ? 'active' : '' }}" style="padding: 8px 10px; font-size: 0.82rem;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          <span>Laporan Saya</span>
        </a>
      @endif
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout-clean" style="display: block; width: 100%; text-align: center; padding: 8px;">Keluar / Logout</button>
      </form>
    </div>
  @endauth
</aside>

<!-- 1. Universal Topbar Header -->
<header class="home-header">
  <div class="header-grid-3">
    <!-- Left: Mobile Trigger + Brand Logo -->
    <div class="header-brand-side">
      <button class="btn-mobile-student-menu" onclick="toggleStudentDrawer(true)" aria-label="Buka Menu Navigasi">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
      </button>
      <a href="{{ route('beranda') }}" class="home-logo" title="Beranda Fluxus">
        <div class="brand-logo-img-wrapper">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo UNP/PTEI" class="brand-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
          <span class="brand-logo-fallback" style="display: none; color: var(--color-accent-yellow);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
          </span>
        </div>
        <div class="logo-text-group">
          <span class="logo-text">Fluxus</span>
          <span class="logo-badge">V2.0</span>
        </div>
      </a>
    </div>

    <!-- Center: Navigation Links (Desktop) -->
    <nav class="header-nav-center">
      <a href="{{ route('beranda') }}" class="nav-item {{ request()->routeIs('beranda') ? 'active' : '' }}">Beranda</a>
      <a href="{{ route('materi') }}" class="nav-item {{ request()->routeIs('materi*') ? 'active' : '' }}">Materi</a>
      <a href="{{ route('simulasi') }}" class="nav-item {{ request()->routeIs('simulasi*') ? 'active' : '' }}">Simulator</a>
      <a href="{{ route('studi-kasus') }}" class="nav-item {{ request()->routeIs('studi-kasus*') ? 'active' : '' }}">Studi Kasus</a>
    </nav>

    <!-- Right: User Avatar Dropdown -->
    <div class="header-user-side">

      @auth
        <div class="student-user-profile-menu" id="studentUserDropdownContainer">
          <button class="btn-student-avatar-trigger" onclick="toggleStudentDropdown(event)" aria-expanded="false" id="studentAvatarTriggerBtn" title="Menu Profil Saya">
            <div class="user-avatar-sm">
              {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <span class="user-name-text">{{ Auth::user()->name }}</span>
            <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </button>

          <!-- Dropdown Menu -->
          <div class="student-user-dropdown" id="studentUserDropdown">
            <div class="dropdown-header">
              <p class="dropdown-name">{{ Auth::user()->name }}</p>
              <p class="dropdown-email">{{ Auth::user()->email }}</p>
              <span class="role-pill {{ Auth::user()->role === 'admin' ? 'role-admin' : '' }}">
                {{ Auth::user()->role === 'admin' ? 'Administrator' : 'Mahasiswa' }}
              </span>
            </div>
            <div class="dropdown-divider"></div>
            @if(Auth::user()->role !== 'admin')
              <a href="{{ route('laporan.saya') }}" class="dropdown-item {{ request()->routeIs('laporan.saya') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Laporan Saya</span>
              </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="dropdown-item">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <span>Pengaturan Profil</span>
            </a>
            @if(Auth::user()->role === 'admin')
              <a href="{{ route('admin.modules.index') }}" class="dropdown-item" style="color: #c084fc;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>Panel Admin</span>
              </a>
            @endif
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item dropdown-logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Keluar / Logout</span>
              </button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-cta-sim" style="padding: 6px 16px;">Masuk</a>
      @endauth
    </div>
  </div>
</header>

<!-- Universal Navbar Scripts -->
<script>
  function toggleStudentDrawer(open) {
    const drawer = document.getElementById("studentDrawer");
    const backdrop = document.getElementById("studentDrawerBackdrop");
    if (open) {
      drawer?.classList.add("open");
      backdrop?.classList.add("active");
      document.body.style.overflow = "hidden";
    } else {
      drawer?.classList.remove("open");
      backdrop?.classList.remove("active");
      document.body.style.overflow = "";
    }
  }

  function toggleStudentDropdown(e) {
    if (e) {
      e.stopPropagation();
    }
    const dropdown = document.getElementById("studentUserDropdown");
    const trigger = document.getElementById("studentAvatarTriggerBtn");
    if (!dropdown) return;
    const isShown = dropdown.classList.toggle("show");
    trigger?.setAttribute("aria-expanded", isShown ? "true" : "false");
  }

  // Close dropdown on outside click
  document.addEventListener("click", function(e) {
    const dropdown = document.getElementById("studentUserDropdown");
    const container = document.getElementById("studentUserDropdownContainer");
    if (dropdown && dropdown.classList.contains("show")) {
      if (container && !container.contains(e.target)) {
        dropdown.classList.remove("show");
        document.getElementById("studentAvatarTriggerBtn")?.setAttribute("aria-expanded", "false");
      }
    }
  });
</script>
