<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Materi Pembelajaran — DTE VirtualLab</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>
<body>
  <!-- Universal Shared Navigation (Topbar, Mobile Drawer, and Avatar Dropdown) -->
  @include('partials.navbar')

  <!-- 2. Single Breadcrumb Navigation (No Overlapping) -->
  <nav class="breadcrumb-container" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
      <li class="breadcrumb-separator">/</li>
      <li class="breadcrumb-item active" aria-current="page">Materi Pembelajaran</li>
    </ol>
  </nav>

  <!-- 3. Content Grid (Dinamis dari Database) -->
  <main class="materi-page-container">
    <div class="materi-header-section">
      <h1 class="materi-page-title">MATERI PEMBELAJARAN</h1>
      <p class="materi-page-desc">
        Pahami teori dasar, rumus fisika, panduan penggunaan alat ukur, dan kaidah analisis kelistrikan sebelum memulai praktikum simulasi.
      </p>
    </div>

    <div class="materi-list-grid">
      @forelse($modules as $module)
        @php
          $status = $module->user_status ?? 'belum_mulai';
          $progressPercent = $status === 'selesai' ? 100 : ($status === 'sedang_berjalan' ? 50 : 0);
          
          $badgeClass = match($status) {
            'selesai' => 'background: rgba(16, 185, 129, 0.18); color: #10b981; border: 1px solid #10b981;',
            'sedang_berjalan' => 'background: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid #f59e0b;',
            default => 'background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid #475569;'
          };

          $badgeText = match($status) {
            'selesai' => '✓ Selesai',
            'sedang_berjalan' => '⚡ Sedang Berjalan',
            default => '○ Belum Dimulai'
          };
        @endphp

        <div class="materi-card" id="card-modul-{{ $module->id }}">
          <div class="materi-card-top">
            <div class="materi-card-meta">
              <span class="materi-number">MODUL {{ sprintf('%02d', $module->module_number) }}</span>
              <span class="status-badge" style="font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; {{ $badgeClass }}">
                {{ $badgeText }}
              </span>
            </div>
            <div class="materi-icon-wrapper">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
            </div>
          </div>

          <h3 class="materi-title">{{ $module->title }}</h3>
          <p class="materi-desc">{{ $module->description }}</p>

          <!-- Progress Bar Dinamis -->
          <div class="materi-progress-bar-track">
            <div class="materi-progress-bar-fill" style="width: {{ $progressPercent }}%;"></div>
          </div>

          <div class="materi-card-actions">
            <button class="btn-learn" onclick="startModuleLearn({{ $module->id }}, {{ $module->module_number }})">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
              <span>Pelajari</span>
            </button>
            <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-practice" onclick="markModuleInProgress({{ $module->id }})">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
              <span>Praktik</span>
            </a>
          </div>
        </div>
      @empty
        <p style="color: #94a3b8;">Belum ada modul yang tersedia di database.</p>
      @endforelse
    </div>
  </main>

  <!-- Modal Detail Materi -->
  <div id="materi-modal-container"></div>

  <!-- Footer -->
  <footer class="home-footer">
    <div class="footer-brand">Virtual Laboratory Pengukuran Listrik</div>
    <div>Modul Pembelajaran Dasar Teknik Elektro — Universitas Negeri Padang</div>
  </footer>

  <script>
    const MATERI_DATA = {
      1: {
        title: "Tegangan Listrik (Voltage)",
        content: `
          <p><strong>Tegangan listrik</strong> (beda potensial) adalah perbedaan energi potensial listrik antara dua titik dalam rangkaian listrik per satuan muatan. Satuannya dinyatakan dalam <strong>Volt (V)</strong>.</p>
          <h4>Karakteristik Tegangan DC:</h4>
          <ul>
            <li>Mengalir dari kutub potensial tinggi (+) menuju kutub potensial rendah (-).</li>
            <li>Nilai tegangan baterai tetap konstan (misal: 12V, 24V).</li>
            <li>Diukur menggunakan <strong>Voltmeter secara PARALEL</strong> terhadap beban atau sumber tegangan.</li>
          </ul>
        `
      },
      2: {
        title: "Hambatan Listrik & Hukum Ohm",
        content: `
          <p><strong>Hambatan listrik (Resistance)</strong> adalah kemampuan suatu komponen/bahan dalam menghambat laju aliran arus listrik. Satuannya adalah <strong>Ohm (Ω)</strong>.</p>
          <h4>Hukum Ohm:</h4>
          <p style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 1.1rem; text-align: center; color: var(--color-primary-light); border: 1px solid var(--color-border);">
            V = I × R &nbsp;|&nbsp; I = V / R &nbsp;|&nbsp; R = V / I
          </p>
          <ul>
            <li><strong>V</strong> = Tegangan (Volt)</li>
            <li><strong>I</strong> = Kuat Arus (Ampere)</li>
            <li><strong>R</strong> = Hambatan (Ohm)</li>
          </ul>
        `
      },
      3: {
        title: "Panduan Penggunaan Multimeter",
        content: `
          <p>Multimeter adalah alat ukur serbaguna untuk mengukur tegangan, arus, dan hambatan.</p>
          <h4>Aturan Pemasangan:</h4>
          <ul>
            <li><strong>Mode Voltmeter (V DC):</strong> Hubungkan probe merah (+) dan hitam (-) <em>secara paralel</em> pada komponen yang ingin diukur tegangannya.</li>
            <li><strong>Mode Amperemeter (A DC):</strong> Putus salah satu jalur kawat dan sambungkan probe <em>secara seri</em> agar arus mengalir melewati instrumen.</li>
            <li><strong>Mode Ohmmeter (Ω):</strong> Ukur komponen dalam kondisi <em>sumber daya MATI (OFF)</em> agar tidak merusak alat ukur.</li>
          </ul>
        `
      }
    };

    function updateModuleCardDOM(moduleId, status) {
      const card = document.getElementById(`card-modul-${moduleId}`);
      if (!card) return;

      const badge = card.querySelector(".status-badge");
      const progressFill = card.querySelector(".materi-progress-bar-fill");

      let badgeText = "○ Belum Dimulai";
      let badgeCss = "font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid #475569;";
      let percent = 0;

      if (status === "selesai") {
        badgeText = "✓ Selesai";
        badgeCss = "font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: rgba(16, 185, 129, 0.18); color: #10b981; border: 1px solid #10b981;";
        percent = 100;
      } else if (status === "sedang_berjalan") {
        badgeText = "⚡ Sedang Berjalan";
        badgeCss = "font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid #f59e0b;";
        percent = 50;
      }

      if (badge) {
        badge.textContent = badgeText;
        badge.style.cssText = badgeCss;
      }

      if (progressFill) {
        progressFill.style.width = `${percent}%`;
      }
    }

    async function updateModuleProgress(moduleId, status) {
      // Update DOM langsung untuk responsivitas instan
      updateModuleCardDOM(moduleId, status);

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      try {
        const response = await fetch(`/api/progress/${moduleId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ status: status })
        });
        const result = await response.json();
        if (result.success && result.data) {
          updateModuleCardDOM(moduleId, result.data.status || status);
        }
      } catch (err) {
        console.error("Gagal update progress:", err);
      }
    }

    function startModuleLearn(dbId, moduleNum) {
      updateModuleProgress(dbId, 'sedang_berjalan');
      openModuleModal(dbId, moduleNum);
    }

    function markModuleInProgress(dbId) {
      updateModuleProgress(dbId, 'sedang_berjalan');
    }

    function markModuleCompleted(dbId) {
      updateModuleProgress(dbId, 'selesai');
      closeModuleModal();
    }

    function openModuleModal(dbId, moduleNum) {
      const data = MATERI_DATA[moduleNum] || {
        title: "Detail Modul",
        content: "<p>Silakan pelajari teori modul ini dan lanjutkan ke praktikum di simulator.</p>"
      };

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="materi-modal-backdrop" onclick="closeModuleModal()">
          <div class="materi-modal-content" onclick="event.stopPropagation()">
            <div class="materi-modal-header">
              <h3 style="color: #f8fafc; font-size: 1.15rem; font-weight: 700;">Modul 0${moduleNum}: ${data.title}</h3>
              <button onclick="closeModuleModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; padding: 4px;" aria-label="Tutup">✕</button>
            </div>
            <div class="materi-modal-body">
              ${data.content}
            </div>
            <div class="materi-modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
              <button class="btn-learn" onclick="markModuleCompleted(${dbId})" style="background: #10b981; color: white;">✓ Tandai Selesai</button>
              <div style="display: flex; gap: 8px;">
                <button class="btn-practice" onclick="closeModuleModal()">Tutup</button>
                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-learn" style="text-decoration: none;">Buka Simulasi 🚀</a>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function closeModuleModal() {
      document.getElementById("materi-modal-container").innerHTML = "";
    }
  </script>
</body>
</html>
