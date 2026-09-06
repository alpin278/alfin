<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Materi Pembelajaran — Fluxus</title>
  
  <!-- Google Fonts: Space Grotesk, Inter, & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}">
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
              @if($module->module_number == 1 || $module->module_number == 2 || $module->module_number == 3 || $module->module_number == 4)
                <span style="font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); display: inline-flex; align-items: center; gap: 4px;">
                  ⚡ Interaktif
                </span>
              @endif
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

          <h3 class="materi-title" title="{{ $module->title }}">{{ $module->title }}</h3>
          <p class="materi-desc" title="{{ $module->description }}">{{ $module->description }}</p>

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
          <p style="background: var(--color-bg-surface-secondary, #f1f5fb); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 1.1rem; text-align: center; color: var(--color-primary, #2563eb); border: 1px solid var(--color-border, #dce5f0);">
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
      },
      4: {
        title: "Rangkaian Seri & Paralel",
        content: `
          <p><strong>Rangkaian Listrik</strong> dapat dikelompokkan menjadi rangkaian <strong>Seri</strong>, <strong>Paralel</strong>, dan <strong>Campuran</strong> berdasarkan susunan percabangan jalurnya.</p>
          <h4>Karakteristik Utama:</h4>
          <ul>
            <li><strong>Rangkaian Seri:</strong> Hanya memiliki 1 jalur kawat, kuat arus sama di semua beban (I<sub>tot</sub> = I<sub>1</sub> = I<sub>2</sub>), tegangan terbagi (V<sub>s</sub> = V<sub>1</sub> + V<sub>2</sub>), hambatan bertambah (R<sub>tot</sub> = R<sub>1</sub> + R<sub>2</sub>).</li>
            <li><strong>Rangkaian Paralel:</strong> Memiliki percabangan, tegangan sama di semua cabang (V<sub>s</sub> = V<sub>1</sub> = V<sub>2</sub>), arus terbagi (I<sub>tot</sub> = I<sub>1</sub> + I<sub>2</sub>), hambatan ekivalen berkurang (1/R<sub>tot</sub> = 1/R<sub>1</sub> + 1/R<sub>2</sub>).</li>
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
      if (moduleNum === 1) {
        openVoltageModule(dbId, moduleNum);
      } else if (moduleNum === 2) {
        openInteractiveModule(dbId, moduleNum);
      } else if (moduleNum === 3) {
        openMultimeterModule(dbId, moduleNum);
      } else if (moduleNum === 4) {
        openSeriesParallelModule(dbId, moduleNum);
      } else {
        openStandardModuleModal(dbId, moduleNum);
      }
    }

    function markModuleInProgress(dbId) {
      updateModuleProgress(dbId, 'sedang_berjalan');
    }

    function markModuleCompleted(dbId) {
      updateModuleProgress(dbId, 'selesai');
      closeModuleModal();
    }

    function openStandardModuleModal(dbId, moduleNum) {
      const data = MATERI_DATA[moduleNum] || {
        title: "Detail Modul",
        content: "<p>Silakan pelajari teori modul ini dan lanjutkan ke praktikum di simulator.</p>"
      };

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="materi-modal-backdrop" onclick="closeModuleModal()">
          <div class="materi-modal-content" onclick="event.stopPropagation()">
            <div class="materi-modal-header">
              <h3 style="color: var(--color-text-primary, #0f172a); font-size: 1.15rem; font-weight: 700;">Modul 0${moduleNum}: ${data.title}</h3>
              <button onclick="closeModuleModal()" style="background: none; border: none; color: var(--color-text-muted, #64748b); font-size: 1.3rem; cursor: pointer; padding: 4px;" aria-label="Tutup">✕</button>
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

    
    /* ==========================================================================
       Interactive Learning Module Engine (Modul 01: Tegangan Listrik / Voltage)
       ========================================================================== */

    let currentVoltStep = 1;
    let completedVoltSteps = new Set([1]);
    let currentDbVoltModuleId = 1;

    const voltState = {
      exploredConcepts: { v: false, dv: false, pol: false },
      va: 12,
      vb: 0,
      probesReversed: false,
      polarityPredictionAnswer: null,
      polarityPredictionSubmitted: false,
      sourceVoltage: 12,
      batteryReversed: false,
      sourcePredictionAnswer: null,
      sourcePredictionSubmitted: false,
      practiceAttempts: { 1: false, 2: false, 3: false, 4: false },
      quizAnswers: {},
      quizSubmitted: false,
      isUnlocked: false
    };

    const VOLT_QUIZ_QUESTIONS = [
      {
        q: "Satuan standar internasional (SI) untuk mengukur beda potensial atau tegangan listrik adalah...",
        options: ["Ampere (A)", "Volt (V)", "Ohm (Ω)"],
        correct: 1,
        explanation: "Satuan tegangan listrik adalah Volt (V), yang didefinisikan sebagai 1 Joule energi per 1 Coulomb muatan (1 V = 1 J/C)."
      },
      {
        q: "Beda potensial listrik antara dua titik (A dan B) dirumuskan secara matematis sebagai...",
        options: ["VAB = VA - VB", "VAB = VA + VB", "VAB = VA × VB"],
        correct: 0,
        explanation: "Beda potensial adalah selisih nilai potensial pada titik pertama dikurangi titik referensi: VAB = VA - VB."
      },
      {
        q: "Jika potensial listrik titik A adalah 12 V dan titik B adalah 4 V, maka nilai tegangan VAB adalah...",
        options: ["16 V", "8 V", "-8 V"],
        correct: 1,
        explanation: "VAB = VA - VB = 12 V - 4 V = +8 V."
      },
      {
        q: "Jika potensial listrik titik A adalah 4 V dan titik B adalah 12 V, maka nilai tegangan VAB adalah...",
        options: ["-8 V", "8 V", "16 V"],
        correct: 0,
        explanation: "VAB = VA - VB = 4 V - 12 V = -8 V. Tanda negatif menunjukkan titik A berpotensial lebih rendah daripada titik B."
      },
      {
        q: "Pada Voltmeter digital DC, nilai tegangan yang ditampilkan di layar merupakan perhitungan...",
        options: ["Vdisplay = Vmerah - Vhitam", "Vdisplay = Vmerah + Vhitam", "Selalu nilai mutlak tanpa tanda"],
        correct: 0,
        explanation: "Voltmeter DC selalu menghitung beda potensial probe merah dikurangi probe hitam (acuan/COM)."
      },
      {
        q: "Jika posisi probe merah dan probe hitam pada Voltmeter DC ditukar pada dua titik uji yang sama, maka...",
        options: [
          "Nilai besaran tetap sama, namun tanda polaritas berbalik (+ menjadi -)",
          "Nilai tegangan selalu menjadi 0 V",
          "Resistansi komponen otomatis berlipat ganda"
        ],
        correct: 0,
        explanation: "Besar magnitudo tegangan tetap sama karena beda potensial antara kedua titik tidak berubah, namun tanda bacaan berbalik karena acuan probe tertukar."
      }
    ];

    function openVoltageModule(dbId, moduleNum = 1) {
      currentDbVoltModuleId = dbId;
      currentVoltStep = 1;
      completedVoltSteps = new Set([1]);

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="sp-fullscreen-backdrop" onclick="closeModuleModal()">
          <div class="sp-fullscreen-container" onclick="event.stopPropagation()">
            
            <!-- 1. Full-Screen Module Header -->
            <header class="sp-fullscreen-header">
              <div class="sp-header-left">
                <span class="interactive-module-badge">⚡ MODUL 01 • DASAR TEKNIK ELEKTRO</span>
                <h2 class="sp-header-title">Tegangan Listrik (Voltage)</h2>
              </div>

              <div class="sp-header-center">
                <div class="sp-progress-wrapper">
                  <div class="sp-progress-bar">
                    <div class="sp-progress-fill" id="volt-progress-fill" style="width: 16.7%;"></div>
                  </div>
                  <span class="sp-progress-text" id="volt-progress-text">Langkah 1 dari 6 (17%)</span>
                </div>
              </div>

              <div class="sp-header-right">
                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" title="Buka Rangkaian di Simulator">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                  <span>Coba di Simulator</span>
                </a>
                <button class="btn-close-modal" onclick="closeModuleModal()" aria-label="Tutup Modul">✕</button>
              </div>
            </header>

            <!-- 2. Full-Screen Step Navigation Tabs Bar -->
            <nav class="sp-tabs-bar" role="tablist">
              <button class="sp-tab-item active" id="volt-tab-btn-1" onclick="goToVoltStep(1)">
                <span class="tab-badge">1</span>
                <span>Kenali Tegangan</span>
              </button>
              <button class="sp-tab-item" id="volt-tab-btn-2" onclick="goToVoltStep(2)">
                <span class="tab-badge">2</span>
                <span>Eksplorasi Beda Potensial</span>
              </button>
              <button class="sp-tab-item" id="volt-tab-btn-3" onclick="goToVoltStep(3)">
                <span class="tab-badge">3</span>
                <span>Polaritas & Pembacaan</span>
              </button>
              <button class="sp-tab-item" id="volt-tab-btn-4" onclick="goToVoltStep(4)">
                <span class="tab-badge">4</span>
                <span>Eksperimen Tegangan</span>
              </button>
              <button class="sp-tab-item" id="volt-tab-btn-5" onclick="goToVoltStep(5)">
                <span class="tab-badge">5</span>
                <span>Latihan Pengukuran</span>
              </button>
              <button class="sp-tab-item" id="volt-tab-btn-6" onclick="goToVoltStep(6)">
                <span class="tab-badge">6</span>
                <span>Quiz & Simulator</span>
              </button>
            </nav>

            <!-- 3. Full-Screen Scrollable Body -->
            <main class="sp-fullscreen-body" id="volt-modal-body">
              
              <!-- ================================================================
                   LANGKAH 1: KENALI TEGANGAN
                   ================================================================ -->
              <div class="step-content-panel active" id="volt-panel-1">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 1 DARI 6 • KENALI TEGANGAN</span>
                  <h3 class="step-title">Gaya Gerak Listrik & Perbedaan Potensial</h3>
                  <p class="step-desc">
                    Tegangan listrik adalah besaran penggerak muatan dalam rangkaian elektronika. Sebelum melakukan pengukuran, pelajari 3 pilar utama konsep tegangan berikut. <strong>Klik ketiga kartu di bawah untuk membuka penjelasannya:</strong>
                  </p>
                </div>

                <div class="concept-cards-grid">
                  <!-- Card Tegangan V -->
                  <div class="interactive-concept-card" id="volt-card-v" onclick="exploreVoltConcept('v')">
                    <div class="card-symbol-badge">V</div>
                    <div class="concept-card-title">TEGANGAN (VOLTAGE)</div>
                    <div class="concept-card-unit">Simbol: V • Satuan: Volt (V)</div>
                    <p class="concept-card-desc">Beda potensial listrik antara dua titik dalam suatu rangkaian tertutup.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Definisi Ilmiah:</strong> Tegangan adalah ukuran energi potensial per satuan muatan (1 Volt = 1 Joule / 1 Coulomb) yang mendorong elektron bebas mengalir melalui konduktor.
                    </div>
                  </div>

                  <!-- Card Beda Potensial ΔV -->
                  <div class="interactive-concept-card" id="volt-card-dv" onclick="exploreVoltConcept('dv')">
                    <div class="card-symbol-badge">ΔV</div>
                    <div class="concept-card-title">BEDA POTENSIAL</div>
                    <div class="concept-card-unit">Rumus: V_AB = V_A - V_B</div>
                    <p class="concept-card-desc">Tegangan SELALU diukur ANTARA dua titik, bukan satu titik mutlak.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Titik Referensi:</strong> Nilai tegangan tidak bermakna tanpa titik acuan. Jika Titik A = 12V dan Titik B = 4V, maka beda potensial VAB = 12 - 4 = +8V. Jika potensialnya sama, VAB = 0V.
                    </div>
                  </div>

                  <!-- Card Polaritas + / - -->
                  <div class="interactive-concept-card" id="volt-card-pol" onclick="exploreVoltConcept('pol')">
                    <div class="card-symbol-badge">+ / −</div>
                    <div class="concept-card-title">POLARITAS (POLARITY)</div>
                    <div class="concept-card-unit">Kutub Positif (+) & Negatif (−)</div>
                    <p class="concept-card-desc">Menentukan arah potensial listrik pada sumber DC atau beban rangkaian.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Arah Aliran DC:</strong> Kutub (+) mewakili titik dengan potensial lebih tinggi, sedangkan kutub (−) merupakan titik acuan berpotensial lebih rendah (biasanya ground / 0V).
                    </div>
                  </div>
                </div>

                <!-- Status Banner -->
                <div id="volt-concept-status" style="margin-top: 14px; padding: 12px 18px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; display: none; background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #059669;">
                  ✓ Ketiga konsep tegangan telah dipelajari! Silakan lanjutkan ke Langkah 2 (Eksplorasi Beda Potensial).
                </div>

                <!-- Collapsible Theory Explanation -->
                <div class="collapsible-box" id="collapsible-volt-step1">
                  <button class="collapsible-header" onclick="toggleCollapsible('collapsible-volt-step1')">
                    <span>💡 Mengapa burung yang bertengger di satu kabel listrik tegangan tinggi tidak tersetrum?</span>
                    <span class="collapsible-icon">▼</span>
                  </button>
                  <div class="collapsible-body">
                    Burung tidak tersetrum karena kedua kakinya berpijak pada kabel yang sama dengan potensial listrik yang identik (VA = VB). Karena tidak ada beda potensial antara kedua kakinya (VAB = VA - VB = 0 Volt), maka tidak ada arus listrik yang terdorong mengalir melewati tubuh burung. Burung baru akan tersetrum jika tubuhnya secara bersamaan menyentuh kabel fasa lain atau menyentuh tiang tanah (ground) yang memiliki perbedaan potensial besar.
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 2: EKSPLORASI BEDA POTENSIAL
                   ================================================================ -->
              <div class="step-content-panel" id="volt-panel-2">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 2 DARI 6 • EKSPLORASI BEDA POTENSIAL</span>
                  <h3 class="step-title">Simulasi Interaktif Rumus V_AB = V_A - V_B</h3>
                  <p class="step-desc">
                    Atur nilai potensial Titik A (VA) dan Titik B (VB) menggunakan slider atau preset. Amati bagaimana rumus beda potensial terhitung secara real-time dan perhatikan bahwa tegangan dapat bernilai positif, negatif, atau nol.
                  </p>
                </div>

                <div class="formula-hero-display">
                  V_AB = V_A − V_B &nbsp;&nbsp;|&nbsp;&nbsp; Tegangan = Selisih Dua Titik Potensial
                </div>

                <div class="formula-explorer-container">
                  <div class="calc-grid">
                    <!-- Left: Sliders & Controls Panel -->
                    <div class="calc-sliders-panel">
                      <!-- Point A Control -->
                      <div class="slider-control-card">
                        <div class="slider-header">
                          <span class="slider-label">Potensial Titik A (V_A):</span>
                          <div class="slider-header-controls">
                            <input type="number" class="calc-number-input" id="num-va" min="0" max="24" step="1" value="12" oninput="handleVoltNumber('va', this.value)">
                            <span class="practice-unit-badge">Volt</span>
                          </div>
                        </div>
                        <input type="range" class="slider-input-range" id="slider-va" min="0" max="24" step="1" value="12" oninput="handleVoltSlider('va', this.value)">
                        <div class="source-preset-group">
                          <span style="font-size: 0.75rem; color: #64748b; margin-right: 4px;">Preset VA:</span>
                          <button class="btn-preset-volt" onclick="setVoltPreset('va', 0)">0V</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('va', 6)">6V</button>
                          <button class="btn-preset-volt active" id="preset-va-12" onclick="setVoltPreset('va', 12)">12V</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('va', 18)">18V</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('va', 24)">24V</button>
                        </div>
                      </div>

                      <!-- Point B Control -->
                      <div class="slider-control-card">
                        <div class="slider-header">
                          <span class="slider-label">Potensial Titik B (V_B):</span>
                          <div class="slider-header-controls">
                            <input type="number" class="calc-number-input" id="num-vb" min="0" max="24" step="1" value="0" oninput="handleVoltNumber('vb', this.value)">
                            <span class="practice-unit-badge">Volt</span>
                          </div>
                        </div>
                        <input type="range" class="slider-input-range" id="slider-vb" min="0" max="24" step="1" value="0" oninput="handleVoltSlider('vb', this.value)">
                        <div class="source-preset-group">
                          <span style="font-size: 0.75rem; color: #64748b; margin-right: 4px;">Preset VB:</span>
                          <button class="btn-preset-volt active" id="preset-vb-0" onclick="setVoltPreset('vb', 0)">0V (GND)</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('vb', 4)">4V</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('vb', 10)">10V</button>
                          <button class="btn-preset-volt" onclick="setVoltPreset('vb', 12)">12V</button>
                        </div>
                      </div>

                      <button class="btn-probe-swap" onclick="swapVoltPotentials()" style="width: 100%;">
                        <span>⇄ Tukar Nilai Potensial (VA ↔ VB)</span>
                      </button>
                    </div>

                    <!-- Right: Live Output Panel -->
                    <div class="calc-output-panel">
                      <div class="output-heading">Substitusi Rumus & Beda Potensial (V_AB):</div>
                      <div class="calc-math-equation" id="volt-equation-display">
                        V_AB = V_A − V_B<br>
                        V_AB = 12 V − 0 V<br>
                        V_AB = +12.0 V
                      </div>

                      <div class="calc-big-result">
                        <span class="big-result-num" id="volt-result-display" style="color: #2563eb;">+12.0</span>
                        <span class="big-result-unit">Volt (V)</span>
                      </div>

                      <!-- Visual Potential Levels -->
                      <div class="potential-visual-container">
                        <div class="potential-bar-row">
                          <span class="potential-bar-label">Titik A (VA):</span>
                          <div class="potential-bar-track">
                            <div class="potential-bar-fill fill-va" id="bar-va" style="width: 50%;">12V</div>
                          </div>
                        </div>
                        <div class="potential-bar-row">
                          <span class="potential-bar-label">Titik B (VB):</span>
                          <div class="potential-bar-track">
                            <div class="potential-bar-fill fill-vb" id="bar-vb" style="width: 0%;">0V</div>
                          </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                          <span style="font-size: 0.78rem; color: #64748b;">Selisih Energi:</span>
                          <span class="potential-diff-badge" id="badge-diff-val">ΔV = +12.0 V</span>
                        </div>
                      </div>

                      <!-- Conceptual Status Feedback -->
                      <div class="relation-feedback-badge" id="volt-feedback-badge">
                        <span>⚡ Titik A berpotensial lebih tinggi (+12.0V). Arus konvensional mengalir dari A ke B.</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 3: POLARITAS & PEMBACAAN VOLTMETER
                   ================================================================ -->
              <div class="step-content-panel" id="volt-panel-3">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 3 DARI 6 • POLARITAS & PEMBACAAN VOLTMETER</span>
                  <h3 class="step-title">Cara Kerja Alat Ukur Voltmeter Digital DC</h3>
                  <p class="step-desc">
                    Voltmeter digital selalu menghitung: <strong>V_display = V_merah − V_hitam</strong>. Probe merah bertindak sebagai probe pengukur, sedangkan probe hitam adalah probe acuan (COM).
                  </p>
                </div>

                <div class="voltmeter-sim-box">
                  <!-- LCD Display -->
                  <div class="voltmeter-lcd-display">
                    <div>
                      <div style="font-size: 0.75rem; color: #64748b; letter-spacing: 0.1em;">DIGITAL VOLTMETER DC</div>
                      <div class="voltmeter-lcd-text" id="meter-lcd-val">+12.0 V</div>
                    </div>
                    <div class="voltmeter-lcd-mode">
                      <span style="color: #38bdf8; font-weight: 700;">DC VOLTS</span>
                      <span style="font-size: 0.72rem;">AUTO-RANGE</span>
                    </div>
                  </div>

                  <!-- Probe Connections Status -->
                  <div class="probe-connection-grid">
                    <div class="probe-status-card probe-red">
                      <div class="probe-bullet red"></div>
                      <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Probe Merah (+) Input</div>
                        <div style="font-size: 0.95rem; font-weight: 800;" id="probe-red-target">Terhubung ke Titik A (12 V)</div>
                      </div>
                    </div>
                    <div class="probe-status-card probe-black">
                      <div class="probe-bullet black"></div>
                      <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Probe Hitam (−) COM / Ground</div>
                        <div style="font-size: 0.95rem; font-weight: 800;" id="probe-black-target">Terhubung ke Titik B (0 V)</div>
                      </div>
                    </div>
                  </div>

                  <div style="display: flex; justify-content: center; margin-bottom: 16px;">
                    <button class="btn-probe-swap" onclick="toggleVoltProbeSwap()">
                      <span>⇄ Balik Posisi Probe (Merah ↔ Hitam)</span>
                    </button>
                  </div>

                  <div class="prediction-scenario" id="meter-explanation-text">
                    📌 <strong>Analisis Pengukuran:</strong> Probe merah mengukur 12V dan probe hitam mengukur 0V. Layar menampilkan: <strong>Vdisplay = 12V − 0V = +12.0 V</strong>.
                  </div>
                </div>

                <!-- Polarity Prediction Challenge -->
                <div class="prediction-card">
                  <span class="qc-badge">TANTANGAN PREDIKSI POLARITAS</span>
                  <div class="prediction-scenario">
                    Kondisi: Titik A berpotensial 12 V dan Titik B berpotensial 0 V.<br>
                    Jika <strong>Probe Merah</strong> dicolokkan ke Titik B (0 V) dan <strong>Probe Hitam</strong> dicolokkan ke Titik A (12 V)...
                  </div>
                  <div class="qc-question">
                    Berapakah nilai yang akan ditampilkan pada layar Voltmeter digital?
                  </div>
                  <div class="qc-options-list">
                    <button class="qc-option-btn" onclick="checkPolarityPrediction('A', this)">A. +12.0 V</button>
                    <button class="qc-option-btn" onclick="checkPolarityPrediction('B', this)">B. −12.0 V</button>
                    <button class="qc-option-btn" onclick="checkPolarityPrediction('C', this)">C. 0.0 V</button>
                  </div>
                  <div class="qc-feedback-panel" id="polarity-pred-feedback"></div>
                  <div class="prediction-math-reveal" id="polarity-pred-reveal">
                    <strong>Penjelasan Matematis:</strong><br>
                    Voltmeter selalu menghitung: <strong>Vdisplay = Vmerah − Vhitam</strong>.<br>
                    Dengan probe merah di 0V dan probe hitam di 12V: <strong>Vdisplay = 0 − 12 = −12.0 V</strong>.<br>
                    <em>Tanda minus (-) menegaskan bahwa titik yang disentuh probe merah memiliki potensial lebih rendah daripada titik referensi probe hitam.</em>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 4: EKSPERIMEN TEGANGAN SUMBER
                   ================================================================ -->
              <div class="step-content-panel" id="volt-panel-4">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 4 DARI 6 • EKSPERIMEN TEGANGAN SUMBER</span>
                  <h3 class="step-title">Pengaruh Nilai dan Arah Sumber Daya DC pada Beban</h3>
                  <p class="step-desc">
                    Sumber tegangan DC (baterai atau power supply) memegang peranan menentukan beda potensial yang disuplai ke beban resistor. Amati bagaimana perubahan tegangan sumber mengubah beda potensial beban.
                  </p>
                </div>

                <div class="source-experiment-box">
                  <div class="calc-grid">
                    <!-- Left: Source Controls -->
                    <div>
                      <div class="slider-control-card">
                        <div class="slider-header">
                          <span class="slider-label">Tegangan Sumber Baterai (Vs):</span>
                          <div class="slider-header-controls">
                            <input type="number" class="calc-number-input" id="num-vs" min="0" max="24" step="1" value="12" oninput="handleSourceVoltageSlider(this.value)">
                            <span class="practice-unit-badge">Volt</span>
                          </div>
                        </div>
                        <input type="range" class="slider-input-range" id="slider-vs" min="0" max="24" step="1" value="12" oninput="handleSourceVoltageSlider(this.value)">
                        <div class="source-preset-group">
                          <span style="font-size: 0.75rem; color: #64748b; margin-right: 4px;">Preset Vs:</span>
                          <button class="btn-preset-volt" onclick="setSourceVoltagePreset(6)">6V</button>
                          <button class="btn-preset-volt active" id="preset-vs-12" onclick="setSourceVoltagePreset(12)">12V</button>
                          <button class="btn-preset-volt" onclick="setSourceVoltagePreset(18)">18V</button>
                          <button class="btn-preset-volt" onclick="setSourceVoltagePreset(24)">24V</button>
                        </div>
                      </div>

                      <div style="margin-top: 14px;">
                        <button class="btn-probe-swap" onclick="toggleBatteryPolarity()" style="width: 100%;">
                          <span id="btn-battery-polarity-label">⇄ Balik Polaritas Baterai (Saat ini: Normal +/−)</span>
                        </button>
                      </div>
                    </div>

                    <!-- Right: Circuit Diagram & Measurements -->
                    <div>
                      <!-- Schematic SVG representation -->
                      <div style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 14px; text-align: center;">
                        <svg viewBox="0 0 340 100" style="width: 100%; height: 90px; display: block;">
                          <!-- Battery -->
                          <rect x="20" y="30" width="36" height="40" rx="4" fill="#ffffff" stroke="#2563eb" stroke-width="2"/>
                          <text x="38" y="55" fill="#2563eb" font-size="12" font-weight="bold" text-anchor="middle" id="svg-bat-label">12V</text>
                          <text x="38" y="24" fill="#ef4444" font-size="11" font-weight="bold" text-anchor="middle" id="svg-bat-plus">+</text>
                          <text x="38" y="82" fill="#0f172a" font-size="11" font-weight="bold" text-anchor="middle" id="svg-bat-minus">−</text>
                          <!-- Wires -->
                          <path d="M56 40 L180 40" stroke="#2563eb" stroke-width="2.5"/>
                          <path d="M56 60 L180 60" stroke="#64748b" stroke-width="2.5"/>
                          <!-- Resistor -->
                          <rect x="180" y="32" width="70" height="36" rx="4" fill="#ffffff" stroke="#f59e0b" stroke-width="2"/>
                          <text x="215" y="54" fill="#0f172a" font-size="11" font-weight="bold" text-anchor="middle">R = 600 Ω</text>
                          <!-- Voltmeter reading badge -->
                          <rect x="270" y="38" width="55" height="24" rx="4" fill="#0f172a"/>
                          <text x="297" y="54" fill="#38bdf8" font-size="10" font-weight="bold" text-anchor="middle" id="svg-vload-val">+12.0V</text>
                        </svg>
                      </div>

                      <div style="margin-top: 14px; background: #ffffff; border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Tegangan Beban Resistor:</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #2563eb;" id="source-load-val">+12.0 Volt</div>
                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 6px;" id="source-ohm-relation">
                          🔗 <strong>Hubungan ke Modul Hukum Ohm (I = V / R):</strong><br>
                          Arus mengalir = 12 V / 600 Ω = <strong style="color: #059669;">20.0 mA</strong>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 4 Prediction -->
                <div class="prediction-card">
                  <span class="qc-badge">PREDIKSI TEGANGAN BEBAN</span>
                  <div class="prediction-scenario">
                    Sebuah rangkaian sederhana memiliki sumber DC ideal dan satu resistor 600 Ω.<br>
                    Jika tegangan sumber dinaikkan dari <strong>6 V</strong> menjadi <strong>12 V</strong> sementara resistor tetap 600 Ω...
                  </div>
                  <div class="qc-question">
                    Apa yang terjadi pada nilai beda potensial yang melintasi beban resistor ideal?
                  </div>
                  <div class="qc-options-list">
                    <button class="qc-option-btn" onclick="checkSourcePrediction('A', this)">A. Bertambah (Menjadi 12 V)</button>
                    <button class="qc-option-btn" onclick="checkSourcePrediction('B', this)">B. Berkurang (Menjadi 3 V)</button>
                    <button class="qc-option-btn" onclick="checkSourcePrediction('C', this)">C. Tetap (6 V)</button>
                  </div>
                  <div class="qc-feedback-panel" id="source-pred-feedback"></div>
                  <div class="prediction-math-reveal" id="source-pred-reveal">
                    <strong>Kalkulasi Pembuktian:</strong><br>
                    Pada rangkaian loop tunggal ideal tanpa hambatan dalam, beda potensial pada resistor sama persis dengan tegangan yang dipasok oleh sumber: <strong>Vbeban = Vs</strong>.<br>
                    Ketika sumber dinaikkan menjadi 12 V, tegangan pada beban langsung ikut bertambah menjadi <strong>12 V</strong>.
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 5: LATIHAN PENGUKURAN TEGANGAN
                   ================================================================ -->
              <div class="step-content-panel" id="volt-panel-5">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 5 DARI 6 • LATIHAN PENGUKURAN</span>
                  <h3 class="step-title">Latihan Mandiri Perhitungan Beda Potensial & Polaritas</h3>
                  <p class="step-desc">
                    Hitung nilai beda potensial dan pembacaan Voltmeter berikut. Perhatikan tanda positif (+) dan tanda negatif (−). Kamu dapat membuka pembahasan langkah demi langkah kapan saja.
                  </p>
                </div>

                <!-- Exercise 1 -->
                <div class="practice-exercise-card" id="volt-prac-card-1">
                  <span class="example-header-tag">LATIHAN 1: BEDA POTENSIAL STANDAR (VA > VB)</span>
                  <div class="example-question">
                    Titik A memiliki potensial listrik <strong>12 Volt</strong> dan Titik B memiliki potensial <strong>5 Volt</strong>. Berapakah beda potensial <strong>V_AB</strong>? <em>(Satuan: Volt)</em>
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="volt-prac-input-1" placeholder="Contoh: 7">
                    <span class="practice-unit-badge">Volt (V)</span>
                    <button class="btn-check-practice" onclick="checkVoltPractice(1)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleVoltSolution('volt-prac-sol-1', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="volt-prac-feedback-1"></div>
                  <div class="solution-steps-container" id="volt-prac-sol-1">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> VA = 12 V, VB = 5 V<br>
                      <strong>Rumus:</strong> VAB = VA − VB<br>
                      <strong>Substitusi:</strong> VAB = 12 − 5 = <strong>7 Volt</strong> (atau +7 V)
                    </div>
                  </div>
                </div>

                <!-- Exercise 2: Negative result -->
                <div class="practice-exercise-card" id="volt-prac-card-2">
                  <span class="example-header-tag">LATIHAN 2: BEDA POTENSIAL BERTANDA NEGATIF (VA < VB)</span>
                  <div class="example-question">
                    Titik A memiliki potensial <strong>3 Volt</strong> dan Titik B memiliki potensial <strong>9 Volt</strong>. Berapakah nilai beda potensial <strong>V_AB</strong>? <em>(Sertakan tanda minus jika bernilai negatif)</em>
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="volt-prac-input-2" placeholder="Contoh: -6">
                    <span class="practice-unit-badge">Volt (V)</span>
                    <button class="btn-check-practice" onclick="checkVoltPractice(2)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleVoltSolution('volt-prac-sol-2', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="volt-prac-feedback-2"></div>
                  <div class="solution-steps-container" id="volt-prac-sol-2">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> VA = 3 V, VB = 9 V<br>
                      <strong>Rumus:</strong> VAB = VA − VB<br>
                      <strong>Substitusi:</strong> VAB = 3 − 9 = <strong>-6 Volt</strong><br>
                      <em>Tanda minus menunjukkan bahwa Titik A lebih rendah 6V dibandingkan Titik B.</em>
                    </div>
                  </div>
                </div>

                <!-- Exercise 3: Normal Probes -->
                <div class="practice-exercise-card" id="volt-prac-card-3">
                  <span class="example-header-tag">LATIHAN 3: PEMBACAAN VOLTMETER PROBE NORMAL</span>
                  <div class="example-question">
                    Probe Merah Voltmeter DC disentuhkan ke titik berpotensial <strong>8 Volt</strong> dan Probe Hitam disentuhkan ke titik berpotensial <strong>2 Volt</strong>. Berapakah nilai tegangan yang terbaca pada display multimeter?
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="volt-prac-input-3" placeholder="Contoh: 6">
                    <span class="practice-unit-badge">Volt (V)</span>
                    <button class="btn-check-practice" onclick="checkVoltPractice(3)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleVoltSolution('volt-prac-sol-3', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="volt-prac-feedback-3"></div>
                  <div class="solution-steps-container" id="volt-prac-sol-3">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> Vmerah = 8 V, Vhitam = 2 V<br>
                      <strong>Rumus:</strong> Vdisplay = Vmerah − Vhitam<br>
                      <strong>Substitusi:</strong> Vdisplay = 8 − 2 = <strong>+6 Volt</strong> (atau 6 V)
                    </div>
                  </div>
                </div>

                <!-- Exercise 4: Reversed Probes -->
                <div class="practice-exercise-card" id="volt-prac-card-4">
                  <span class="example-header-tag">LATIHAN 4: PEMBACAAN VOLTMETER PROBE TERBALIK</span>
                  <div class="example-question">
                    Pada dua titik yang sama, posisi probe ditukar: Probe Merah menyentuh titik <strong>2 Volt</strong> dan Probe Hitam menyentuh titik <strong>8 Volt</strong>. Berapakah nilai tegangan yang akan muncul di layar?
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="volt-prac-input-4" placeholder="Contoh: -6">
                    <span class="practice-unit-badge">Volt (V)</span>
                    <button class="btn-check-practice" onclick="checkVoltPractice(4)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleVoltSolution('volt-prac-sol-4', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="volt-prac-feedback-4"></div>
                  <div class="solution-steps-container" id="volt-prac-sol-4">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> Vmerah = 2 V, Vhitam = 8 V<br>
                      <strong>Rumus:</strong> Vdisplay = Vmerah − Vhitam<br>
                      <strong>Substitusi:</strong> Vdisplay = 2 − 8 = <strong>-6 Volt</strong><br>
                      <em>Voltmeter digital akan menyalakan simbol minus (−) di depan angka 6.</em>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 6: QUIZ & SIMULATOR
                   ================================================================ -->
              <div class="step-content-panel" id="volt-panel-6">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 6 DARI 6 • QUIZ & SIMULATOR</span>
                  <h3 class="step-title">Evaluasi Pemahaman & Praktikum Simulator</h3>
                  <p class="step-desc">
                    Jawab 6 pertanyaan evaluasi di bawah ini untuk menguji penguasaan materi tegangan, beda potensial, dan polaritas. Selesaikan seluruh tahap modul untuk membuka tombol penyelesaian.
                  </p>
                </div>

                <!-- 6 Quiz Questions -->
                <div class="quiz-wrapper" id="volt-quiz-wrapper">
                  ${VOLT_QUIZ_QUESTIONS.map((item, qIdx) => `
                    <div class="quiz-card" id="volt-quiz-card-${qIdx}">
                      <div class="quiz-q-header">
                        <span class="quiz-q-num">Soal ${qIdx + 1} dari ${VOLT_QUIZ_QUESTIONS.length}</span>
                      </div>
                      <div class="quiz-q-text">${item.q}</div>
                      <div class="quiz-options-group">
                        ${item.options.map((opt, optIdx) => `
                          <label class="quiz-option-label" id="lbl-volt-q-${qIdx}-opt-${optIdx}">
                            <input type="radio" name="volt_quiz_q_${qIdx}" class="quiz-option-radio" value="${optIdx}" onchange="selectVoltQuizOption(${qIdx}, ${optIdx})">
                            <span class="quiz-option-text">${opt}</span>
                          </label>
                        `).join('')}
                      </div>
                    </div>
                  `).join('')}

                  <button class="btn-submit-quiz" onclick="submitVoltQuiz()">
                    <span>Periksa Hasil Quiz</span>
                  </button>
                </div>

                <!-- Quiz Result Card -->
                <div class="quiz-result-card" id="volt-quiz-result-card">
                  <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: #10b981; letter-spacing: 0.05em;">HASIL PEMAHAMAN</span>
                  <div class="quiz-result-score" id="volt-quiz-score-display">6 / 6 benar</div>
                  <p class="quiz-result-msg" id="volt-quiz-feedback-msg"></p>
                  <button class="btn-step-nav btn-step-prev" onclick="resetVoltQuiz()" style="margin: 0 auto;">
                    <span>🔄 Ulangi Quiz</span>
                  </button>
                </div>

                <!-- Practical Challenges for Simulator -->
                <div class="challenge-card">
                  <span class="challenge-badge">🎯 TANTANGAN PRAKTIKUM SIMULATOR</span>
                  <h4 class="challenge-title">Tantangan 1: Mengukur Tegangan Baterai 12V</h4>
                  <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); line-height: 1.6; margin: 0 0 10px;">
                    1. Buka Simulator, letakkan sebuah <strong>Baterai 12V</strong>.<br>
                    2. Letakkan <strong>Multimeter</strong> dan atur selektor mode ke <strong>Voltmeter DC (V⎓)</strong>.<br>
                    3. Hubungkan <strong>Probe Merah</strong> ke kutub positif (+) dan <strong>Probe Hitam</strong> ke kutub negatif (−).<br>
                    4. Amati display menunjukkan: <span style="color: #2563eb; font-weight: 700;">+12.0 V</span>.
                  </p>

                  <h4 class="challenge-title" style="margin-top: 16px;">Tantangan 2: Mengamati Polaritas Negatif Saat Probe Dibalik</h4>
                  <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); line-height: 1.6; margin: 0 0 10px;">
                    1. Tukar kabel probe multimeter: colokkan probe merah ke kutub (−) dan probe hitam ke kutub (+).<br>
                    2. Amati display multimeter: nilai pembacaan berubah menjadi <span style="color: #ef4444; font-weight: 700;">-12.0 V</span>.<br>
                    3. Ini membuktikan secara langsung konsep <em>Vdisplay = Vmerah − Vhitam</em>.
                  </p>

                  <div style="margin-top: 18px;">
                    <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" style="padding: 10px 20px; font-size: 0.92rem;">
                      <span>🚀 Buka Simulator Sekarang</span>
                    </a>
                  </div>
                </div>

                <!-- Completion Lock Section -->
                <div class="completion-lock-box">
                  <div class="completion-lock-title">
                    <span>📋 Syarat Kelulusan Modul Interaktif:</span>
                  </div>
                  <div class="completion-checklist" id="volt-completion-checklist">
                    <div class="checklist-item" id="chk-volt-concepts"><span>○</span> 1. Kenali Tegangan, Beda Potensial & Polaritas (Langkah 1)</div>
                    <div class="checklist-item" id="chk-volt-explorer"><span>○</span> 2. Eksplorasi Beda Potensial VA & VB (Langkah 2)</div>
                    <div class="checklist-item" id="chk-volt-probe"><span>○</span> 3. Uji Balik Probe Voltmeter & Prediksi (Langkah 3)</div>
                    <div class="checklist-item" id="chk-volt-source"><span>○</span> 4. Eksperimen Tegangan Sumber (Langkah 4)</div>
                    <div class="checklist-item" id="chk-volt-practice"><span>○</span> 5. Selesaikan Latihan Pengukuran (Langkah 5)</div>
                    <div class="checklist-item" id="chk-volt-quiz"><span>○</span> 6. Selesaikan Kuis Evaluasi (Langkah 6)</div>
                  </div>

                  <button class="btn-finish-module" id="btn-finish-volt-module" onclick="finishAndSaveVoltageModule(${dbId})" disabled>
                    <span>✓ Tandai Selesai & Simpan Progress</span>
                  </button>
                  <div class="completion-lock-helper" id="volt-completion-lock-helper">
                    🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 6 untuk membuka tombol selesai.
                  </div>
                </div>
              </div>

            </main>

            <!-- 4. Full-Screen Modal Footer -->
            <footer class="sp-fullscreen-footer">
              <button class="btn-step-nav btn-step-prev" id="btn-volt-step-prev" onclick="goToVoltStep(currentVoltStep - 1)" disabled>
                <span>← Sebelumnya</span>
              </button>

              <div class="footer-step-counter" id="volt-step-counter-footer">
                Langkah 1 dari 6
              </div>

              <button class="btn-step-nav btn-step-next" id="btn-volt-step-next" onclick="goToVoltStep(currentVoltStep + 1)">
                <span>Langkah Selanjutnya →</span>
              </button>
            </footer>

          </div>
        </div>
      `;

      // Initial state sync
      updateVoltExplorer();
      updateVoltChecklist();
    }

    /* ==========================================================================
       Modul 01: Step Navigation & State
       ========================================================================== */

    function goToVoltStep(step) {
      if (step < 1 || step > 6) return;
      currentVoltStep = step;
      completedVoltSteps.add(step);

      // Update active panels
      for (let i = 1; i <= 6; i++) {
        const panel = document.getElementById(`volt-panel-${i}`);
        if (panel) panel.classList.toggle("active", i === step);
      }

      // Update tabs
      for (let i = 1; i <= 6; i++) {
        const tab = document.getElementById(`volt-tab-btn-${i}`);
        if (!tab) continue;
        tab.classList.toggle("active", i === step);
        tab.classList.toggle("completed", completedVoltSteps.has(i) && i !== step);
      }

      // Update progress bar
      const percent = Math.round((step / 6) * 100);
      const fill = document.getElementById("volt-progress-fill");
      const text = document.getElementById("volt-progress-text");
      const footerCounter = document.getElementById("volt-step-counter-footer");

      if (fill) fill.style.width = `${percent}%`;
      if (text) text.textContent = `Langkah ${step} dari 6 (${percent}%)`;
      if (footerCounter) footerCounter.textContent = `Langkah ${step} dari 6`;

      // Update footer buttons
      const prevBtn = document.getElementById("btn-volt-step-prev");
      const nextBtn = document.getElementById("btn-volt-step-next");

      if (prevBtn) prevBtn.disabled = (step === 1);
      if (nextBtn) {
        if (step === 6) {
          nextBtn.style.display = "none";
        } else {
          nextBtn.style.display = "inline-flex";
          nextBtn.innerHTML = `<span>Langkah Selanjutnya →</span>`;
          nextBtn.onclick = () => goToVoltStep(step + 1);
        }
      }

      updateVoltChecklist();

      // Scroll body to top
      const body = document.getElementById("volt-modal-body");
      if (body) body.scrollTop = 0;
    }

    /* ==========================================================================
       Modul 01: Step 1 Concept Exploration
       ========================================================================== */

    function exploreVoltConcept(key) {
      if (!voltState.exploredConcepts) return;
      voltState.exploredConcepts[key] = true;

      const card = document.getElementById(`volt-card-${key}`);
      if (card) card.classList.add("active");

      if (voltState.exploredConcepts.v && voltState.exploredConcepts.dv && voltState.exploredConcepts.pol) {
        const status = document.getElementById("volt-concept-status");
        if (status) status.style.display = "block";
      }

      updateVoltChecklist();
    }

    /* ==========================================================================
       Modul 01: Step 2 Potential Difference Explorer
       ========================================================================== */

    function handleVoltSlider(point, val) {
      val = parseFloat(val) || 0;
      if (point === 'va') {
        voltState.va = val;
        const num = document.getElementById("num-va");
        if (num) num.value = val;
      } else {
        voltState.vb = val;
        const num = document.getElementById("num-vb");
        if (num) num.value = val;
      }
      updateVoltExplorer();
    }

    function handleVoltNumber(point, val) {
      val = parseFloat(val) || 0;
      val = Math.max(0, Math.min(24, val));
      if (point === 'va') {
        voltState.va = val;
        const slider = document.getElementById("slider-va");
        if (slider) slider.value = val;
      } else {
        voltState.vb = val;
        const slider = document.getElementById("slider-vb");
        if (slider) slider.value = val;
      }
      updateVoltExplorer();
    }

    function setVoltPreset(point, val) {
      if (point === 'va') {
        voltState.va = val;
        const slider = document.getElementById("slider-va");
        const num = document.getElementById("num-va");
        if (slider) slider.value = val;
        if (num) num.value = val;
      } else {
        voltState.vb = val;
        const slider = document.getElementById("slider-vb");
        const num = document.getElementById("num-vb");
        if (slider) slider.value = val;
        if (num) num.value = val;
      }
      updateVoltExplorer();
    }

    function swapVoltPotentials() {
      const temp = voltState.va;
      voltState.va = voltState.vb;
      voltState.vb = temp;

      const sVa = document.getElementById("slider-va");
      const nVa = document.getElementById("num-va");
      const sVb = document.getElementById("slider-vb");
      const nVb = document.getElementById("num-vb");

      if (sVa) sVa.value = voltState.va;
      if (nVa) nVa.value = voltState.va;
      if (sVb) sVb.value = voltState.vb;
      if (nVb) nVb.value = voltState.vb;

      updateVoltExplorer();
    }

    function updateVoltExplorer() {
      const va = voltState.va;
      const vb = voltState.vb;
      const vab = va - vb;

      const signStr = vab > 0 ? "+" : (vab < 0 ? "−" : "");
      const absVal = Math.abs(vab).toFixed(1);
      const displayStr = `${vab > 0 ? '+' : (vab < 0 ? '-' : '')}${absVal}`;

      // Math equation display
      const eqDisplay = document.getElementById("volt-equation-display");
      if (eqDisplay) {
        eqDisplay.innerHTML = `
          V_AB = V_A − V_B<br>
          V_AB = ${va.toFixed(1)} V − ${vb.toFixed(1)} V<br>
          V_AB = <strong>${displayStr} V</strong>
        `;
      }

      // Result display
      const resDisplay = document.getElementById("volt-result-display");
      if (resDisplay) {
        resDisplay.textContent = displayStr;
        if (vab > 0) resDisplay.style.color = "#2563eb";
        else if (vab < 0) resDisplay.style.color = "#ef4444";
        else resDisplay.style.color = "#64748b";
      }

      // Bars visual
      const barVa = document.getElementById("bar-va");
      const barVb = document.getElementById("bar-vb");
      const badgeDiff = document.getElementById("badge-diff-val");

      const pctA = Math.min(100, Math.max(0, (va / 24) * 100));
      const pctB = Math.min(100, Math.max(0, (vb / 24) * 100));

      if (barVa) {
        barVa.style.width = `${Math.max(8, pctA)}%`;
        barVa.textContent = `${va}V`;
      }
      if (barVb) {
        barVb.style.width = `${Math.max(8, pctB)}%`;
        barVb.textContent = `${vb}V`;
      }
      if (badgeDiff) {
        badgeDiff.textContent = `ΔV = ${displayStr} V`;
        if (vab > 0) {
          badgeDiff.style.background = "#eaf2ff";
          badgeDiff.style.color = "#2563eb";
          badgeDiff.style.borderColor = "#93c5fd";
        } else if (vab < 0) {
          badgeDiff.style.background = "#fef2f2";
          badgeDiff.style.color = "#ef4444";
          badgeDiff.style.borderColor = "#fca5a5";
        } else {
          badgeDiff.style.background = "#f1f5fb";
          badgeDiff.style.color = "#64748b";
          badgeDiff.style.borderColor = "#cbd5e1";
        }
      }

      // Feedback badge
      const fBadge = document.getElementById("volt-feedback-badge");
      if (fBadge) {
        if (vab > 0) {
          fBadge.innerHTML = `<span>⚡ Titik A berpotensial lebih tinggi (+${absVal}V). Arus konvensional mengalir dari A ke B.</span>`;
        } else if (vab < 0) {
          fBadge.innerHTML = `<span>⚠️ Titik A berpotensial lebih rendah (-${absVal}V). Arus konvensional mengalir dari B ke A.</span>`;
        } else {
          fBadge.innerHTML = `<span>○ Titik A dan Titik B ekuipotensial (VAB = 0V). Tidak ada beda potensial, arus tidak mengalir.</span>`;
        }
      }

      updateVoltChecklist();
    }

    /* ==========================================================================
       Modul 01: Step 3 Voltmeter Polarity & Probes
       ========================================================================== */

    function toggleVoltProbeSwap() {
      voltState.probesReversed = !voltState.probesReversed;

      const lcd = document.getElementById("meter-lcd-val");
      const redTarget = document.getElementById("probe-red-target");
      const blackTarget = document.getElementById("probe-black-target");
      const exp = document.getElementById("meter-explanation-text");

      if (voltState.probesReversed) {
        if (lcd) {
          lcd.textContent = "-12.0 V";
          lcd.style.color = "#f87171";
        }
        if (redTarget) redTarget.textContent = "Terhubung ke Titik B (0 V)";
        if (blackTarget) blackTarget.textContent = "Terhubung ke Titik A (12 V)";
        if (exp) {
          exp.innerHTML = `
            📌 <strong>Analisis Pengukuran (Probe Dibalik):</strong><br>
            Probe merah sekarang mengukur 0V dan probe hitam mengukur 12V.<br>
            Display multimeter menghitung: <strong>Vdisplay = 0V − 12V = -12.0 V</strong>.<br>
            <em>Besar nilai tegangan tetap 12V, namun tanda menjadi minus (−) karena acuan probe terbalik!</em>
          `;
        }
      } else {
        if (lcd) {
          lcd.textContent = "+12.0 V";
          lcd.style.color = "#38bdf8";
        }
        if (redTarget) redTarget.textContent = "Terhubung ke Titik A (12 V)";
        if (blackTarget) blackTarget.textContent = "Terhubung ke Titik B (0 V)";
        if (exp) {
          exp.innerHTML = `
            📌 <strong>Analisis Pengukuran (Probe Normal):</strong><br>
            Probe merah mengukur 12V dan probe hitam mengukur 0V.<br>
            Display multimeter menghitung: <strong>Vdisplay = 12V − 0V = +12.0 V</strong>.
          `;
        }
      }

      updateVoltChecklist();
    }

    function checkPolarityPrediction(opt, btnEl) {
      voltState.polarityPredictionAnswer = opt;
      voltState.polarityPredictionSubmitted = true;

      const card = btnEl.closest(".prediction-card");
      if (card) {
        card.querySelectorAll(".qc-option-btn").forEach(b => b.classList.remove("selected", "correct", "wrong"));
        btnEl.classList.add("selected");
      }

      const fb = document.getElementById("polarity-pred-feedback");
      const reveal = document.getElementById("polarity-pred-reveal");

      if (opt === 'B') {
        btnEl.classList.add("correct");
        if (fb) {
          fb.style.display = "block";
          fb.innerHTML = `<span style="color: #059669; font-weight: 700;">✓ Benar! Layar Voltmeter akan menampilkan -12.0 V.</span>`;
        }
      } else {
        btnEl.classList.add("wrong");
        if (fb) {
          fb.style.display = "block";
          fb.innerHTML = `<span style="color: #ef4444; font-weight: 700;">✗ Kurang tepat. Jawaban yang benar adalah B (-12.0 V).</span>`;
        }
      }

      if (reveal) reveal.classList.add("open");
      updateVoltChecklist();
    }

    /* ==========================================================================
       Modul 01: Step 4 Source Voltage Experiment
       ========================================================================== */

    function handleSourceVoltageSlider(val) {
      val = parseFloat(val) || 0;
      voltState.sourceVoltage = val;
      const num = document.getElementById("num-vs");
      const slider = document.getElementById("slider-vs");
      if (num) num.value = val;
      if (slider) slider.value = val;
      updateSourceVisuals();
    }

    function setSourceVoltagePreset(val) {
      voltState.sourceVoltage = val;
      const num = document.getElementById("num-vs");
      const slider = document.getElementById("slider-vs");
      if (num) num.value = val;
      if (slider) slider.value = val;
      updateSourceVisuals();
    }

    function toggleBatteryPolarity() {
      voltState.batteryReversed = !voltState.batteryReversed;
      const btnLabel = document.getElementById("btn-battery-polarity-label");
      if (btnLabel) {
        btnLabel.textContent = voltState.batteryReversed ?
          "⇄ Balik Polaritas Baterai (Saat ini: Terbalik −/+)" :
          "⇄ Balik Polaritas Baterai (Saat ini: Normal +/−)";
      }
      updateSourceVisuals();
    }

    function updateSourceVisuals() {
      const vs = voltState.sourceVoltage;
      const isRev = voltState.batteryReversed;
      const sign = isRev ? "-" : "+";
      const signedVal = isRev ? -vs : vs;

      const svgBatLabel = document.getElementById("svg-bat-label");
      const svgPlus = document.getElementById("svg-bat-plus");
      const svgMinus = document.getElementById("svg-bat-minus");
      const svgVload = document.getElementById("svg-vload-val");
      const loadVal = document.getElementById("source-load-val");
      const ohmRel = document.getElementById("source-ohm-relation");

      if (svgBatLabel) svgBatLabel.textContent = `${vs}V`;
      if (svgPlus) svgPlus.textContent = isRev ? "−" : "+";
      if (svgMinus) svgMinus.textContent = isRev ? "+" : "−";

      const displayStr = `${signedVal > 0 ? '+' : (signedVal < 0 ? '-' : '')}${Math.abs(signedVal).toFixed(1)} V`;
      if (svgVload) svgVload.textContent = displayStr;
      if (loadVal) {
        loadVal.textContent = displayStr;
        loadVal.style.color = isRev ? "#ef4444" : "#2563eb";
      }

      const currentMilli = ((vs / 600) * 1000).toFixed(1);
      if (ohmRel) {
        ohmRel.innerHTML = `
          🔗 <strong>Hubungan ke Modul Hukum Ohm (I = V / R):</strong><br>
          Arus mengalir = ${vs} V / 600 Ω = <strong style="color: #059669;">${currentMilli} mA</strong> ${isRev ? '(Arah arus berbalik)' : ''}
        `;
      }

      updateVoltChecklist();
    }

    function checkSourcePrediction(opt, btnEl) {
      voltState.sourcePredictionAnswer = opt;
      voltState.sourcePredictionSubmitted = true;

      const card = btnEl.closest(".prediction-card");
      if (card) {
        card.querySelectorAll(".qc-option-btn").forEach(b => b.classList.remove("selected", "correct", "wrong"));
        btnEl.classList.add("selected");
      }

      const fb = document.getElementById("source-pred-feedback");
      const reveal = document.getElementById("source-pred-reveal");

      if (opt === 'A') {
        btnEl.classList.add("correct");
        if (fb) {
          fb.style.display = "block";
          fb.innerHTML = `<span style="color: #059669; font-weight: 700;">✓ Benar! Beda potensial beban bertambah menjadi 12V.</span>`;
        }
      } else {
        btnEl.classList.add("wrong");
        if (fb) {
          fb.style.display = "block";
          fb.innerHTML = `<span style="color: #ef4444; font-weight: 700;">✗ Kurang tepat. Beda potensial beban akan bertambah mengikuti sumber.</span>`;
        }
      }

      if (reveal) reveal.classList.add("open");
      updateVoltChecklist();
    }

    /* ==========================================================================
       Modul 01: Step 5 Measurement Practice Checking
       ========================================================================== */

    function checkVoltPractice(exerciseNum) {
      voltState.practiceAttempts[exerciseNum] = true;
      const inputEl = document.getElementById(`volt-prac-input-${exerciseNum}`);
      const feedbackEl = document.getElementById(`volt-prac-feedback-${exerciseNum}`);
      if (!inputEl || !feedbackEl) return;

      const raw = inputEl.value.trim().toLowerCase().replace(/volt|v/g, '').trim();
      const num = parseFloat(raw);

      let isCorrect = false;
      let wrongSignMsg = null;

      if (exerciseNum === 1) {
        // Expected: 7
        isCorrect = (!isNaN(num) && Math.abs(num - 7) < 0.1);
      } else if (exerciseNum === 2) {
        // Expected: -6
        if (!isNaN(num) && Math.abs(num - (-6)) < 0.1) {
          isCorrect = true;
        } else if (!isNaN(num) && Math.abs(num - 6) < 0.1) {
          wrongSignMsg = "Besarnya sudah benar (6 V), namun perhatikan urutan titik: V_AB = V_A − V_B = 3 − 9 = <strong>-6 V</strong>. Titik A lebih rendah potensialnya daripada Titik B.";
        }
      } else if (exerciseNum === 3) {
        // Expected: 6
        isCorrect = (!isNaN(num) && Math.abs(num - 6) < 0.1);
      } else if (exerciseNum === 4) {
        // Expected: -6
        if (!isNaN(num) && Math.abs(num - (-6)) < 0.1) {
          isCorrect = true;
        } else if (!isNaN(num) && Math.abs(num - 6) < 0.1) {
          wrongSignMsg = "Besarnya sudah benar, namun probe terbalik: Vdisplay = Vmerah − Vhitam = 2 − 8 = <strong>-6 V</strong>.";
        }
      }

      feedbackEl.style.display = "block";
      if (isCorrect) {
        feedbackEl.className = "practice-feedback correct";
        feedbackEl.innerHTML = "✓ Jawabanmu benar! Perhitungan dan tanda polaritas tepat.";
      } else if (wrongSignMsg) {
        feedbackEl.className = "practice-feedback wrong";
        feedbackEl.innerHTML = `⚠️ ${wrongSignMsg}`;
      } else {
        feedbackEl.className = "practice-feedback wrong";
        feedbackEl.innerHTML = "✗ Jawaban belum tepat. Periksa kembali rumus V = V1 − V2 atau buka pembahasan.";
      }

      updateVoltChecklist();
    }

    function toggleVoltSolution(solId, btnEl) {
      const sol = document.getElementById(solId);
      if (!sol) return;
      sol.classList.toggle("open");
      const isOpen = sol.classList.contains("open");
      btnEl.innerHTML = isOpen ?
        "<span>Sembunyikan Pembahasan</span><span>▲</span>" :
        "<span>Lihat Pembahasan</span><span>▼</span>";
    }

    /* ==========================================================================
       Modul 01: Step 6 Quiz & Progression
       ========================================================================== */

    function selectVoltQuizOption(qIdx, optIdx) {
      voltState.quizAnswers[qIdx] = optIdx;
      for (let i = 0; i < 3; i++) {
        const lbl = document.getElementById(`lbl-volt-q-${qIdx}-opt-${i}`);
        if (lbl) lbl.classList.toggle("selected", i === optIdx);
      }
      updateVoltChecklist();
    }

    function submitVoltQuiz() {
      let score = 0;
      const total = VOLT_QUIZ_QUESTIONS.length;

      VOLT_QUIZ_QUESTIONS.forEach((item, idx) => {
        const card = document.getElementById(`volt-quiz-card-${idx}`);
        const userAns = voltState.quizAnswers[idx];
        const isCorrect = (userAns === item.correct);

        if (isCorrect) score++;

        if (card) {
          card.classList.remove("quiz-card-correct", "quiz-card-wrong");
          card.classList.add(isCorrect ? "quiz-card-correct" : "quiz-card-wrong");

          let fb = card.querySelector(".quiz-explanation-box");
          if (!fb) {
            fb = document.createElement("div");
            fb.className = "quiz-explanation-box";
            card.appendChild(fb);
          }
          fb.style.display = "block";
          fb.innerHTML = `
            <strong>${isCorrect ? "✓ Jawaban Benar" : "✗ Jawaban Salah"}</strong> — ${item.explanation}
          `;
        }
      });

      voltState.quizSubmitted = true;

      const resCard = document.getElementById("volt-quiz-result-card");
      const scoreDisp = document.getElementById("volt-quiz-score-display");
      const msgDisp = document.getElementById("volt-quiz-feedback-msg");

      if (resCard) resCard.style.display = "block";
      if (scoreDisp) scoreDisp.textContent = `${score} / ${total} benar (${Math.round((score/total)*100)}%)`;
      if (msgDisp) {
        if (score === total) {
          msgDisp.textContent = "Luar biasa! Pemahamanmu mengenai konsep tegangan, beda potensial, dan polaritas sempurna.";
        } else if (score >= 4) {
          msgDisp.textContent = "Bagus! Kamu telah memahami sebagian besar konsep. Pelajari penjelasan soal yang belum tepat di atas.";
        } else {
          msgDisp.textContent = "Disarankan untuk meninjau kembali konsep beda potensial dan polaritas probe sebelum mengulangi kuis.";
        }
      }

      updateVoltChecklist();
    }

    function resetVoltQuiz() {
      voltState.quizAnswers = {};
      voltState.quizSubmitted = false;

      VOLT_QUIZ_QUESTIONS.forEach((item, idx) => {
        const card = document.getElementById(`volt-quiz-card-${idx}`);
        if (card) {
          card.classList.remove("quiz-card-correct", "quiz-card-wrong");
          const fb = card.querySelector(".quiz-explanation-box");
          if (fb) fb.style.display = "none";
        }
        for (let i = 0; i < 3; i++) {
          const lbl = document.getElementById(`lbl-volt-q-${idx}-opt-${i}`);
          if (lbl) lbl.classList.remove("selected");
          const rad = document.querySelector(`input[name="volt_quiz_q_${idx}"][value="${i}"]`);
          if (rad) rad.checked = false;
        }
      });

      const resCard = document.getElementById("volt-quiz-result-card");
      if (resCard) resCard.style.display = "none";

      updateVoltChecklist();
    }

    function updateVoltChecklist() {
      // Requirements
      const r1 = !!(voltState.exploredConcepts && voltState.exploredConcepts.v && voltState.exploredConcepts.dv && voltState.exploredConcepts.pol);
      const r2 = completedVoltSteps.has(2);
      const r3 = voltState.probesReversed || voltState.polarityPredictionSubmitted;
      const r4 = completedVoltSteps.has(4);
      const r5 = Object.values(voltState.practiceAttempts).some(v => v === true);
      const r6 = voltState.quizSubmitted;

      const setChk = (id, done) => {
        const el = document.getElementById(id);
        if (el) {
          el.classList.toggle("done", done);
          const icon = el.querySelector("span");
          if (icon) icon.textContent = done ? "✓" : "○";
        }
      };

      setChk("chk-volt-concepts", r1);
      setChk("chk-volt-explorer", r2);
      setChk("chk-volt-probe", r3);
      setChk("chk-volt-source", r4);
      setChk("chk-volt-practice", r5);
      setChk("chk-volt-quiz", r6);

      const allDone = r1 && r2 && r3 && r4 && r5 && r6;
      voltState.isUnlocked = allDone;

      const finishBtn = document.getElementById("btn-finish-volt-module");
      const helper = document.getElementById("volt-completion-lock-helper");

      if (finishBtn) finishBtn.disabled = !allDone;
      if (helper) {
        helper.innerHTML = allDone ?
          "🎉 Seluruh kriteria kelulusan terpenuhi! Silakan klik tombol di atas untuk menyelesaikan modul." :
          "🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 6 untuk membuka tombol selesai.";
      }
    }

    async function finishAndSaveVoltageModule(dbId) {
      const btn = document.getElementById("btn-finish-volt-module");
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = "<span>Menyimpan Progress...</span>";
      }
      await updateModuleProgress(dbId, 'selesai');
      if (btn) {
        btn.innerHTML = "<span>✓ Selesai & Tersimpan!</span>";
      }
      setTimeout(() => {
        closeModuleModal();
      }, 1000);
    }

/* ==========================================================================
       Interactive Learning Module Engine (Modul 02: Hukum Ohm)
       ========================================================================== */

    let currentModuleStep = 1;
    let completedModuleSteps = new Set([1]);
    let currentDbModuleId = 2;

    const interactiveState = {
      exploredVir: { v: false, i: false, r: false },
      voltage: 12,
      resistance: 500,
      prevV: 12,
      prevR: 500,
      formulaExplorerUsed: false,
      pred1Answer: null,
      pred2Answer: null,
      predictionAttempted: false,
      prac1Answer: "",
      prac2Answer: "",
      prac3Answer: "",
      calculationPracticeAttempted: false,
      quizAnswers: {},
      quizAttempted: false,
      quizSubmitted: false
    };

    const QUIZ_QUESTIONS = [
      {
        q: "Jika tegangan (V) tetap konstan dan nilai hambatan (R) dinaikkan, maka kuat arus (I) akan...",
        options: ["Naik", "Turun", "Tetap"],
        correct: 1,
        explanation: "Sesuai rumus I = V / R, kuat arus (I) berbanding terbalik dengan nilai hambatan (R). Jika pembagi membesar, nilai arus pasti menurun."
      },
      {
        q: "Rumus matematis Hukum Ohm yang benar untuk mencari kuat arus listrik (I) adalah...",
        options: ["I = V / R", "I = V × R", "I = R / V"],
        correct: 0,
        explanation: "Bentuk dasar Hukum Ohm adalah V = I × R. Jika diturunkan untuk mencari arus, kedua ruas dibagi R sehingga menjadi I = V / R."
      },
      {
        q: "Sumber tegangan 12 Volt dihubungkan melintasi resistor 600 Ω. Kuat arus yang mengalir adalah...",
        options: ["2 mA", "20 mA", "200 mA"],
        correct: 1,
        explanation: "I = 12 V / 600 Ω = 0.02 A. Dikonversi ke miliampere: 0.02 × 1000 = 20 mA."
      },
      {
        q: "Jika kuat arus listrik I = 2 A mengalir melewati resistor R = 5 Ω, berapakah tegangan V pada komponen tersebut?",
        options: ["2.5 V", "7 V", "10 V"],
        correct: 2,
        explanation: "Gunakan rumus V = I × R = 2 A × 5 Ω = 10 Volt."
      },
      {
        q: "Jika tegangan (V) dinaikkan menjadi dua kali lipat sementara hambatan (R) dijaga tetap, kuat arus (I) akan...",
        options: ["Bertambah kira-kira dua kali lipat", "Berkurang menjadi setengahnya", "Bernilai nol"],
        correct: 0,
        explanation: "Kuat arus berbanding lurus dengan tegangan (I ∝ V). Jika tegangan berlipat ganda, laju aliran muatan juga berlipat ganda."
      }
    ];

    function openInteractiveModule(dbId, moduleNum = 2) {
      currentDbModuleId = dbId;
      currentModuleStep = 1;
      completedModuleSteps = new Set([1]);

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="sp-fullscreen-backdrop" onclick="closeModuleModal()">
          <div class="sp-fullscreen-container" onclick="event.stopPropagation()">
            
            <!-- 1. Full-Screen Module Header -->
            <header class="sp-fullscreen-header">
              <div class="sp-header-left">
                <span class="interactive-module-badge">⚡ MODUL 02 • DASAR TEKNIK ELEKTRO</span>
                <h2 class="sp-header-title">Hambatan Listrik & Hukum Ohm</h2>
              </div>

              <div class="sp-header-center">
                <div class="sp-progress-wrapper">
                  <div class="sp-progress-bar">
                    <div class="sp-progress-fill" id="interactive-progress-fill" style="width: 20%;"></div>
                  </div>
                  <span class="sp-progress-text" id="interactive-progress-text">Langkah 1 dari 5 (20%)</span>
                </div>
              </div>

              <div class="sp-header-right">
                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" title="Buka Rangkaian di Simulator">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                  <span>Coba di Simulator</span>
                </a>
                <button class="btn-close-modal" onclick="closeModuleModal()" aria-label="Tutup Modul">✕</button>
              </div>
            </header>

            <!-- 2. Full-Screen Step Navigation Tabs Bar -->
            <nav class="sp-tabs-bar" role="tablist">
              <button class="sp-tab-item active" id="tab-btn-1" onclick="goToStep(1)">
                <span class="tab-badge">1</span>
                <span>Kenali V, I, R</span>
              </button>
              <button class="sp-tab-item" id="tab-btn-2" onclick="goToStep(2)">
                <span class="tab-badge">2</span>
                <span>Eksplorasi Formula</span>
              </button>
              <button class="sp-tab-item" id="tab-btn-3" onclick="goToStep(3)">
                <span class="tab-badge">3</span>
                <span>Prediksi Perubahan</span>
              </button>
              <button class="sp-tab-item" id="tab-btn-4" onclick="goToStep(4)">
                <span class="tab-badge">4</span>
                <span>Latihan Perhitungan</span>
              </button>
              <button class="sp-tab-item" id="tab-btn-5" onclick="goToStep(5)">
                <span class="tab-badge">5</span>
                <span>Quiz & Simulator</span>
              </button>
            </nav>

            <!-- 3. Full-Screen Scrollable Body -->
            <main class="sp-fullscreen-body" id="interactive-modal-body">
              
              <!-- ================================================================
                   LANGKAH 1: KENALI V, I, DAN R
                   ================================================================ -->
              <div class="step-content-panel active" id="step-panel-1">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 1 DARI 5 • KENALI V, I, DAN R</span>
                  <h3 class="step-title">3 Besaran Utama Rangkaian Listrik</h3>
                  <p class="step-desc">
                    Sebelum menghubungkan variabel dalam formula, pelajari simbol, satuan, definisi, dan peran masing-masing besaran berikut. <strong>Klik ketiga kartu di bawah untuk membuka penjelasannya:</strong>
                  </p>
                </div>

                <div class="concept-cards-grid">
                  <!-- Card Tegangan V -->
                  <div class="interactive-concept-card" id="card-v" onclick="exploreVirCard('v')">
                    <div class="card-symbol-badge">V</div>
                    <div class="concept-card-title">TEGANGAN (VOLTAGE)</div>
                    <div class="concept-card-unit">Simbol: V • Satuan: Volt (V)</div>
                    <p class="concept-card-desc">Beda potensial yang mendorong muatan listrik mengalir dalam suatu rangkaian.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Peran dalam Hukum Ohm:</strong> Sebagai sumber energi penggerak. Diukur menggunakan <strong>Voltmeter secara PARALEL</strong> terhadap beban atau baterai.
                    </div>
                  </div>

                  <!-- Card Arus I -->
                  <div class="interactive-concept-card" id="card-i" onclick="exploreVirCard('i')">
                    <div class="card-symbol-badge">I</div>
                    <div class="concept-card-title">ARUS (CURRENT)</div>
                    <div class="concept-card-unit">Simbol: I • Satuan: Ampere (A)</div>
                    <p class="concept-card-desc">Laju aliran muatan listrik (elektron) yang melintasi penampang kawat per satuan detik.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Peran dalam Hukum Ohm:</strong> Sebagai respon aliran muatan akibat dorongan tegangan. Diukur menggunakan <strong>Amperemeter secara SERI</strong> dengan memutus jalur kawat.
                    </div>
                  </div>

                  <!-- Card Hambatan R -->
                  <div class="interactive-concept-card" id="card-r" onclick="exploreVirCard('r')">
                    <div class="card-symbol-badge">R</div>
                    <div class="concept-card-title">HAMBATAN (RESISTANCE)</div>
                    <div class="concept-card-unit">Simbol: R • Satuan: Ohm (Ω)</div>
                    <p class="concept-card-desc">Karakteristik bahan konduktor yang menghambat laju aliran elektron bebas.</p>
                    <div class="concept-card-detail">
                      📌 <strong>Peran dalam Hukum Ohm:</strong> Sebagai pembatas arus agar komponen tidak rusak. Diukur dengan <strong>Ohmmeter saat rangkaian MATI (OFF)</strong>.
                    </div>
                  </div>
                </div>

                <!-- Exploration Status Indicator -->
                <div id="vir-explored-status" style="margin-top: 14px; padding: 10px 14px; border-radius: 8px; font-size: 0.86rem; font-weight: 600; display: none; background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #a7f3d0;">
                  ✓ Ketiga besaran (V, I, R) telah dipelajari! Silakan lanjutkan ke Langkah 2 (Eksplorasi Formula).
                </div>

                <!-- Collapsible Theory Explanation -->
                <div class="collapsible-box" id="collapsible-step1">
                  <button class="collapsible-header" onclick="toggleCollapsible('collapsible-step1')">
                    <span>💡 Mengapa elektron mengalami hambatan di dalam kawat konduktor?</span>
                    <span class="collapsible-icon">▼</span>
                  </button>
                  <div class="collapsible-body">
                    Di tingkat atom, hambatan listrik timbul karena elektron bebas yang bergerak terdorong oleh beda potensial (tegangan) mengalami benturan berulang kali dengan ion-ion atom kisi logam konduktor. Resistor dibuat dari bahan dengan resistivitas tertentu untuk membatasi jumlah elektron yang berhasil lewat setiap detiknya, sehingga besar kuat arus dapat dikendalikan dengan presisi.
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 2: EKSPLORASI HUKUM OHM (REAL-TIME V-I-R EXPLORER)
                   ================================================================ -->
              <div class="step-content-panel" id="step-panel-2">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 2 DARI 5 • EKSPLORASI HUKUM OHM</span>
                  <h3 class="step-title">Real-Time V-I-R Calculator & Explorer</h3>
                  <p class="step-desc">
                    Ubah nilai Tegangan (V) dan Hambatan (R) menggunakan slider atau ketikkan angka langsung. Perhatikan bagaimana nilai kuat arus (I) dan rumus substitusi langsung terhitung secara real-time.
                  </p>
                </div>

                <div class="formula-hero-display">
                  V = I × R &nbsp;&nbsp;|&nbsp;&nbsp; I = V / R &nbsp;&nbsp;|&nbsp;&nbsp; R = V / I
                </div>

                <div class="formula-explorer-container">
                  <div class="calc-grid">
                    <!-- Sliders & Inputs Panel -->
                    <div class="calc-sliders-panel">
                      <!-- Voltage Control -->
                      <div class="slider-control-card">
                        <div class="slider-header">
                          <span class="slider-label">Tegangan Sumber (V):</span>
                          <div class="slider-header-controls">
                            <input type="number" class="calc-number-input" id="num-voltage" min="0" max="24" step="1" value="12" oninput="handleExplorerNumberInput('V', this.value)">
                            <span class="practice-unit-badge">Volt</span>
                          </div>
                        </div>
                        <input type="range" class="slider-input-range" id="slider-voltage" min="0" max="24" step="1" value="12" oninput="handleExplorerSliderInput('V', this.value)">
                        <div class="slider-ticks">
                          <span>0 V</span>
                          <span>12 V (Default)</span>
                          <span>24 V</span>
                        </div>
                      </div>

                      <!-- Resistance Control -->
                      <div class="slider-control-card">
                        <div class="slider-header">
                          <span class="slider-label">Hambatan Resistor (R):</span>
                          <div class="slider-header-controls">
                            <input type="number" class="calc-number-input" id="num-resistance" min="10" max="2000" step="10" value="500" oninput="handleExplorerNumberInput('R', this.value)">
                            <span class="practice-unit-badge">Ω</span>
                          </div>
                        </div>
                        <input type="range" class="slider-input-range" id="slider-resistance" min="10" max="2000" step="10" value="500" oninput="handleExplorerSliderInput('R', this.value)">
                        <div class="slider-ticks">
                          <span>10 Ω</span>
                          <span>500 Ω</span>
                          <span>2000 Ω (2 kΩ)</span>
                        </div>
                      </div>
                    </div>

                    <!-- Output & Dynamic Math Display -->
                    <div class="calc-output-panel">
                      <div class="output-heading">Substitusi Rumus & Hasil Kuat Arus (I):</div>
                      <div class="calc-math-equation" id="calc-equation-display">
                        I = V / R<br>
                        I = 12 / 500<br>
                        I = 0.024 A<br>
                        I = 24.0 mA
                      </div>
                      
                      <div class="calc-big-result">
                        <span class="big-result-num" id="calc-current-val">24.0</span>
                        <span class="big-result-unit">mA (miliampere)</span>
                      </div>

                      <div style="font-family: var(--font-mono, monospace); font-size: 0.82rem; color: var(--color-text-secondary, #64748b);">
                        Konversi Satuan: <strong id="calc-ampere-val" style="color: var(--color-primary, #2563eb);">0.024 A</strong> (1 A = 1000 mA)
                      </div>

                      <!-- Dynamic Conceptual Feedback Badge -->
                      <div class="relation-feedback-badge" id="relation-feedback-badge">
                        <span>⚡ Status: V = 12V, R = 500Ω → Arus mengalir normal (24 mA)</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Triangle Aid -->
                <div class="collapsible-box" id="collapsible-triangle">
                  <button class="collapsible-header" onclick="toggleCollapsible('collapsible-triangle')">
                    <span>📐 Metode Bantuan: Segitiga Rumus V-I-R</span>
                    <span class="collapsible-icon">▼</span>
                  </button>
                  <div class="collapsible-body">
                    <pre style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); padding: 12px; border-radius: 8px; font-family: var(--font-mono, monospace); color: var(--color-primary, #2563eb); text-align: center; margin: 8px 0; font-weight: 700;">
        [  V  ]
      -----------
      [ I ] × [ R ]
                    </pre>
                    • Tutup huruf <strong>V</strong>: V = I × R<br>
                    • Tutup huruf <strong>I</strong>: I = V / R<br>
                    • Tutup huruf <strong>R</strong>: R = V / I
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 3: PREDIKSI PERUBAHAN
                   ================================================================ -->
              <div class="step-content-panel" id="step-panel-3">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 3 DARI 5 • PREDIKSI PERUBAHAN</span>
                  <h3 class="step-title">Tantangan Intuisi Fisika Kelistrikan</h3>
                  <p class="step-desc">
                    Sebelum melihat hasil kalkulator, uji pemahaman intuisimu mengenai hubungan sebab-akibat antara tegangan, hambatan, dan kuat arus.
                  </p>
                </div>

                <!-- Prediction 1 -->
                <div class="prediction-card" id="pred-card-1">
                  <span class="qc-badge">PREDIKSI 1: HAMBATAN DIPERBESAR</span>
                  <div class="prediction-scenario">
                    Kondisi Awal: V = 12 V, R = 500 Ω (Arus I = 24 mA)<br>
                    Perubahan: Tegangan tetap 12 V, Hambatan dinaikkan dari 500 Ω menjadi 1000 Ω (2× lipat).
                  </div>
                  <div class="qc-question">
                    Apa yang akan terjadi pada nilai kuat arus listrik (I)?
                  </div>
                  <div class="qc-options-list">
                    <button class="qc-option-btn" onclick="checkPrediction(1, 'A', this)">A. Bertambah</button>
                    <button class="qc-option-btn" onclick="checkPrediction(1, 'B', this)">B. Berkurang</button>
                    <button class="qc-option-btn" onclick="checkPrediction(1, 'C', this)">C. Tetap</button>
                  </div>
                  <div class="qc-feedback-panel" id="pred-feedback-1"></div>
                  <div class="prediction-math-reveal" id="pred-reveal-1">
                    <strong>Kalkulasi Pembuktian:</strong><br>
                    • Kondisi Awal: I = 12 V / 500 Ω = 0.024 A = 24.0 mA<br>
                    • Kondisi Baru: I = 12 V / 1000 Ω = 0.012 A = 12.0 mA<br>
                    <em>Arus berkurang tepat setengahnya (dari 24 mA menjadi 12 mA).</em>
                  </div>
                </div>

                <!-- Prediction 2 -->
                <div class="prediction-card" id="pred-card-2">
                  <span class="qc-badge">PREDIKSI 2: TEGANGAN DINAIKKAN</span>
                  <div class="prediction-scenario">
                    Kondisi Awal: R = 500 Ω, V = 6 V (Arus I = 12 mA)<br>
                    Perubahan: Hambatan tetap 500 Ω, Tegangan sumber dinaikkan dari 6 V menjadi 12 V (2× lipat).
                  </div>
                  <div class="qc-question">
                    Apa yang akan terjadi pada nilai kuat arus listrik (I)?
                  </div>
                  <div class="qc-options-list">
                    <button class="qc-option-btn" onclick="checkPrediction(2, 'A', this)">A. Bertambah</button>
                    <button class="qc-option-btn" onclick="checkPrediction(2, 'B', this)">B. Berkurang</button>
                    <button class="qc-option-btn" onclick="checkPrediction(2, 'C', this)">C. Tetap</button>
                  </div>
                  <div class="qc-feedback-panel" id="pred-feedback-2"></div>
                  <div class="prediction-math-reveal" id="pred-reveal-2">
                    <strong>Kalkulasi Pembuktian:</strong><br>
                    • Kondisi Awal: I = 6 V / 500 Ω = 0.012 A = 12.0 mA<br>
                    • Kondisi Baru: I = 12 V / 500 Ω = 0.024 A = 24.0 mA<br>
                    <em>Arus bertambah dua kali lipat (dari 12 mA menjadi 24 mA).</em>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 4: LATIHAN PERHITUNGAN
                   ================================================================ -->
              <div class="step-content-panel" id="step-panel-4">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 4 DARI 5 • LATIHAN PERHITUNGAN</span>
                  <h3 class="step-title">Latihan Mandiri Numerik Hukum Ohm</h3>
                  <p class="step-desc">
                    Hitung besaran kelistrikan berikut dan ketikkan jawabanmu pada kotak input. Kamu dapat membuka pembahasan langkah demi langkah kapan saja.
                  </p>
                </div>

                <!-- Practice 1: Find I -->
                <div class="practice-exercise-card" id="prac-card-1">
                  <span class="example-header-tag">LATIHAN 1: MENCARI KUAT ARUS (I)</span>
                  <div class="example-question">
                    Sebuah resistor <strong>600 Ω</strong> dihubungkan ke sumber tegangan <strong>12 Volt</strong>. Berapakah kuat arus listrik yang mengalir? <em>(Tuliskan jawaban dalam miliampere / mA)</em>
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="prac-input-1" placeholder="Contoh: 20">
                    <span class="practice-unit-badge">mA</span>
                    <button class="btn-check-practice" onclick="checkCalculationPractice(1)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleSolution('prac-sol-1', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="prac-feedback-1"></div>
                  <div class="solution-steps-container" id="prac-sol-1">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> V = 12 V, R = 600 Ω<br>
                      <strong>Rumus:</strong> I = V / R<br>
                      <strong>Substitusi:</strong> I = 12 / 600 = 0.02 A<br>
                      <strong>Konversi ke mA:</strong> 0.02 A × 1000 = <strong>20 mA</strong>
                    </div>
                  </div>
                </div>

                <!-- Practice 2: Find V -->
                <div class="practice-exercise-card" id="prac-card-2">
                  <span class="example-header-tag">LATIHAN 2: MENCARI TEGANGAN (V)</span>
                  <div class="example-question">
                    Kuat arus sebesar <strong>0.5 Ampere</strong> mengalir melalui kumparan motor dengan resistansi <strong>20 Ω</strong>. Berapakah beda potensial tegangan listrik yang menyuplai komponen tersebut? <em>(Satuan: Volt)</em>
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="prac-input-2" placeholder="Contoh: 10">
                    <span class="practice-unit-badge">Volt (V)</span>
                    <button class="btn-check-practice" onclick="checkCalculationPractice(2)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleSolution('prac-sol-2', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="prac-feedback-2"></div>
                  <div class="solution-steps-container" id="prac-sol-2">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> I = 0.5 A, R = 20 Ω<br>
                      <strong>Rumus:</strong> V = I × R<br>
                      <strong>Substitusi:</strong> V = 0.5 A × 20 Ω = <strong>10 Volt</strong>
                    </div>
                  </div>
                </div>

                <!-- Practice 3: Find R -->
                <div class="practice-exercise-card" id="prac-card-3">
                  <span class="example-header-tag">LATIHAN 3: MENCARI HAMBATAN (R)</span>
                  <div class="example-question">
                    Sebuah rangkaian menghasilkan arus tepat <strong>0.02 A (20 mA)</strong> saat diberi tegangan <strong>12 Volt</strong>. Berapakah nilai hambatan resistor yang terpasang? <em>(Satuan: Ohm / Ω)</em>
                  </div>
                  <div class="practice-input-group">
                    <input type="text" class="practice-num-input" id="prac-input-3" placeholder="Contoh: 600">
                    <span class="practice-unit-badge">Ohm (Ω)</span>
                    <button class="btn-check-practice" onclick="checkCalculationPractice(3)">Periksa Jawaban</button>
                    <button class="btn-reveal-solution" onclick="toggleSolution('prac-sol-3', this)">
                      <span>Lihat Pembahasan</span><span>▼</span>
                    </button>
                  </div>
                  <div class="practice-feedback" id="prac-feedback-3"></div>
                  <div class="solution-steps-container" id="prac-sol-3">
                    <div class="solution-step-item">
                      <strong>Diketahui:</strong> V = 12 V, I = 0.02 A<br>
                      <strong>Rumus:</strong> R = V / I<br>
                      <strong>Substitusi:</strong> R = 12 / 0.02 = <strong>600 Ω</strong>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 5: QUIZ & COBA DI SIMULATOR
                   ================================================================ -->
              <div class="step-content-panel" id="step-panel-5">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 5 DARI 5 • QUIZ & SIMULATOR</span>
                  <h3 class="step-title">Evaluasi Akhir & Tantangan Simulator</h3>
                  <p class="step-desc">
                    Kerjakan 5 soal evaluasi di bawah ini untuk mengukur ketuntasan belajar. Selesaikan seluruh tahap modul untuk membuka tombol penyelesaian.
                  </p>
                </div>

                <!-- 5 Quiz Questions -->
                <div class="quiz-wrapper" id="quiz-wrapper">
                  ${QUIZ_QUESTIONS.map((item, qIdx) => `
                    <div class="quiz-card" id="quiz-card-${qIdx}">
                      <div class="quiz-q-header">
                        <span class="quiz-q-num">Soal ${qIdx + 1} dari ${QUIZ_QUESTIONS.length}</span>
                      </div>
                      <div class="quiz-q-text">${item.q}</div>
                      <div class="quiz-options-group">
                        ${item.options.map((opt, optIdx) => `
                          <label class="quiz-option-label" id="lbl-q-${qIdx}-opt-${optIdx}">
                            <input type="radio" name="quiz_q_${qIdx}" class="quiz-option-radio" value="${optIdx}" onchange="selectQuizOption(${qIdx}, ${optIdx})">
                            <span class="quiz-option-text">${opt}</span>
                          </label>
                        `).join('')}
                      </div>
                    </div>
                  `).join('')}

                  <button class="btn-submit-quiz" onclick="submitQuiz()">
                    <span>Periksa Hasil Quiz</span>
                  </button>
                </div>

                <!-- Quiz Result Card -->
                <div class="quiz-result-card" id="quiz-result-card">
                  <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: #10b981; letter-spacing: 0.05em;">HASIL PEMAHAMAN</span>
                  <div class="quiz-result-score" id="quiz-score-display">5 / 5 benar</div>
                  <p class="quiz-result-msg" id="quiz-feedback-msg"></p>
                  <button class="btn-step-nav btn-step-prev" onclick="resetQuiz()" style="margin: 0 auto;">
                    <span>🔄 Ulangi Quiz</span>
                  </button>
                </div>

                <!-- Practical Challenges for Simulator -->
                <div class="challenge-card">
                  <span class="challenge-badge">🎯 TANTANGAN PRAKTIKUM SIMULATOR</span>
                  <h4 class="challenge-title">Tantangan 1: Uji Rangkaian 12V + 600Ω</h4>
                  <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); line-height: 1.6; margin: 0 0 10px;">
                    Buat rangkaian dengan sumber <strong>12V</strong> dan resistor <strong>600Ω</strong> pada simulator, kemudian hubungkan Multimeter (mode Amperemeter DC secara seri) untuk mengukur arusnya.<br>
                    <strong>Arus Teoretis yang diharapkan:</strong> <span style="color: var(--color-primary, #2563eb); font-weight: 700;">20 mA</span>.
                  </p>

                  <h4 class="challenge-title" style="margin-top: 16px;">Tantangan 2: Pengujian Resistor 1200Ω</h4>
                  <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); line-height: 1.6; margin: 0 0 10px;">
                    Ubah nilai resistor menjadi <strong>1200 Ω</strong>. Prediksi arus sebelum menjalankan simulasi!
                  </p>
                  <button class="btn-hint" onclick="toggleTheoryReveal('chal-2-reveal', this)">
                    <span>💡 Lihat Nilai Teori</span>
                  </button>
                  <div class="hint-content-box" id="chal-2-reveal">
                    Perhitungan: I = 12 V / 1200 Ω = 0.01 A = <strong>10 mA</strong>.
                  </div>

                  <div style="margin-top: 18px;">
                    <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" style="padding: 10px 20px; font-size: 0.92rem;">
                      <span>🚀 Buka Simulator Sekarang</span>
                    </a>
                  </div>
                </div>

                <!-- Completion Lock Section (Section 23) -->
                <div class="completion-lock-box">
                  <div class="completion-lock-title">
                    <span>📋 Syarat Kelulusan Modul Interaktif:</span>
                  </div>
                  <div class="completion-checklist" id="completion-checklist">
                    <div class="checklist-item" id="chk-vir"><span>○</span> 1. Kenali V, I, dan R</div>
                    <div class="checklist-item" id="chk-explorer"><span>○</span> 2. Eksplorasi Formula</div>
                    <div class="checklist-item" id="chk-prediction"><span>○</span> 3. Uji Prediksi</div>
                    <div class="checklist-item" id="chk-practice"><span>○</span> 4. Latihan Perhitungan</div>
                    <div class="checklist-item" id="chk-quiz"><span>○</span> 5. Kerjakan Kuis Akhir</div>
                  </div>

                  <button class="btn-finish-module" id="btn-finish-module" onclick="finishAndSaveModule(${dbId})" disabled>
                    <span>✓ Tandai Selesai & Simpan Progress</span>
                  </button>
                  <div class="completion-lock-helper" id="completion-lock-helper">
                    🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 5 untuk membuka tombol selesai.
                  </div>
                </div>

              </div>

            </main>

            <!-- 4. Full-Screen Modal Footer -->
            <footer class="sp-fullscreen-footer">
              <button class="btn-step-nav btn-step-prev" id="btn-step-prev" onclick="goToStep(currentModuleStep - 1)" disabled>
                <span>← Sebelumnya</span>
              </button>

              <div class="footer-step-counter" id="footer-step-counter">
                Langkah 1 dari 5
              </div>

              <button class="btn-step-nav btn-step-next" id="btn-step-next" onclick="goToStep(currentModuleStep + 1)">
                <span>Langkah Selanjutnya →</span>
              </button>
            </footer>

          </div>
        </div>
      `;

      // Initial setup
      updateFormulaExplorer();
      updateCompletionChecklist();
    }

    /* ==========================================================================
       Step Navigation & State Preservation
       ========================================================================== */

    function goToStep(step) {
      if (step < 1 || step > 5) return;
      currentModuleStep = step;
      completedModuleSteps.add(step);

      // Update active panels
      document.querySelectorAll(".step-content-panel").forEach((panel, idx) => {
        panel.classList.toggle("active", idx + 1 === step);
      });

      // Update tabs
      for (let i = 1; i <= 5; i++) {
        const tab = document.getElementById(`tab-btn-${i}`);
        if (!tab) continue;
        tab.classList.toggle("active", i === step);
        tab.classList.toggle("completed", completedModuleSteps.has(i) && i !== step);
      }

      // Update progress bar
      const percent = Math.round((step / 5) * 100);
      const fill = document.getElementById("interactive-progress-fill");
      const text = document.getElementById("interactive-progress-text");
      const footerCounter = document.getElementById("footer-step-counter");

      if (fill) fill.style.width = `${percent}%`;
      if (text) text.textContent = `Langkah ${step} dari 5 (${percent}%)`;
      if (footerCounter) footerCounter.textContent = `Langkah ${step} dari 5`;

      // Update footer buttons
      const prevBtn = document.getElementById("btn-step-prev");
      const nextBtn = document.getElementById("btn-step-next");

      if (prevBtn) prevBtn.disabled = (step === 1);
      if (nextBtn) {
        if (step === 5) {
          nextBtn.style.display = "none"; // Handled by completion button on step 5
        } else {
          nextBtn.style.display = "inline-flex";
          nextBtn.innerHTML = `<span>Langkah Selanjutnya →</span>`;
          nextBtn.onclick = () => goToStep(step + 1);
        }
      }

      updateCompletionChecklist();

      // Scroll modal body to top
      const body = document.getElementById("interactive-modal-body");
      if (body) body.scrollTop = 0;
    }

    /* ==========================================================================
       Step 1: V, I, R Concept Exploration Tracker
       ========================================================================== */

    function exploreVirCard(key) {
      interactiveState.exploredVir[key] = true;

      const card = document.getElementById(`card-${key}`);
      if (card) card.classList.toggle("active");

      // Check if all 3 explored
      const allExplored = interactiveState.exploredVir.v && interactiveState.exploredVir.i && interactiveState.exploredVir.r;
      const statusBox = document.getElementById("vir-explored-status");
      if (statusBox && allExplored) {
        statusBox.style.display = "block";
      }

      updateCompletionChecklist();
    }

    function toggleCollapsible(boxId) {
      const box = document.getElementById(boxId);
      if (box) box.classList.toggle("open");
    }

    /* ==========================================================================
       Step 2: Dual Input & Slider V-I-R Calculator
       ========================================================================== */

    function handleExplorerSliderInput(param, val) {
      const numVal = parseFloat(val) || 0;
      if (param === 'V') {
        interactiveState.voltage = numVal;
        const numInput = document.getElementById("num-voltage");
        if (numInput) numInput.value = numVal;
      } else {
        interactiveState.resistance = Math.max(10, numVal);
        const numInput = document.getElementById("num-resistance");
        if (numInput) numInput.value = interactiveState.resistance;
      }
      interactiveState.formulaExplorerUsed = true;
      updateFormulaExplorer(param);
      updateCompletionChecklist();
    }

    function handleExplorerNumberInput(param, val) {
      let numVal = parseFloat(val);
      if (isNaN(numVal)) return;

      if (param === 'V') {
        numVal = Math.max(0, Math.min(24, numVal));
        interactiveState.voltage = numVal;
        const slider = document.getElementById("slider-voltage");
        if (slider) slider.value = numVal;
      } else {
        numVal = Math.max(10, Math.min(2000, numVal));
        interactiveState.resistance = numVal;
        const slider = document.getElementById("slider-resistance");
        if (slider) slider.value = numVal;
      }
      interactiveState.formulaExplorerUsed = true;
      updateFormulaExplorer(param);
      updateCompletionChecklist();
    }

    function updateFormulaExplorer(changedParam = null) {
      const v = interactiveState.voltage;
      const r = Math.max(10, interactiveState.resistance); // Safe clamp, no division by zero or NaN

      // Calculate Current (I = V / R)
      const current_A = v / r;
      const current_mA = current_A * 1000;

      // Update Math text display
      const eqDisplay = document.getElementById("calc-equation-display");
      if (eqDisplay) {
        eqDisplay.innerHTML = `
          I = V / R<br>
          I = ${v} / ${r}<br>
          I = ${current_A.toFixed(3)} A<br>
          I = <strong>${current_mA.toFixed(1)} mA</strong>
        `;
      }

      // Update Big number display
      const bigVal = document.getElementById("calc-current-val");
      if (bigVal) bigVal.textContent = current_mA.toFixed(1);

      const ampVal = document.getElementById("calc-ampere-val");
      if (ampVal) ampVal.textContent = `${current_A.toFixed(3)} A`;

      // Dynamic conceptual feedback badge
      const feedbackBadge = document.getElementById("relation-feedback-badge");
      if (feedbackBadge) {
        if (changedParam === 'R') {
          if (r > interactiveState.prevR) {
            feedbackBadge.innerHTML = `<span>Hambatan naik, maka arus menurun. (R ↑ → I ↓)</span>`;
            feedbackBadge.style.color = "#f59e0b";
            feedbackBadge.style.borderColor = "rgba(245, 158, 11, 0.4)";
          } else if (r < interactiveState.prevR) {
            feedbackBadge.innerHTML = `<span>Hambatan turun, maka arus meningkat. (R ↓ → I ↑)</span>`;
            feedbackBadge.style.color = "#38bdf8";
            feedbackBadge.style.borderColor = "rgba(56, 189, 248, 0.4)";
          }
        } else if (changedParam === 'V') {
          if (v > interactiveState.prevV) {
            feedbackBadge.innerHTML = `<span>Tegangan naik, maka arus meningkat. (V ↑ → I ↑)</span>`;
            feedbackBadge.style.color = "#10b981";
            feedbackBadge.style.borderColor = "rgba(16, 185, 129, 0.4)";
          } else if (v < interactiveState.prevV) {
            feedbackBadge.innerHTML = `<span>Tegangan turun, maka arus menurun. (V ↓ → I ↓)</span>`;
            feedbackBadge.style.color = "#94a3b8";
            feedbackBadge.style.borderColor = "rgba(148, 163, 184, 0.4)";
          }
        } else {
          feedbackBadge.innerHTML = `<span>⚡ V = ${v}V, R = ${r}Ω → I = ${current_mA.toFixed(1)} mA</span>`;
        }
      }

      interactiveState.prevR = r;
      interactiveState.prevV = v;
    }

    /* ==========================================================================
       Step 3: Prediction Challenge
       ========================================================================== */

    function checkPrediction(predId, choice, btn) {
      interactiveState.predictionAttempted = true;
      if (predId === 1) interactiveState.pred1Answer = choice;
      if (predId === 2) interactiveState.pred2Answer = choice;

      const card = document.getElementById(`pred-card-${predId}`);
      if (!card) return;

      const allBtns = card.querySelectorAll(".qc-option-btn");
      allBtns.forEach(b => {
        b.disabled = true;
        b.classList.remove("correct", "wrong");
      });

      const feedback = document.getElementById(`pred-feedback-${predId}`);
      const reveal = document.getElementById(`pred-reveal-${predId}`);

      const isCorrect = (predId === 1 && choice === 'B') || (predId === 2 && choice === 'A');

      if (isCorrect) {
        btn.classList.add("correct");
        if (feedback) {
          feedback.className = "qc-feedback-panel correct";
          feedback.innerHTML = `<strong>✓ Benar!</strong> Prediksimu tepat sesuai kaidah Hukum Ohm.`;
        }
        if (reveal) reveal.classList.add("open");
      } else {
        btn.classList.add("wrong");
        if (feedback) {
          feedback.className = "qc-feedback-panel wrong";
          feedback.innerHTML = `
            <strong>Belum tepat.</strong> Perhatikan kembali hubungan I = V / R.<br>
            <button class="btn-retry-qc" onclick="retryPrediction(${predId})">🔄 Coba Lagi</button>
          `;
        }
      }

      updateCompletionChecklist();
    }

    function retryPrediction(predId) {
      const card = document.getElementById(`pred-card-${predId}`);
      if (!card) return;

      card.querySelectorAll(".qc-option-btn").forEach(b => {
        b.disabled = false;
        b.classList.remove("correct", "wrong");
      });

      const feedback = document.getElementById(`pred-feedback-${predId}`);
      if (feedback) {
        feedback.className = "qc-feedback-panel";
        feedback.style.display = "none";
        feedback.innerHTML = "";
      }

      const reveal = document.getElementById(`pred-reveal-${predId}`);
      if (reveal) reveal.classList.remove("open");
    }

    /* ==========================================================================
       Step 4: Calculation Practice with Flexible Validation
       ========================================================================== */

    function checkCalculationPractice(qNum) {
      interactiveState.calculationPracticeAttempted = true;

      const input = document.getElementById(`prac-input-${qNum}`);
      const feedback = document.getElementById(`prac-feedback-${qNum}`);
      if (!input || !feedback) return;

      const rawVal = input.value.trim().toLowerCase().replace(",", ".");
      interactiveState[`prac${qNum}Answer`] = rawVal;

      let isCorrect = false;
      let errorHint = "";

      if (qNum === 1) {
        // Expected: 20 mA (or 0.02 A)
        if (rawVal === "20" || rawVal === "20 ma" || rawVal === "20ma") {
          isCorrect = true;
        } else if (rawVal === "0.02" || rawVal === "0.02 a" || rawVal === "0.02a") {
          errorHint = "Perhatikan konversi A ke mA. Nilai 0.02 A sama dengan 20 mA. Satuan yang diminta pada kotak adalah mA (masukkan angka 20).";
        } else {
          errorHint = "Gunakan rumus I = V / R = 12 / 600 = 0.02 A = 20 mA.";
        }
      } else if (qNum === 2) {
        // Expected: 10 V
        if (rawVal === "10" || rawVal === "10 v" || rawVal === "10v" || rawVal === "10 volt") {
          isCorrect = true;
        } else {
          errorHint = "Gunakan rumus V = I × R = 0.5 A × 20 Ω = 10 Volt.";
        }
      } else if (qNum === 3) {
        // Expected: 600 Ω
        if (rawVal === "600" || rawVal === "600 ohm" || rawVal === "600ohm" || rawVal === "600 ω" || rawVal === "600Ω") {
          isCorrect = true;
        } else {
          errorHint = "Gunakan rumus R = V / I = 12 / 0.02 = 600 Ω.";
        }
      }

      if (isCorrect) {
        feedback.className = "practice-feedback correct";
        feedback.innerHTML = `<strong>✓ Benar!</strong> Perhitunganmu tepat.`;
      } else {
        feedback.className = "practice-feedback wrong";
        feedback.innerHTML = `<strong>Belum tepat.</strong> ${errorHint}`;
      }

      updateCompletionChecklist();
    }

    function toggleSolution(solId, btn) {
      const sol = document.getElementById(solId);
      if (!sol) return;
      sol.classList.toggle("open");
      const isOpen = sol.classList.contains("open");
      btn.innerHTML = isOpen 
        ? `<span>Tutup Pembahasan</span><span>▲</span>` 
        : `<span>Lihat Pembahasan</span><span>▼</span>`;
    }

    /* ==========================================================================
       Step 5: Final 5-Question Quiz & Results
       ========================================================================== */

    function selectQuizOption(qIdx, optIdx) {
      interactiveState.quizAnswers[qIdx] = optIdx;

      const card = document.getElementById(`quiz-card-${qIdx}`);
      if (card) {
        card.querySelectorAll(".quiz-option-label").forEach((lbl, idx) => {
          lbl.classList.toggle("selected", idx === optIdx);
        });
      }
    }

    function submitQuiz() {
      interactiveState.quizAttempted = true;
      interactiveState.quizSubmitted = true;

      let correctCount = 0;
      const total = QUIZ_QUESTIONS.length;
      let wrongConcepts = [];

      QUIZ_QUESTIONS.forEach((q, idx) => {
        const userAns = interactiveState.quizAnswers[idx];
        const card = document.getElementById(`quiz-card-${idx}`);
        if (!card) return;

        card.querySelectorAll(".quiz-option-label").forEach((lbl, optIdx) => {
          if (optIdx === q.correct) {
            lbl.style.borderColor = "#10b981";
            lbl.style.background = "rgba(16, 185, 129, 0.15)";
          } else if (userAns === optIdx && userAns !== q.correct) {
            lbl.style.borderColor = "#ef4444";
            lbl.style.background = "rgba(239, 68, 68, 0.15)";
          }
        });

        if (userAns === q.correct) {
          correctCount++;
        } else {
          if (idx === 0 || idx === 4) wrongConcepts.push("hubungan perbandingan V, I, dan R");
          if (idx === 1) wrongConcepts.push("rumus turunan Hukum Ohm");
          if (idx === 2 || idx === 3) wrongConcepts.push("perhitungan numerik dan konversi satuan");
        }
      });

      const percent = Math.round((correctCount / total) * 100);
      const scoreDisplay = document.getElementById("quiz-score-display");
      const msgDisplay = document.getElementById("quiz-feedback-msg");
      const resultCard = document.getElementById("quiz-result-card");

      if (scoreDisplay) scoreDisplay.textContent = `${correctCount} / ${total} benar (${percent}%)`;
      if (msgDisplay) {
        if (percent === 100) {
          msgDisplay.textContent = "Luar biasa! Pemahamanmu terhadap Hukum Ohm sudah sempurna.";
        } else {
          const uniqueMistakes = [...new Set(wrongConcepts)].join(", ");
          msgDisplay.innerHTML = `Bagus! Kamu menjawab benar ${correctCount} dari ${total} soal.<br><span style="color: #facc15;">Saran: Pelajari kembali ${uniqueMistakes} untuk hasil yang optimal.</span>`;
        }
      }

      if (resultCard) {
        resultCard.style.display = "block";
        resultCard.scrollIntoView({ behavior: 'smooth' });
      }

      updateCompletionChecklist();
    }

    function resetQuiz() {
      interactiveState.quizAnswers = {};
      interactiveState.quizSubmitted = false;

      QUIZ_QUESTIONS.forEach((q, idx) => {
        const card = document.getElementById(`quiz-card-${idx}`);
        if (!card) return;
        card.querySelectorAll(".quiz-option-label").forEach(lbl => {
          lbl.style.borderColor = "";
          lbl.style.background = "";
          lbl.classList.remove("selected");
        });
        card.querySelectorAll(".quiz-option-radio").forEach(rad => {
          rad.checked = false;
        });
      });

      const resultCard = document.getElementById("quiz-result-card");
      if (resultCard) resultCard.style.display = "none";
    }

    function toggleTheoryReveal(boxId, btn) {
      const box = document.getElementById(boxId);
      if (!box) return;
      box.classList.toggle("open");
      const isOpen = box.classList.contains("open");
      btn.innerHTML = isOpen ? `<span>Tutup Nilai Teori</span>` : `<span>💡 Lihat Nilai Teori</span>`;
    }

    /* ==========================================================================
       Completion Lock Logic (Section 23)
       ========================================================================== */

    function updateCompletionChecklist() {
      const isVirDone = interactiveState.exploredVir.v && interactiveState.exploredVir.i && interactiveState.exploredVir.r;
      const isExplorerDone = interactiveState.formulaExplorerUsed;
      const isPredictionDone = interactiveState.predictionAttempted;
      const isPracticeDone = interactiveState.calculationPracticeAttempted;
      const isQuizDone = interactiveState.quizAttempted;

      const setCheck = (id, done, text) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle("done", done);
        el.innerHTML = `<span>${done ? '✓' : '○'}</span> ${text}`;
      };

      setCheck("chk-vir", isVirDone, "1. Kenali V, I, dan R");
      setCheck("chk-explorer", isExplorerDone, "2. Eksplorasi Formula");
      setCheck("chk-prediction", isPredictionDone, "3. Uji Prediksi");
      setCheck("chk-practice", isPracticeDone, "4. Latihan Perhitungan");
      setCheck("chk-quiz", isQuizDone, "5. Kerjakan Kuis Akhir");

      const canComplete = isVirDone && isExplorerDone && isPredictionDone && isPracticeDone && isQuizDone;
      const finishBtn = document.getElementById("btn-finish-module");
      const helper = document.getElementById("completion-lock-helper");

      if (finishBtn) {
        finishBtn.disabled = !canComplete;
      }
      if (helper) {
        helper.innerHTML = canComplete 
          ? "🎉 <strong>Selamat!</strong> Seluruh tahapan pembelajaran telah selesai. Klik tombol di atas untuk menyimpan kelulusan modul."
          : "🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 5 untuk membuka tombol selesai.";
        helper.style.color = canComplete ? "#10b981" : "#94a3b8";
      }
    }

    function finishAndSaveModule(dbId) {
      updateModuleProgress(dbId, 'selesai');
      closeModuleModal();
    }

    
    /* ==========================================================================
       Interactive Learning Module Engine (Modul 03: Panduan Penggunaan Multimeter)
       REAL FLUXUS SIMULATOR MULTIMETER ASSET INTEGRATION & PEDAGOGICAL VISUALS
       ========================================================================== */

    let currentMeterStep = 1;
    let completedMeterSteps = new Set([1]);
    let currentDbMeterModuleId = 3;

    // Local, isolated learning state (Does NOT affect /simulasi or solver)
    const learningMeter = {
      // Hardware state
      poweredOn: false, // Starts OFF as per real Fluxus Multimeter!
      hold: false,
      mode: 'V_DC', // 'V_DC', 'A_DC', 'OHM'
      rangeMode: 'AUTO',
      reading: '0.00',
      unit: 'V',

      // Jack plug positions
      blackJack: 'COM',
      redJack: 'V_OHM', // 'V_OHM' or 'MA'

      // Step 1: Action-driven explored controls & Inspector state
      exploredControls: {
        power: false,
        hold: false,
        mode: false,
        range: false,
        com: false,
        voltageOhmJack: false,
        currentJack: false
      },
      activeInspector: 'POWER',
      inspectedItems: new Set(),

      // Step 2: Voltmeter
      voltPlacement: 'parallel', // 'parallel' or 'series'
      voltProbesReversed: false,
      voltAttempted: false,

      // Step 3: Ammeter
      ampPlacement: 'series', // 'series' or 'parallel'
      ampWarningAnswer: null,
      ampWarningAttempted: false,
      ampAttempted: false,

      // Step 4: Ohmmeter
      powerSourceOn: true, // Starts with source ON to demonstrate LIVE CIRCUIT rejection!
      ohmAttempted: false,

      // Step 5: Diagnostics
      diagnosticAnswers: { 1: null, 2: null, 3: null, 4: null },
      diagnosticChecked: { 1: false, 2: false, 3: false, 4: false },

      // Step 6: Quiz
      quizAnswers: {},
      quizAttempted: false,
      quizSubmitted: false
    };

    // Alias for backward compatibility
    const meterState = learningMeter;

    const METER_QUIZ_QUESTIONS = [
      {
        q: "Untuk mengukur beda potensial (tegangan) pada suatu resistor, voltmeter harus dipasang secara...",
        options: ["Paralel melintasi resistor", "Seri dengan memutus kawat", "Bebas di mana saja tanpa aturan"],
        correct: 0,
        explanation: "Voltmeter memiliki hambatan input sangat tinggi (~10 MΩ) sehingga wajib dipasang secara paralel melintasi dua titik yang diukur tanpa memutus kawat utama."
      },
      {
        q: "Untuk mengukur kuat arus listrik yang mengalir melalui suatu cabang, amperemeter harus dipasang secara...",
        options: ["Paralel dengan komponen", "Seri dengan memutus kawat rangkaian", "Terhubung langsung ke kedua kutub baterai"],
        correct: 1,
        explanation: "Amperemeter dirancang sebagai lintasan berhambatan sangat rendah (~1 mΩ shunt) sehingga harus disisipkan secara seri agar seluruh muatan mengalir melaluinya."
      },
      {
        q: "Mengapa pengukuran nilai resistansi (Ω) dengan ohmmeter wajib dilakukan saat rangkaian mati (sumber daya OFF)?",
        options: [
          "Karena ohmmeter kehabisan baterai internal jika sirkuit menyala",
          "Karena tegangan aktif membuat Fluxus menampilkan LIVE CIRCUIT dan menolak pengukuran demi melindungi sirkuit ukur",
          "Karena nilai hambatan resistor berubah drastis menjadi nol saat dialiri arus"
        ],
        correct: 1,
        explanation: "Ohmmeter mengalirkan arus uji internalnya sendiri. Pada rangkaian bertegangan aktif, Fluxus mendeteksi kondisi berenergi dan menolak pengukuran dengan error LIVE CIRCUIT."
      },
      {
        q: "Bagaimanakah rumus pembacaan nilai tegangan yang tampil pada layar multimeter digital?",
        options: ["V_display = V_merah - V_hitam", "V_display = V_hitam - V_merah", "V_display = V_merah + V_hitam"],
        correct: 0,
        explanation: "Multimeter membaca selisih potensial listrik antara probe merah dikurangi potensial probe hitam (titik referensi / COM)."
      },
      {
        q: "Jika probe merah ditempelkan pada titik berpotensial 12 V dan probe hitam ditempelkan pada titik 4 V, display multimeter akan menunjukkan...",
        options: ["-8.00 V", "+8.00 V", "+16.00 V"],
        correct: 1,
        explanation: "V_display = V_merah - V_hitam = 12 V - 4 V = +8.00 V."
      },
      {
        q: "Jika kedua probe pada kasus sebelumnya dibalik (merah di 4 V dan hitam di 12 V), display multimeter akan menunjukkan...",
        options: ["-8.00 V", "+8.00 V", "0.00 V"],
        correct: 0,
        explanation: "V_display = 4 V - 12 V = -8.00 V. Tanda minus pada LCD menunjukkan probe merah berada pada potensial lebih rendah daripada COM."
      },
      {
        q: "Jack soket input pada multimeter digital yang SELALU digunakan sebagai terminal referensi (ground/negatif) untuk seluruh pengukuran adalah...",
        options: ["10A", "V-Ω-mA", "COM (Common)"],
        correct: 2,
        explanation: "Terminal COM (Common) adalah ground referensi universal multimeter yang selalu dihubungkan dengan probe hitam pada setiap mode pengukuran."
      },
      {
        q: "Fungsi utama tombol HOLD pada multimeter digital adalah untuk...",
        options: ["Mematikan daya multimeter", "Membekukan nilai pembacaan terakhir pada layar LCD agar mudah dicatat", "Menaikkan resolusi pengukuran"],
        correct: 1,
        explanation: "Tombol HOLD berfungsi membekukan (freeze) angka pengukuran yang sedang aktif di layar LCD sehingga nilai tetap terbaca meskipun probe dilepas."
      }
    ];

    const METER_INSPECTOR_INFO = {
      'POWER': {
        title: 'Tombol POWER (⏻)',
        badge: 'Kontrol Daya',
        desc: 'Menghidupkan atau mematikan multimeter digital. Saat pertama kali digunakan di laboratorium atau simulator, multimeter selalu berada dalam kondisi <strong>POWER OFF</strong>. Tekan tombol ini sebelum memulai pengukuran.',
        specs: [
          { label: 'Status Saat Ini', key: 'powerStatus' },
          { label: 'Fungsi', val: 'Sakelar Utama Instrumen' }
        ],
        actionLabel: 'Tekan POWER',
        actionFn: 'toggleMeterPower()'
      },
      'HOLD': {
        title: 'Tombol HOLD (Data Hold)',
        badge: 'Fungsi Memori',
        desc: 'Membekukan (freeze) angka pembacaan terakhir di layar LCD agar mudah dicatat. <em>HOLD menahan angka pada layar, bukan memutus pengukuran di rangkaian.</em> Tekan kembali untuk melanjutkan pembacaan langsung.',
        specs: [
          { label: 'Status HOLD', key: 'holdStatus' },
          { label: 'Indikator LCD', val: 'Aksara "HOLD"' }
        ],
        actionLabel: 'Tekan HOLD',
        actionFn: 'toggleMeterHold()'
      },
      'MODE': {
        title: 'Tombol & Selektor MODE',
        badge: 'Pemilih Besaran',
        desc: 'Menentukan jenis besaran listrik yang diukur: <strong>V (Tegangan DC)</strong>, <strong>A (Kuat Arus DC)</strong>, atau <strong>Ω (Resistansi / Hambatan)</strong>. Mengubah mode akan memutar knob selektor dan memindahkan colokan probe merah ke jack yang sesuai.',
        specs: [
          { label: 'Mode Aktif', key: 'currentMode' },
          { label: 'Sudut Knob', key: 'knobAngle' }
        ],
        actionLabel: 'Ganti Mode (V ➔ A ➔ Ω)',
        actionFn: 'cycleMeterMode()'
      },
      'RANGE': {
        title: 'Tombol RANGE (Rentang Skala)',
        badge: 'Resolusi Ukur',
        desc: 'Mengatur rentang skala pengukuran antara otomatis (<strong>AUTO</strong>) atau manual (<strong>MANUAL</strong>). Mode AUTO secara cerdas memilih resolusi terbaik untuk besaran yang sedang dibaca.',
        specs: [
          { label: 'Status Range', key: 'rangeStatus' },
          { label: 'Standar Default', val: 'AUTO Range' }
        ],
        actionLabel: 'Ubah RANGE (Auto/Manual)',
        actionFn: 'toggleMeterRange()'
      },
      'COM': {
        title: 'Jack Input COM (Common)',
        badge: 'Terminal Negatif / Ref',
        desc: '<strong>Probe hitam SELALU menggunakan jack COM sebagai referensi pengukuran.</strong> Terminal ini adalah titik acuan potensial nol ($0\text{ V}$ relatif) untuk seluruh mode (Volt, Ampere, dan Ohm).',
        specs: [
          { label: 'Warna Probe', val: 'Hitam (Ground)' },
          { label: 'Aturan Lab', val: 'Selalu di COM' }
        ],
        actionLabel: 'Pilih Jack COM',
        actionFn: 'clickMeterJack("COM")'
      },
      'V-Ω-mA': {
        title: 'Jack Input V-Ω-mA',
        badge: 'Terminal Positif V & Ω',
        desc: 'Digunakan untuk menancapkan <strong>Probe MERAH</strong> saat mengukur <strong>Tegangan (V)</strong> dan <strong>Hambatan (Ω)</strong>. Jack ini terhubung ke rangkaian impedansi tinggi (~10 MΩ) saat mode V, dan sirkuit arus uji saat mode Ω.',
        specs: [
          { label: 'Warna Probe', val: 'Merah (+)' },
          { label: 'Mode Terhubung', val: 'Mode V⎓ dan Ω' }
        ],
        actionLabel: 'Pilih Jack V-Ω-mA',
        actionFn: 'clickMeterJack("V-Ω-mA")'
      },
      'mA/A': {
        title: 'Jack Input mA/A',
        badge: 'Terminal Arus Masuk',
        desc: 'Digunakan untuk menancapkan <strong>Probe MERAH</strong> saat mengukur <strong>Kuat Arus (A DC)</strong> pada latihan praktikum ini. Jack ini terhubung ke resistor shunt internal berimpedansi sangat rendah (~1 mΩ).',
        specs: [
          { label: 'Warna Probe', val: 'Merah (Arus Masuk)' },
          { label: 'Hambatan Shunt', val: 'R_shunt ≈ 1 mΩ' }
        ],
        actionLabel: 'Pilih Jack mA/A',
        actionFn: 'clickMeterJack("mA/A")'
      },
      '10A': {
        title: 'Jack Input 10A (High Current)',
        badge: 'Terminal Arus Tinggi',
        desc: 'Jack 10A dicadangkan untuk pengukuran arus besar khusus di masa depan dengan proteksi sekring terpisah. <em>Jack ini belum digunakan pada latihan praktikum dasar ini.</em>',
        specs: [
          { label: 'Status Latihan', val: 'Belum Digunakan' },
          { label: 'Proteksi', val: 'Sekring 10A Max' }
        ],
        actionLabel: 'Inspeksi Jack 10A',
        actionFn: 'clickMeterJack("10A")'
      }
    };

    /**
     * Renders the authentic Fluxus Multimeter visual markup (identical to simulator).
     */
    function renderFluxusMultimeterHTML(opts = {}) {
      const powerOn = opts.powerOn !== undefined ? opts.powerOn : learningMeter.poweredOn;
      const holdEnabled = opts.holdActive !== undefined ? opts.holdActive : learningMeter.hold;
      const mode = opts.mode || learningMeter.mode;
      const rangeMode = opts.range || learningMeter.rangeMode;
      const reading = opts.reading !== undefined ? opts.reading : (powerOn ? learningMeter.reading : 'OFF');
      const unit = opts.unit !== undefined ? opts.unit : (powerOn ? learningMeter.unit : '');
      const activeJackRed = opts.activeJackRed || (mode === 'A_DC' ? 'MA' : 'V_OHM');
      const activeJackBlack = 'COM';
      const showPlugs = opts.showPlugs !== undefined ? opts.showPlugs : true;
      const inspectedTarget = learningMeter.activeInspector;

      let modeBadge = "DC";
      if (mode === "OHM") modeBadge = "Ω";

      // Precise calibrated rotary dial angles from simulator components.js
      let dialAngle = -57;
      if (mode === "V_DC") dialAngle = -57;
      else if (mode === "OHM") dialAngle = 0;
      else if (mode === "A_DC") dialAngle = 57;

      // Authentic banana plug SVG with realistic 3D body, metallic collar & natural cable segment
      const plugBlackSVG = `
        <svg class="meter-plug-overlay plug-black" viewBox="-12 0 24 50">
          <defs>
            <linearGradient id="plug-grad-black-trainer" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#475569"/>
              <stop offset="35%" stop-color="#1e293b"/>
              <stop offset="100%" stop-color="#020617"/>
            </linearGradient>
            <linearGradient id="plug-metal-core-black" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#ffffff"/>
              <stop offset="45%" stop-color="#cbd5e1"/>
              <stop offset="70%" stop-color="#94a3b8"/>
              <stop offset="100%" stop-color="#475569"/>
            </linearGradient>
          </defs>
          <!-- Plug Shadow onto Casing Face -->
          <ellipse cx="0" cy="18" rx="7" ry="12" fill="rgba(0,0,0,0.5)" filter="blur(1.5px)"/>
          
          <!-- Natural Short Test-Lead Cable Segment (Behind barrel, emerges from boot tip) -->
          <path d="M 0 28.5 C 0 35, -2.5 41, -6 49" stroke="rgba(0,0,0,0.5)" stroke-width="4.2" fill="none" stroke-linecap="round"/>
          <path d="M 0 28.5 C 0 35, -2.5 41, -6 49" stroke="#0f172a" stroke-width="3.4" fill="none" stroke-linecap="round"/>
          <path d="M 0 28.5 C 0 35, -2.5 41, -6 49" stroke="#334155" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.6"/>

          <!-- Metallic Socket Pin Rim & Hole Insert (Covers socket from front) -->
          <circle cx="0" cy="8" r="5.2" fill="#475569" stroke="#1e293b" stroke-width="1"/>
          <circle cx="0" cy="8" r="3" fill="#020617"/>
          <circle cx="0" cy="8" r="4.2" fill="none" stroke="url(#plug-metal-core-black)" stroke-width="0.8" opacity="0.8"/>

          <!-- Molded Banana Barrel Body (y: 8 to 24, width 11) -->
          <path d="M -5.5 8 L 5.5 8 L 4.5 24 L -4.5 24 Z" fill="url(#plug-grad-black-trainer)" stroke="#020617" stroke-width="0.9"/>
          
          <!-- Tactile Grips -->
          <line x1="-5" y1="12.5" x2="5" y2="12.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          <line x1="-4.6" y1="16.5" x2="4.6" y2="16.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          <line x1="-4.2" y1="20.5" x2="4.2" y2="20.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          
          <!-- Specular Highlight Sheen -->
          <line x1="-2.2" y1="10" x2="-2.2" y2="22.5" stroke="#ffffff" stroke-width="0.8" opacity="0.5" stroke-linecap="round"/>
          
          <!-- Molded Strain Relief Collar Boot -->
          <rect x="-3.5" y="23.5" width="7" height="4" rx="1.2" fill="#0f172a" stroke="#334155" stroke-width="0.7"/>
          <rect x="-2.5" y="27" width="5" height="2.5" rx="0.8" fill="#1e293b" stroke="#0f172a" stroke-width="0.6"/>
        </svg>
      `;

      const plugRedSVG = `
        <svg class="meter-plug-overlay plug-red" viewBox="-12 0 24 50">
          <defs>
            <linearGradient id="plug-grad-red-trainer" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#f87171"/>
              <stop offset="35%" stop-color="#dc2626"/>
              <stop offset="100%" stop-color="#991b1b"/>
            </linearGradient>
            <linearGradient id="plug-metal-core-red" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#ffffff"/>
              <stop offset="45%" stop-color="#cbd5e1"/>
              <stop offset="70%" stop-color="#94a3b8"/>
              <stop offset="100%" stop-color="#475569"/>
            </linearGradient>
          </defs>
          <!-- Plug Shadow onto Casing Face -->
          <ellipse cx="0" cy="18" rx="7" ry="12" fill="rgba(0,0,0,0.5)" filter="blur(1.5px)"/>
          
          <!-- Natural Short Test-Lead Cable Segment (Behind barrel, emerges from boot tip) -->
          <path d="M 0 28.5 C 0 35, 2.5 41, 6 49" stroke="rgba(0,0,0,0.5)" stroke-width="4.2" fill="none" stroke-linecap="round"/>
          <path d="M 0 28.5 C 0 35, 2.5 41, 6 49" stroke="#dc2626" stroke-width="3.4" fill="none" stroke-linecap="round"/>
          <path d="M 0 28.5 C 0 35, 2.5 41, 6 49" stroke="#f87171" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.6"/>

          <!-- Metallic Socket Pin Rim & Hole Insert (Covers socket from front) -->
          <circle cx="0" cy="8" r="5.2" fill="#ef4444" stroke="#991b1b" stroke-width="1"/>
          <circle cx="0" cy="8" r="3" fill="#7f1d1d"/>
          <circle cx="0" cy="8" r="4.2" fill="none" stroke="url(#plug-metal-core-red)" stroke-width="0.8" opacity="0.8"/>

          <!-- Molded Banana Barrel Body (y: 8 to 24, width 11) -->
          <path d="M -5.5 8 L 5.5 8 L 4.5 24 L -4.5 24 Z" fill="url(#plug-grad-red-trainer)" stroke="#7f1d1d" stroke-width="0.9"/>
          
          <!-- Tactile Grips -->
          <line x1="-5" y1="12.5" x2="5" y2="12.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          <line x1="-4.6" y1="16.5" x2="4.6" y2="16.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          <line x1="-4.2" y1="20.5" x2="4.2" y2="20.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
          
          <!-- Specular Highlight Sheen -->
          <line x1="-2.2" y1="10" x2="-2.2" y2="22.5" stroke="#ffffff" stroke-width="0.8" opacity="0.5" stroke-linecap="round"/>
          
          <!-- Molded Strain Relief Collar Boot -->
          <rect x="-3.5" y="23.5" width="7" height="4" rx="1.2" fill="#0f172a" stroke="#334155" stroke-width="0.7"/>
          <rect x="-2.5" y="27" width="5" height="2.5" rx="0.8" fill="#1e293b" stroke="#0f172a" stroke-width="0.6"/>
        </svg>
      `;

      return `
        <div class="multimeter-visual fluke-179-style dark-edition ${powerOn ? '' : 'power-off'}">
          <div class="meter-casing-vertical">
            <!-- Branding Header -->
            <div class="meter-header">
              <span class="meter-brand-badge">FLUXUS</span>
              <span class="meter-model-text">TRUE RMS</span>
            </div>

            <!-- Authentic STN Light LCD Screen -->
            <div class="meter-lcd-bezel">
              <div class="meter-lcd-screen-light">
                <div class="meter-lcd-top-bar multimeter-lcd-status">
                  <span class="status-range lcd-badge-auto">${rangeMode}</span>
                  <span class="status-mode lcd-badge-mode">${modeBadge}</span>
                  <span class="status-hold lcd-badge-hold" style="${holdEnabled ? 'visibility: visible;' : 'visibility: hidden;'}">HOLD</span>
                  <span class="status-rms lcd-badge-rms">RMS</span>
                </div>
                <div class="meter-lcd-main-light reading-row">
                  <span class="meter-comp-val">${reading}</span>
                  <span class="meter-comp-unit">${powerOn ? unit : ''}</span>
                </div>
                <div class="meter-lcd-bar-graph">
                  <div class="lcd-bar-scale">
                    <span>0</span>
                    <span>30</span>
                    <span>60</span>
                  </div>
                  <div class="lcd-bar-track">
                    <div class="lcd-bar-fill" style="width: ${powerOn && reading !== 'OFF' && !reading.includes('ERR') ? '48%' : '0%'};"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Authentic Function Controls -->
            <div class="meter-function-row">
              <div class="meter-power-btn ${powerOn ? '' : 'off'} ${inspectedTarget === 'POWER' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('POWER')" 
                   title="Toggle Multimeter Power">⏻</div>
              <div class="meter-chip-btn btn-hold ${holdEnabled ? 'active' : ''} ${inspectedTarget === 'HOLD' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('HOLD')" 
                   title="Data Hold">HOLD</div>
              <div class="meter-chip-btn btn-mode ${inspectedTarget === 'MODE' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('MODE')" 
                   title="Ganti Mode Pengukuran">MODE</div>
              <div class="meter-chip-btn btn-range ${rangeMode === 'MANUAL' ? 'active' : ''} ${inspectedTarget === 'RANGE' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('RANGE')" 
                   title="Cycle Range (Auto/Manual)">RANGE</div>
            </div>

            <!-- Calibrated Rotary Dial Section -->
            <div class="meter-dial-section">
              <div class="meter-dial-scale">
                <span class="meter-dial-label label-v ${mode === 'V_DC' ? 'active' : ''}" 
                      onclick="selectSpecificMode('V_DC')" title="Tegangan DC (V)">V⎓</span>
                <span class="meter-dial-label label-vac" title="Tegangan AC">V~</span>
                <span class="meter-dial-label label-ohm ${mode === 'OHM' ? 'active' : ''}" 
                      onclick="selectSpecificMode('OHM')" title="Resistansi (Ω)">Ω</span>
                <span class="meter-dial-label label-a ${mode === 'A_DC' ? 'active' : ''}" 
                      onclick="selectSpecificMode('A_DC')" title="Arus DC (A)">A⎓</span>
                <span class="meter-dial-label label-aac" title="Arus AC">A~</span>
              </div>
              <div class="meter-rotary-knob ${inspectedTarget === 'MODE' ? 'inspected-target' : ''}" 
                   style="transform: rotate(${dialAngle}deg);" 
                   onclick="handleMeterElementClick('MODE')" 
                   title="Putar Knob Mode">
                <div class="knob-face">
                  <div class="knob-bar-handle">
                    <div class="knob-pointer-arrow"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 4-Port Banana Jack Panel with Exact Approved Order: [10A] [COM] [V-Ω-mA] [mA/A] -->
            <div class="meter-jacks-panel jack-row">
              <!-- Jack 1: 10A -->
              <div class="multimeter-jack jack-10a meter-jack-housing ${inspectedTarget === '10A' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('10A')" title="Jack 10A (High Current)">
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">10A</span>
              </div>

              <!-- Jack 2: COM (Always Black) -->
              <div class="multimeter-jack jack-com meter-jack-housing ${inspectedTarget === 'COM' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('COM')" title="Jack COM (Ground / Reference)">
                ${showPlugs && activeJackBlack === 'COM' ? plugBlackSVG : ''}
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">COM</span>
              </div>

              <!-- Jack 3: V-Ω-mA (Red for V and OHM) -->
              <div class="multimeter-jack jack-v-ohm-ma jack-vwma meter-jack-housing ${inspectedTarget === 'V-Ω-mA' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('V-Ω-mA')" title="Jack V-Ω-mA (Tegangan & Hambatan)">
                ${showPlugs && activeJackRed === 'V_OHM' ? plugRedSVG : ''}
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">V-Ω-mA</span>
              </div>

              <!-- Jack 4: mA/A (Red for Current) -->
              <div class="multimeter-jack jack-ma meter-jack-housing ${inspectedTarget === 'mA/A' ? 'inspected-target' : ''}" 
                   onclick="handleMeterElementClick('mA/A')" title="Jack mA/A (Kuat Arus)">
                ${showPlugs && activeJackRed === 'MA' ? plugRedSVG : ''}
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">mA/A</span>
              </div>
            </div>

          </div>
        </div>
      `;
    }

    function openMultimeterModule(dbId, moduleNum = 3) {
      currentDbMeterModuleId = dbId;
      currentMeterStep = 1;
      completedMeterSteps = new Set(); // Step 1 completes only after all 7 controls are explored

      // Reset Step 1 exploration state for fresh learning
      learningMeter.exploredControls = {
        power: false,
        hold: false,
        mode: false,
        range: false,
        com: false,
        voltageOhmJack: false,
        currentJack: false
      };
      learningMeter.inspectedItems = new Set();
      learningMeter.poweredOn = false;
      learningMeter.hold = false;
      learningMeter.mode = 'V_DC';
      learningMeter.rangeMode = 'AUTO';
      learningMeter.reading = '0.00';
      learningMeter.unit = 'V';
      learningMeter.activeInspector = 'POWER';

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="sp-fullscreen-backdrop" onclick="closeModuleModal()">
          <div class="sp-fullscreen-container" onclick="event.stopPropagation()">
            
            <!-- 1. Full-Screen Module Header Matching Modules 02 & 04 -->
            <header class="sp-fullscreen-header">
              <div class="sp-header-left">
                <span class="interactive-module-badge">⚡ Modul 03 • Alat Ukur Digital</span>
                <h2 class="sp-header-title">Panduan Penggunaan Multimeter Digital</h2>
              </div>

              <div class="sp-header-center">
                <div class="sp-progress-wrapper">
                  <div class="sp-progress-bar">
                    <div class="sp-progress-fill" id="meter-progress-fill" style="width: 16.7%;"></div>
                  </div>
                  <span class="sp-progress-text" id="meter-progress-text">Langkah 1 dari 6 (17%)</span>
                </div>
              </div>

              <div class="sp-header-right">
                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" title="Buka Instrumen di Simulator">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                  <span>Coba di Simulator</span>
                </a>
                <button class="btn-close-modal" onclick="closeModuleModal()" aria-label="Tutup Modul">✕</button>
              </div>
            </header>

            <!-- 2. Step Navigation Tabs Bar (Consistent Fluxus Design System) -->
            <nav class="sp-tabs-bar" role="tablist">
              <button class="sp-tab-item active" id="meter-tab-btn-1" onclick="goToMeterStep(1)">
                <span class="tab-badge">1</span>
                <span>Kenali Multimeter</span>
              </button>
              <button class="sp-tab-item" id="meter-tab-btn-2" onclick="goToMeterStep(2)">
                <span class="tab-badge">2</span>
                <span>Mode Tegangan (V)</span>
              </button>
              <button class="sp-tab-item" id="meter-tab-btn-3" onclick="goToMeterStep(3)">
                <span class="tab-badge">3</span>
                <span>Mode Arus (A)</span>
              </button>
              <button class="sp-tab-item" id="meter-tab-btn-4" onclick="goToMeterStep(4)">
                <span class="tab-badge">4</span>
                <span>Mode Hambatan (Ω)</span>
              </button>
              <button class="sp-tab-item" id="meter-tab-btn-5" onclick="goToMeterStep(5)">
                <span class="tab-badge">5</span>
                <span>Kesalahan & Diagnostik</span>
              </button>
              <button class="sp-tab-item" id="meter-tab-btn-6" onclick="goToMeterStep(6)">
                <span class="tab-badge">6</span>
                <span>Quiz & Evaluasi</span>
              </button>
            </nav>

            <!-- 3. Dynamic Step Body -->
            <main class="sp-fullscreen-body" id="meter-fullscreen-body">
              <!-- Dynamically Rendered Content -->
            </main>

            <!-- 4. Sticky Footer with Polished Navigation Buttons -->
            <footer class="sp-fullscreen-footer">
              <button class="btn-step-nav btn-step-prev" id="btn-meter-prev" onclick="changeMeterStep(-1)" disabled>
                <span>← Sebelumnya</span>
              </button>

              <div class="footer-step-counter" id="meter-step-counter-footer">
                Langkah 1 dari 6
              </div>

              <button class="btn-step-nav btn-step-next" id="btn-meter-next" onclick="changeMeterStep(1)">
                <span>Langkah Selanjutnya →</span>
              </button>
            </footer>

          </div>
        </div>
      `;

      renderCurrentMeterStep(false);
      updateMeterProgress();
    }

    function goToMeterStep(stepNum) {
      if (stepNum < 1 || stepNum > 6) return;
      currentMeterStep = stepNum;
      completedMeterSteps.add(stepNum);

      // In step 2, 3, 4 ensure power is ON for educational clarity
      if (stepNum >= 2 && !learningMeter.poweredOn) {
        learningMeter.poweredOn = true;
      }
      if (stepNum === 2) {
        learningMeter.mode = 'V_DC';
        learningMeter.redJack = 'V_OHM';
        updateVoltReading();
      } else if (stepNum === 3) {
        learningMeter.mode = 'A_DC';
        learningMeter.redJack = 'MA';
        learningMeter.reading = learningMeter.ampPlacement === 'series' ? '2.40' : '0.00';
        learningMeter.unit = 'A';
      } else if (stepNum === 4) {
        learningMeter.mode = 'OHM';
        learningMeter.redJack = 'V_OHM';
        learningMeter.reading = learningMeter.powerSourceOn ? 'ERR LIVE' : '500.0';
        learningMeter.unit = learningMeter.powerSourceOn ? '' : 'Ω';
      }

      renderCurrentMeterStep(false); // Only step change resets scroll to top
      updateMeterProgress();
    }

    function changeMeterStep(delta) {
      const target = currentMeterStep + delta;
      if (target >= 1 && target <= 6) {
        goToMeterStep(target);
      }
    }

    function updateMeterProgress() {
      const fill = document.getElementById("meter-progress-fill");
      const text = document.getElementById("meter-progress-text");
      const counter = document.getElementById("meter-step-counter-footer");
      const btnPrev = document.getElementById("btn-meter-prev");
      const btnNext = document.getElementById("btn-meter-next");

      for (let i = 1; i <= 6; i++) {
        const tab = document.getElementById(`meter-tab-btn-${i}`);
        if (tab) {
          tab.classList.toggle("active", i === currentMeterStep);
          tab.classList.toggle("completed", completedMeterSteps.has(i) && i !== currentMeterStep);
        }
      }

      const pct = Math.round((completedMeterSteps.size / 6) * 100);
      if (fill) fill.style.width = `${pct}%`;
      if (text) text.innerText = `Langkah ${currentMeterStep} dari 6 (${pct}%)`;
      if (counter) counter.innerText = `Langkah ${currentMeterStep} dari 6`;

      if (btnPrev) btnPrev.disabled = currentMeterStep === 1;
      if (btnNext) {
        if (currentMeterStep === 6) {
          btnNext.style.display = "none";
        } else {
          btnNext.style.display = "inline-flex";
        }
      }
    }

    /**
     * Renders the current step content.
     * @param {boolean} preserveScroll - If true (default for local interactions), preserves the exact viewport scroll position.
     */
    function renderCurrentMeterStep(preserveScroll = true) {
      const body = document.getElementById("meter-fullscreen-body");
      if (!body) return;

      const prevBodyScroll = body.scrollTop;
      const prevWindowScroll = window.scrollY || document.documentElement.scrollTop;

      if (currentMeterStep === 1) renderMeterStep1(body);
      else if (currentMeterStep === 2) renderMeterStep2(body);
      else if (currentMeterStep === 3) renderMeterStep3(body);
      else if (currentMeterStep === 4) renderMeterStep4(body);
      else if (currentMeterStep === 5) renderMeterStep5(body);
      else if (currentMeterStep === 6) renderMeterStep6(body);

      if (preserveScroll) {
        body.scrollTop = prevBodyScroll;
        if (window.scrollY !== prevWindowScroll) {
          window.scrollTo(0, prevWindowScroll);
        }
      } else {
        body.scrollTop = 0;
      }
    }

    /* --------------------------------------------------------------------------
       STEP 1: KENALI MULTIMETER (REAL FLUXUS ASSET TRAINER & CONTROL INSPECTOR)
       -------------------------------------------------------------------------- */
    function renderMeterStep1(container) {
      const pOn = learningMeter.poweredOn;
      const inspectedKey = learningMeter.activeInspector || 'POWER';
      const info = METER_INSPECTOR_INFO[inspectedKey] || METER_INSPECTOR_INFO['POWER'];

      // Dynamic spec values based on current state
      const specRows = info.specs.map(s => {
        let val = s.val;
        if (s.key === 'powerStatus') val = pOn ? 'ON (Menyala)' : 'OFF (Mati)';
        else if (s.key === 'holdStatus') val = learningMeter.hold ? 'Aktif (Membekukan LCD)' : 'Nonaktif';
        else if (s.key === 'currentMode') {
          val = learningMeter.mode === 'V_DC' ? 'V⎓ (Tegangan DC)' : (learningMeter.mode === 'A_DC' ? 'A⎓ (Arus DC)' : 'Ω (Resistansi)');
        }
        else if (s.key === 'knobAngle') {
          val = learningMeter.mode === 'V_DC' ? '-57°' : (learningMeter.mode === 'A_DC' ? '+57°' : '0°');
        }
        else if (s.key === 'rangeStatus') val = learningMeter.rangeMode;

        return `
          <div class="meter-inspector-item">
            <span class="meter-inspector-label">${s.label}</span>
            <span class="meter-inspector-val">${val}</span>
          </div>
        `;
      }).join('');

      // Checklist of essential controls (7 items)
      const checklistItems = [
        { id: 'POWER', label: '1. Sakelar POWER' },
        { id: 'HOLD', label: '2. Tombol HOLD' },
        { id: 'MODE', label: '3. Selektor MODE' },
        { id: 'RANGE', label: '4. Tombol RANGE' },
        { id: 'COM', label: '5. Jack COM (Hitam)' },
        { id: 'V-Ω-mA', label: '6. Jack V-Ω-mA' },
        { id: 'mA/A', label: '7. Jack mA/A' }
      ];

      const exploredCount = getExploredControlsCount();
      const allExplored = exploredCount >= 7;

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 1: Kenali Multimeter Digital Fluxus (DMM)</h3>
            <p class="sp-step-desc">
              Instrumen ukur di bawah adalah <strong>Multimeter Digital Fluxus</strong> persis seperti yang digunakan di laboratorium virtual. 
              Multimeter baru selalu berada dalam kondisi <strong>POWER OFF</strong>. Klik tombol, knob putar, dan jack colokan untuk mempelajari fungsinya.
            </p>
          </div>

          <div class="sp-workbench-layout" style="grid-template-columns: 320px 1fr; gap: 20px; align-items: start;">
            <!-- Left: Authentic Fluxus Multimeter Trainer -->
            <div class="meter-trainer-wrapper">
              <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">
                Panel Depan Multimeter Digital
              </span>

              <div class="meter-trainer-mount">
                ${renderFluxusMultimeterHTML({
                  reading: pOn ? (learningMeter.hold ? '12.00' : '0.00') : 'OFF',
                  unit: pOn ? (learningMeter.mode === 'OHM' ? 'Ω' : (learningMeter.mode === 'A_DC' ? 'A' : 'V')) : ''
                })}
              </div>

              <div style="margin-top: 14px; text-align: center; font-size: 0.78rem; color: #64748b;">
                ${!pOn 
                  ? '⚠️ <strong style="color: #ef4444;">Status: POWER OFF</strong> — Klik tombol merah ⏻ POWER untuk menyalakan.' 
                  : '✅ <strong style="color: #10b981;">Status: POWER ON</strong> — Coba klik tombol HOLD, MODE, RANGE, dan Jack.'
                }
              </div>
            </div>

            <!-- Right: Interactive Control Inspector & Checklist -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <!-- Inspector Card -->
              <div class="meter-inspector-panel">
                <div class="meter-inspector-header">
                  <div class="meter-inspector-title-wrap">
                    <span class="meter-inspector-badge">${info.badge}</span>
                    <h4 class="meter-inspector-title">${info.title}</h4>
                  </div>
                  <span class="meter-inspector-status ${pOn ? 'status-active' : ''}">
                    ${pOn ? 'Daya Aktif' : 'Daya Standby'}
                  </span>
                </div>

                <div class="meter-inspector-body">
                  ${info.desc}
                </div>

                <div class="meter-inspector-grid">
                  ${specRows}
                </div>

                <div style="display: flex; gap: 10px; align-items: center; justify-content: flex-end; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                  <button type="button" class="btn-step-nav btn-step-next" style="padding: 6px 14px; font-size: 0.8rem;" onclick="${info.actionFn}">
                    ${info.actionLabel}
                  </button>
                </div>
              </div>

              <!-- Checklist Exploration Tracker -->
              <div class="meter-checklist-box">
                <div class="meter-checklist-title">
                  <span>Daftar Kendali yang Harus Dipelajari</span>
                  <span style="color: ${allExplored ? '#10b981' : '#0284c7'}; font-weight: 800;">
                    ${exploredCount} / 7 Terjelajahi
                  </span>
                </div>

                <div class="meter-checklist-grid">
                  ${checklistItems.map(item => {
                    const isDone = isMeterControlExplored(item.id);
                    return `
                      <div class="meter-check-item ${isDone ? 'done' : 'unexplored'}" 
                           onclick="guideToMeterControl('${item.id}')"
                           title="${isDone ? 'Sudah dipelajari' : 'Klik untuk melihat panduan posisi kendali ini di multimeter'}">
                        <span class="meter-check-icon">${isDone ? '✓' : '○'}</span>
                        <span class="meter-check-label">${item.label}</span>
                      </div>
                    `;
                  }).join('')}
                </div>

                ${allExplored ? `
                  <div style="margin-top: 10px; padding: 8px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; font-size: 0.8rem; color: #065f46; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                    <span>🎉 Seluruh kendali inti telah dipelajari!</span>
                    <button type="button" class="btn-step-nav btn-step-next" style="padding: 4px 10px; font-size: 0.75rem;" onclick="goToMeterStep(2)">
                      Lanjut ke Mode Tegangan →
                    </button>
                  </div>
                ` : ''}
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function markMeterAction(ctrlKey) {
      if (!learningMeter.exploredControls) {
        learningMeter.exploredControls = {
          power: false, hold: false, mode: false, range: false, com: false, voltageOhmJack: false, currentJack: false
        };
      }
      if (ctrlKey === 'POWER') learningMeter.exploredControls.power = true;
      else if (ctrlKey === 'HOLD') learningMeter.exploredControls.hold = true;
      else if (ctrlKey === 'MODE') learningMeter.exploredControls.mode = true;
      else if (ctrlKey === 'RANGE') learningMeter.exploredControls.range = true;
      else if (ctrlKey === 'COM') learningMeter.exploredControls.com = true;
      else if (ctrlKey === 'V-Ω-mA') learningMeter.exploredControls.voltageOhmJack = true;
      else if (ctrlKey === 'mA/A') learningMeter.exploredControls.currentJack = true;

      // Keep inspectedItems Set synchronized for backward compatibility
      if (['POWER', 'HOLD', 'MODE', 'RANGE', 'COM', 'V-Ω-mA', 'mA/A'].includes(ctrlKey)) {
        learningMeter.inspectedItems.add(ctrlKey);
      }

      if (getExploredControlsCount() >= 7) {
        completedMeterSteps.add(1);
      }
    }

    function isMeterControlExplored(ctrlKey) {
      if (!learningMeter.exploredControls) return false;
      if (ctrlKey === 'POWER') return !!learningMeter.exploredControls.power;
      if (ctrlKey === 'HOLD') return !!learningMeter.exploredControls.hold;
      if (ctrlKey === 'MODE') return !!learningMeter.exploredControls.mode;
      if (ctrlKey === 'RANGE') return !!learningMeter.exploredControls.range;
      if (ctrlKey === 'COM') return !!learningMeter.exploredControls.com;
      if (ctrlKey === 'V-Ω-mA') return !!learningMeter.exploredControls.voltageOhmJack;
      if (ctrlKey === 'mA/A') return !!learningMeter.exploredControls.currentJack;
      return false;
    }

    function getExploredControlsCount() {
      let count = 0;
      if (learningMeter.exploredControls?.power) count++;
      if (learningMeter.exploredControls?.hold) count++;
      if (learningMeter.exploredControls?.mode) count++;
      if (learningMeter.exploredControls?.range) count++;
      if (learningMeter.exploredControls?.com) count++;
      if (learningMeter.exploredControls?.voltageOhmJack) count++;
      if (learningMeter.exploredControls?.currentJack) count++;
      return count;
    }

    function guideToMeterControl(ctrlKey) {
      // Guidance only: highlight the control and update inspector view
      // DOES NOT MARK CONTROL AS EXPLORED!
      learningMeter.activeInspector = ctrlKey;
      renderCurrentMeterStep(true);
    }

    function handleMeterElementClick(ctrlKey) {
      if (ctrlKey === 'POWER') {
        toggleMeterPower();
      } else if (ctrlKey === 'HOLD') {
        toggleMeterHold();
      } else if (ctrlKey === 'MODE') {
        cycleMeterMode();
      } else if (ctrlKey === 'RANGE') {
        toggleMeterRange();
      } else if (['COM', 'V-Ω-mA', 'mA/A', '10A'].includes(ctrlKey)) {
        clickMeterJack(ctrlKey);
      }
    }

    function clickMeterJack(jackName) {
      if (jackName !== '10A') {
        markMeterAction(jackName);
      }
      learningMeter.activeInspector = jackName;
      renderCurrentMeterStep(true);
    }

    function inspectMeterControl(ctrlKey) {
      learningMeter.activeInspector = ctrlKey;
      renderCurrentMeterStep(true);
    }

    function toggleMeterPower() {
      learningMeter.poweredOn = !learningMeter.poweredOn;
      markMeterAction('POWER');
      learningMeter.activeInspector = 'POWER';
      if (!learningMeter.poweredOn) {
        learningMeter.hold = false;
      }
      renderCurrentMeterStep(true);
    }

    function toggleMeterHold() {
      learningMeter.hold = !learningMeter.hold;
      markMeterAction('HOLD');
      learningMeter.activeInspector = 'HOLD';
      renderCurrentMeterStep(true);
    }

    function toggleMeterRange() {
      learningMeter.rangeMode = learningMeter.rangeMode === 'AUTO' ? 'MANUAL' : 'AUTO';
      markMeterAction('RANGE');
      learningMeter.activeInspector = 'RANGE';
      renderCurrentMeterStep(true);
    }

    function cycleMeterMode() {
      markMeterAction('MODE');
      learningMeter.activeInspector = 'MODE';
      if (learningMeter.mode === 'V_DC') {
        selectSpecificMode('A_DC');
      } else if (learningMeter.mode === 'A_DC') {
        selectSpecificMode('OHM');
      } else {
        selectSpecificMode('V_DC');
      }
    }

    function selectSpecificMode(mode) {
      learningMeter.mode = mode;
      markMeterAction('MODE');
      learningMeter.activeInspector = 'MODE';
      if (mode === 'A_DC') {
        learningMeter.redJack = 'MA';
        learningMeter.unit = 'A';
      } else {
        learningMeter.redJack = 'V_OHM';
        learningMeter.unit = mode === 'OHM' ? 'Ω' : 'V';
      }
      renderCurrentMeterStep(true);
    }

    /* --------------------------------------------------------------------------
       STEP 2: MODE TEGANGAN (V DC) - VOLTMETER = PARALEL (10 MΩ INPUT)
       EXPLICIT SERIES VS PARALLEL TOPOLOGY VISUALIZATION
       -------------------------------------------------------------------------- */
    function updateVoltReading() {
      const isParallel = learningMeter.voltPlacement === 'parallel';
      const isReversed = learningMeter.voltProbesReversed;
      if (isParallel) {
        learningMeter.reading = isReversed ? '-8.00' : '+8.00';
      } else {
        // In series cut, meter reads open circuit drop across the 10 MΩ break
        learningMeter.reading = isReversed ? '-12.00' : '+12.00';
      }
      learningMeter.unit = 'V';
    }

    function renderMeterStep2(container) {
      const isParallel = learningMeter.voltPlacement === 'parallel';
      const isReversed = learningMeter.voltProbesReversed;
      updateVoltReading();

      let displayReading = learningMeter.reading;
      let mathFormula = '';
      if (isParallel) {
        mathFormula = isReversed 
          ? 'V_{meter} = V_{merah} - V_{hitam} = 4 V - 12 V = -8.00 V (Probe Dibalik)'
          : 'V_{meter} = V_{merah} - V_{hitam} = 12 V - 4 V = +8.00 V';
      } else {
        mathFormula = isReversed
          ? 'V_{meter} = V_{merah} - V_{hitam} = 0 V - 12 V = -12.00 V (Probe Dibalik)'
          : 'V_{meter} = V_{merah} - V_{hitam} = 12 V - 0 V = +12.00 V';
      }

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 2: Mode Tegangan (V DC) — Voltmeter Harus Paralel</h3>
            <p class="sp-step-desc">
              Tegangan adalah beda potensial antara dua titik. <strong>Voltmeter memiliki hambatan input sangat tinggi (~10 MΩ) 
              sehingga arus yang ditarik sangat kecil dibandingkan jalur utama</strong>. Voltmeter wajib dipasang secara <strong>PARALEL</strong> melintasi komponen tanpa memutus kawat utama.
            </p>
          </div>

          <div class="sp-workbench-layout" style="grid-template-columns: 260px 1fr; gap: 20px; align-items: start;">
            <!-- Left: Real Multimeter Asset in V Mode -->
            <div class="meter-trainer-wrapper">
              <span style="font-size: 0.72rem; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                Mode V⎓ (Tegangan DC)
              </span>

              <div class="meter-trainer-mount" style="transform: scale(0.95); margin: -8px auto;">
                ${renderFluxusMultimeterHTML({
                  powerOn: true,
                  mode: 'V_DC',
                  reading: displayReading,
                  unit: 'V',
                  activeJackRed: 'V_OHM',
                  activeJackBlack: 'COM'
                })}
              </div>

              <!-- Terminal Jack Indicator -->
              <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 6px 10px; margin-top: 10px; font-size: 0.75rem; text-align: center; color: #0369a1;">
                Jack Terpasang: <strong>COM</strong> (Hitam) & <strong>V-Ω-mA</strong> (Merah)
              </div>
            </div>

            <!-- Right: Interactive Circuit Schematic & Explicit Series/Parallel Topology -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div class="sp-card" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                  <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">
                    ${isParallel ? 'Topologi Paralel (Benar)' : 'Topologi Seri (Salah — Kawat Diputus)'}
                  </span>
                  <span style="font-size: 0.72rem; padding: 2px 10px; border-radius: 9999px; font-weight: 700; background: ${isParallel ? '#dcfce7' : '#fee2e2'}; color: ${isParallel ? '#15803d' : '#b91c1c'};">
                    ${isParallel ? '✓ PARALEL — BENAR (Jalur utama utuh)' : '✕ SERI — SALAH (Jalur utama diputus)'}
                  </span>
                </div>

                <!-- Schematic SVG -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                  <svg width="100%" height="230" viewBox="0 0 460 230" style="display: block; margin: 0 auto; max-width: 460px;">
                    <!-- 12V Battery on Left -->
                    <rect x="35" y="75" width="30" height="50" fill="#f8fafc" stroke="#334155" stroke-width="1.5" rx="3"></rect>
                    <line x1="42" y1="90" x2="58" y2="90" stroke="#ef4444" stroke-width="3"></line>
                    <line x1="46" y1="108" x2="54" y2="108" stroke="#0284c7" stroke-width="2"></line>
                    <text x="25" y="104" font-size="11" font-weight="700" fill="#0f172a" text-anchor="end">12V DC</text>

                    <!-- Battery positive lead up to top rail -->
                    <line x1="50" y1="75" x2="50" y2="45" stroke="#334155" stroke-width="2.5"></line>
                    
                    <!-- Bottom return conductor (Always continuous) -->
                    <line x1="50" y1="125" x2="50" y2="185" stroke="#334155" stroke-width="2.5"></line>
                    <line x1="50" y1="185" x2="410" y2="185" stroke="#334155" stroke-width="2.5"></line>
                    <line x1="410" y1="185" x2="410" y2="45" stroke="#334155" stroke-width="2.5"></line>

                    <!-- Top rail: Battery to R1 -->
                    <line x1="50" y1="45" x2="110" y2="45" stroke="#334155" stroke-width="2.5"></line>

                    <!-- Resistor R1 (4V drop) -->
                    <rect x="110" y="37" width="54" height="16" fill="#fef3c7" stroke="#d97706" stroke-width="1.5" rx="2"></rect>
                    <text x="137" y="49" font-size="9.5" font-weight="700" fill="#92400e" text-anchor="middle">R1 (4V)</text>

                    <!-- Conductor between R1 and R2 -->
                    <line x1="164" y1="45" x2="220" y2="45" stroke="#334155" stroke-width="2.5"></line>

                    <!-- Resistor R2 (8V drop) -->
                    <rect x="220" y="37" width="54" height="16" fill="#fef3c7" stroke="#d97706" stroke-width="1.5" rx="2"></rect>
                    <text x="247" y="49" font-size="9.5" font-weight="700" fill="#92400e" text-anchor="middle">R2 (8V)</text>

                    ${isParallel ? `
                      <!-- ================= PARALLEL WIRING (BENAR) ================= -->
                      <!-- Main loop conductor after R2 remains 100% CONTINUOUS to corner -->
                      <line x1="274" y1="45" x2="410" y2="45" stroke="#334155" stroke-width="2.5"></line>

                      <!-- Normal loop current flow arrows -->
                      <polygon points="85,42 93,45 85,48" fill="#10b981"/>
                      <polygon points="195,42 203,45 195,48" fill="#10b981"/>
                      <polygon points="350,42 358,45 350,48" fill="#10b981"/>
                      <text x="350" y="36" font-size="8.5" font-weight="700" fill="#10b981">I_loop normal</text>

                      <!-- Measurement Nodes A and B branching off across R2 -->
                      <circle cx="210" cy="45" r="5" fill="#ef4444"></circle>
                      <text x="210" y="32" font-size="9" font-weight="800" fill="#ef4444" text-anchor="middle">Titik A (12V)</text>

                      <circle cx="285" cy="45" r="5" fill="#3b82f6"></circle>
                      <text x="285" y="32" font-size="9" font-weight="800" fill="#3b82f6" text-anchor="middle">Titik B (4V)</text>

                      <!-- Probe Wires branching down into Parallel Voltmeter -->
                      <path d="M 210 45 C 210 95, ${isReversed ? '290' : '230'} 95, ${isReversed ? '290' : '230'} 130" fill="none" stroke="${isReversed ? '#0f172a' : '#ef4444'}" stroke-width="2.5" stroke-dasharray="4 2"></path>
                      <path d="M 285 45 C 285 95, ${isReversed ? '230' : '290'} 95, ${isReversed ? '230' : '290'} 130" fill="none" stroke="${isReversed ? '#ef4444' : '#0f172a'}" stroke-width="2.5" stroke-dasharray="4 2"></path>

                      <!-- Parallel Voltmeter Box -->
                      <rect x="210" y="130" width="100" height="50" fill="#0f172a" stroke="#0284c7" stroke-width="2" rx="6"></rect>
                      <text x="260" y="152" font-family="monospace" font-size="13" font-weight="800" fill="#38bdf8" text-anchor="middle">${displayReading} V</text>
                      <text x="260" y="165" font-size="8" fill="#94a3b8" text-anchor="middle">Voltmeter (R_in ≈ 10 MΩ)</text>
                      <text x="230" y="174" font-size="8" font-weight="700" fill="${isReversed ? '#94a3b8' : '#f87171'}" text-anchor="middle">[${isReversed ? 'COM' : 'V+'}]</text>
                      <text x="290" y="174" font-size="8" font-weight="700" fill="${isReversed ? '#f87171' : '#94a3b8'}" text-anchor="middle">[${isReversed ? 'V+' : 'COM'}]</text>
                      <text x="260" y="196" font-size="8.5" font-weight="600" fill="#0369a1" text-anchor="middle">Jalur utama utuh • Meter mencabang paralel</text>
                    ` : `
                      <!-- ================= SERIES WIRING (SALAH) ================= -->
                      <!-- Conductor after R2 leads to Terminal 1, then THERE IS A VISIBLE CUT/BREAK -->
                      <line x1="274" y1="45" x2="310" y2="45" stroke="#334155" stroke-width="2.5"></line>
                      <circle cx="310" cy="45" r="5.5" fill="#ef4444" stroke="#991b1b" stroke-width="1.5"></circle>
                      <text x="310" y="32" font-size="9" font-weight="800" fill="#ef4444" text-anchor="middle">Kutub 1</text>

                      <!-- PHYSICAL AIR GAP (NO WIRE!) -->
                      <line x1="315" y1="45" x2="365" y2="45" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="3 3"></line>
                      <rect x="320" y="36" width="40" height="18" fill="#fef2f2" stroke="#ef4444" stroke-width="1" rx="3"></rect>
                      <text x="340" y="49" font-size="8" font-weight="900" fill="#dc2626" text-anchor="middle">✂️ PUTUS</text>

                      <!-- Terminal 2 continuing to corner -->
                      <circle cx="370" cy="45" r="5.5" fill="#3b82f6" stroke="#1d4ed8" stroke-width="1.5"></circle>
                      <text x="370" y="32" font-size="9" font-weight="800" fill="#3b82f6" text-anchor="middle">Kutub 2</text>
                      <line x1="375" y1="45" x2="410" y2="45" stroke="#334155" stroke-width="2.5"></line>

                      <!-- Voltmeter PROBES PHYSICALLY BRIDGING THE CUT (INSERTED IN SERIES) -->
                      <path d="M 310 45 C 310 85, ${isReversed ? '355' : '325'} 85, ${isReversed ? '355' : '325'} 120" fill="none" stroke="${isReversed ? '#0f172a' : '#ef4444'}" stroke-width="2.5"></path>
                      <path d="M 370 45 C 370 85, ${isReversed ? '325' : '355'} 85, ${isReversed ? '325' : '355'} 120" fill="none" stroke="${isReversed ? '#ef4444' : '#0f172a'}" stroke-width="2.5"></path>

                      <!-- Voltmeter Box Directly in Series Path -->
                      <rect x="300" y="120" width="80" height="50" fill="#0f172a" stroke="#ef4444" stroke-width="2" rx="6"></rect>
                      <text x="340" y="140" font-family="monospace" font-size="12" font-weight="800" fill="#ef4444" text-anchor="middle">${displayReading} V</text>
                      <text x="340" y="152" font-size="7.5" fill="#fca5a5" text-anchor="middle">R_in ≈ 10 MΩ</text>
                      <text x="325" y="162" font-size="7.5" font-weight="700" fill="${isReversed ? '#94a3b8' : '#f87171'}" text-anchor="middle">[${isReversed ? 'COM' : 'V+'}]</text>
                      <text x="355" y="162" font-size="7.5" font-weight="700" fill="${isReversed ? '#f87171' : '#94a3b8'}" text-anchor="middle">[${isReversed ? 'V+' : 'COM'}]</text>

                      <!-- Clear consequence text -->
                      <text x="240" y="205" font-size="9" font-weight="800" fill="#dc2626" text-anchor="middle">⚠️ Arus tercekik impedansi 10 MΩ (I sangat kecil / mendekati nol)</text>
                      <text x="240" y="218" font-size="8" fill="#64748b" text-anchor="middle">Beban tidak bekerja • Voltmeter menyisip memutus kawat utama</text>
                    `}
                  </svg>
                </div>

                <!-- Action Controls (Explicit type="button", preserves viewport position) -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                  <button type="button" class="diagnostic-btn ${isParallel ? 'selected' : ''}" style="flex: 1;" onclick="setVoltPlacement('parallel')">
                    ✓ Pasang Paralel (Benar)
                  </button>
                  <button type="button" class="diagnostic-btn ${!isParallel ? 'selected' : ''}" style="flex: 1;" onclick="setVoltPlacement('series')">
                    ✗ Pasang Seri (Salah)
                  </button>
                  <button type="button" class="diagnostic-btn" style="flex: 1.2;" onclick="toggleVoltReversal()">
                    🔄 Balik Posisi Probe (Merah ⮂ Hitam)
                  </button>
                </div>

                <!-- Explanation Box Aligned with Fluxus Model -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 0.84rem; line-height: 1.5; color: #334155;">
                  <div style="font-family: monospace; font-weight: 700; color: #0284c7; margin-bottom: 4px;">
                    ${mathFormula}
                  </div>
                  ${isParallel ? `
                    <div>
                      <strong>Analisis Rangkaian Paralel:</strong> Kawat utama sirkuit tetap utuh sehingga beban R1 dan R2 dialiri arus kerja normal. Voltmeter mencabang secara paralel dengan resistansi input sangat tinggi (~10 MΩ), sehingga hanya menyerap arus super kecil yang tidak membebani rangkaian.
                    </div>
                  ` : `
                    <div>
                      <strong>Analisis Kesalahan Pemasangan Seri:</strong> Voltmeter dipasang pada jalur utama sehingga arus rangkaian harus melewati impedansi input meter (~10 MΩ). Akibatnya arus menjadi sangat kecil / mendekati nol dan beban dapat tidak bekerja sebagaimana mestinya. Untuk mengukur tegangan, Voltmeter harus dipasang PARALEL.
                    </div>
                  `}
                </div>

                <div style="margin-top: 14px; text-align: right;">
                  <button type="button" class="btn-step-nav btn-step-next" onclick="goToMeterStep(3)">
                    Lanjut ke Mode Arus (A DC) →
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function setVoltPlacement(placement) {
      learningMeter.voltPlacement = placement;
      learningMeter.voltAttempted = true;
      renderCurrentMeterStep(true);
    }

    function toggleVoltReversal() {
      learningMeter.voltProbesReversed = !learningMeter.voltProbesReversed;
      learningMeter.voltAttempted = true;
      renderCurrentMeterStep(true);
    }

    /* --------------------------------------------------------------------------
       STEP 3: MODE ARUS (A DC) - AMPEREMETER = SERI (1 mΩ SHUNT)
       -------------------------------------------------------------------------- */
    function renderMeterStep3(container) {
      const isSeries = learningMeter.ampPlacement === 'series';
      const isWarningDone = learningMeter.ampWarningAttempted;

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 3: Mode Kuat Arus (A DC) — Amperemeter Harus Seri</h3>
            <p class="sp-step-desc">
              Kuat arus adalah laju aliran muatan listrik. <strong>Fluxus educational Ammeter menggunakan resistansi shunt sangat rendah (~1 mΩ) 
              sehingga menjadi lintasan berhambatan sangat kecil</strong>. Amperemeter wajib disisipkan secara <strong>SERI</strong> dengan memutus kawat.
            </p>
          </div>

          <div class="sp-workbench-layout" style="grid-template-columns: 260px 1fr; gap: 20px; align-items: start;">
            <!-- Left: Real Multimeter in A Mode -->
            <div class="meter-trainer-wrapper">
              <span style="font-size: 0.72rem; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                Mode A⎓ (Arus DC)
              </span>

              <div class="meter-trainer-mount" style="transform: scale(0.95); margin: -8px auto;">
                ${renderFluxusMultimeterHTML({
                  powerOn: true,
                  mode: 'A_DC',
                  reading: isSeries ? '2.40' : '0.00',
                  unit: 'A',
                  activeJackRed: 'MA',
                  activeJackBlack: 'COM'
                })}
              </div>

              <!-- Terminal Jack Indicator -->
              <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 6px 10px; margin-top: 10px; font-size: 0.75rem; text-align: center; color: #166534;">
                Jack Terpasang: <strong>COM</strong> (Hitam) & <strong>mA/A</strong> (Merah)
              </div>
            </div>

            <!-- Right: Schematic & Safety Warning -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div class="sp-card" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                  <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">Pemasangan Amperemeter pada Sirkuit 12V</span>
                  <span style="font-size: 0.72rem; padding: 2px 10px; border-radius: 9999px; font-weight: 700; background: ${isSeries ? '#dcfce7' : '#fee2e2'}; color: ${isSeries ? '#15803d' : '#b91c1c'};">
                    ${isSeries ? '✓ Pemasangan Seri (Benar)' : '⚠️ Pemasangan Paralel (Bahaya)'}
                  </span>
                </div>

                <!-- Schematic SVG -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                  <svg width="100%" height="200" viewBox="0 0 460 200" style="display: block; margin: 0 auto; max-width: 460px;">
                    <!-- 12V Battery -->
                    <rect x="40" y="65" width="30" height="50" fill="#f8fafc" stroke="#334155" stroke-width="1.5" rx="3"></rect>
                    <line x1="48" y1="80" x2="62" y2="80" stroke="#ef4444" stroke-width="3"></line>
                    <line x1="51" y1="100" x2="59" y2="100" stroke="#0284c7" stroke-width="2"></line>
                    <text x="30" y="94" font-size="11" font-weight="700" fill="#0f172a" text-anchor="end">12V</text>

                    <!-- Bottom Return Wire -->
                    <line x1="55" y1="115" x2="55" y2="155" stroke="#334155" stroke-width="2.5"></line>
                    <line x1="55" y1="155" x2="400" y2="155" stroke="#334155" stroke-width="2.5"></line>
                    <line x1="400" y1="155" x2="400" y2="100" stroke="#334155" stroke-width="2.5"></line>

                    <!-- Load Resistor R = 5 Ω -->
                    <rect x="375" y="60" width="50" height="40" fill="#fef3c7" stroke="#d97706" stroke-width="1.5" rx="3"></rect>
                    <text x="400" y="82" font-size="10" font-weight="700" fill="#92400e" text-anchor="middle">Beban R</text>
                    <text x="400" y="94" font-size="9" fill="#92400e" text-anchor="middle">5 Ω</text>

                    <!-- Top wire from battery to break -->
                    <line x1="55" y1="65" x2="55" y2="45" stroke="#334155" stroke-width="2.5"></line>

                    ${isSeries ? `
                      <!-- Ammeter inserted in SERIES (Main wire is cut, meter bridges the cut) -->
                      <line x1="55" y1="45" x2="160" y2="45" stroke="#334155" stroke-width="2.5"></line>
                      <circle cx="160" cy="45" r="5" fill="#ef4444"></circle>

                      <rect x="180" y="20" width="110" height="50" fill="#0f172a" stroke="#0284c7" stroke-width="2" rx="6"></rect>
                      <text x="235" y="43" font-family="monospace" font-size="14" font-weight="800" fill="#38bdf8" text-anchor="middle">2.40 A DC</text>
                      <text x="195" y="62" font-size="8" fill="#f87171" font-weight="700">RED [In]</text>
                      <text x="265" y="62" font-size="8" fill="#94a3b8" font-weight="700">COM [Out]</text>

                      <!-- Wire continuing from Ammeter to Load -->
                      <circle cx="310" cy="45" r="5" fill="#3b82f6"></circle>
                      <line x1="310" y1="45" x2="400" y2="45" stroke="#334155" stroke-width="2.5"></line>
                      <line x1="400" y1="45" x2="400" y2="60" stroke="#334155" stroke-width="2.5"></line>
                    ` : `
                      <!-- Wrong Parallel Setup: Main line stays, Ammeter shorts across battery -->
                      <line x1="55" y1="45" x2="400" y2="45" stroke="#334155" stroke-width="2.5"></line>
                      <line x1="400" y1="45" x2="400" y2="60" stroke="#334155" stroke-width="2.5"></line>
                      
                      <!-- Parallel short branch -->
                      <path d="M 90 45 L 90 90 L 150 90" fill="none" stroke="#ef4444" stroke-width="2.5"></path>
                      <path d="M 90 155 L 90 120 L 150 120" fill="none" stroke="#0f172a" stroke-width="2.5"></path>
                      <rect x="150" y="85" width="100" height="42" fill="#fef2f2" stroke="#ef4444" stroke-width="2" rx="6"></rect>
                      <text x="200" y="104" font-size="10" font-weight="800" fill="#b91c1c" text-anchor="middle">💥 KORSLETING!</text>
                      <text x="200" y="118" font-size="8" fill="#ef4444" text-anchor="middle">I = 12.000 A • Sekring Putus</text>
                    `}
                  </svg>
                </div>

                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                  <button type="button" class="diagnostic-btn ${isSeries ? 'selected' : ''}" style="flex: 1;" onclick="setAmpPlacement('series')">
                    ✓ Pasang Seri Memutus Kawat
                  </button>
                  <button type="button" class="diagnostic-btn ${!isSeries ? 'selected' : ''}" style="flex: 1;" onclick="setAmpPlacement('parallel')">
                    ✗ Pasang Paralel Langsung
                  </button>
                </div>

                <!-- Conceptual Question -->
                <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 14px;">
                  <span style="font-size: 0.72rem; font-weight: 800; color: #c2410c; text-transform: uppercase;">⚠️ Pertanyaan Pemahaman Keselamatan:</span>
                  <p style="font-size: 0.84rem; font-weight: 700; color: #9a3412; margin: 6px 0 10px 0;">
                    Apa akibatnya jika mode amperemeter dihubungkan paralel langsung pada kedua kutub baterai 12V?
                  </p>

                  <div style="display: flex; flex-direction: column; gap: 6px;">
                    <button type="button" class="diagnostic-btn ${learningMeter.ampWarningAnswer === 0 ? 'selected' : ''}" onclick="answerAmpWarning(0)">
                      A. Multimeter membaca tegangan baterai 12 Volt secara normal
                    </button>
                    <button type="button" class="diagnostic-btn ${learningMeter.ampWarningAnswer === 1 ? 'selected' : ''}" onclick="answerAmpWarning(1)">
                      B. Terjadi hubungan singkat (korsleting) arus masif yang memutuskan sekring multimeter!
                    </button>
                    <button type="button" class="diagnostic-btn ${learningMeter.ampWarningAnswer === 2 ? 'selected' : ''}" onclick="answerAmpWarning(2)">
                      C. Arus rangkaian otomatis menjadi 0 Ampere
                    </button>
                  </div>

                  ${isWarningDone ? `
                    <div class="diagnostic-feedback ${learningMeter.ampWarningCorrect ? 'correct' : 'incorrect'}">
                      ${learningMeter.ampWarningCorrect 
                        ? '🎉 <strong>Tepat Sekali!</strong> Karena resistansi shunt amperemeter sangat rendah (~0.001 Ω), arus hubung singkat teoritis mencapai I = 12 / 0.001 = 12.000 Ampere! Arus ini seketika membakar sekring pengaman multimeter.'
                        : '❌ <strong>Kurang Tepat.</strong> Amperemeter bertindak seperti kawat jumper lurus (R ≈ 1 mΩ). Menghubungkannya paralel langsung ke kutub baterai menghasilkan arus korslet sangat besar.'
                      }
                    </div>
                  ` : ''}
                </div>

                <div style="margin-top: 14px; text-align: right;">
                  <button type="button" class="btn-step-nav btn-step-next" onclick="goToMeterStep(4)">
                    Lanjut ke Mode Hambatan (Ω) →
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function setAmpPlacement(placement) {
      learningMeter.ampPlacement = placement;
      learningMeter.ampAttempted = true;
      renderCurrentMeterStep(true);
    }

    function answerAmpWarning(choice) {
      learningMeter.ampWarningAnswer = choice;
      learningMeter.ampWarningAttempted = true;
      learningMeter.ampWarningCorrect = (choice === 1);
      renderCurrentMeterStep(true);
    }

    /* --------------------------------------------------------------------------
       STEP 4: MODE HAMBATAN (Ω) - SUMBER WAJIB MATI (LIVE CIRCUIT PROTECTION)
       -------------------------------------------------------------------------- */
    function renderMeterStep4(container) {
      const isPowerOn = learningMeter.powerSourceOn;

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 4: Mode Hambatan (Ω) — Sumber Wajib Dimatikan (OFF)</h3>
            <p class="sp-step-desc">
              Mode Ω digunakan saat rangkaian tidak diberi sumber aktif. 
              <strong>Pada rangkaian hidup, Fluxus menampilkan LIVE CIRCUIT dan menolak pembacaan resistansi</strong>.
            </p>
          </div>

          <div class="sp-workbench-layout" style="grid-template-columns: 260px 1fr; gap: 20px; align-items: start;">
            <!-- Left: Real Multimeter in Ohm Mode -->
            <div class="meter-trainer-wrapper">
              <span style="font-size: 0.72rem; font-weight: 800; color: #0284c7; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                Mode Ω (Resistansi)
              </span>

              <div class="meter-trainer-mount" style="transform: scale(0.95); margin: -8px auto;">
                ${renderFluxusMultimeterHTML({
                  powerOn: true,
                  mode: 'OHM',
                  reading: isPowerOn ? 'ERR LIVE' : '500.0',
                  unit: isPowerOn ? '' : 'Ω',
                  activeJackRed: 'V_OHM',
                  activeJackBlack: 'COM'
                })}
              </div>

              <!-- Terminal Jack Indicator -->
              <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 6px 10px; margin-top: 10px; font-size: 0.75rem; text-align: center; color: #0369a1;">
                Jack Terpasang: <strong>COM</strong> (Hitam) & <strong>V-Ω-mA</strong> (Merah)
              </div>
            </div>

            <!-- Right: Live vs De-energized Circuit Scenario -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div class="sp-card" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                  <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">Kondisi Sakelar Sumber Rangkaian</span>
                  <span style="font-size: 0.72rem; padding: 2px 10px; border-radius: 9999px; font-weight: 700; background: ${!isPowerOn ? '#dcfce7' : '#fee2e2'}; color: ${!isPowerOn ? '#15803d' : '#b91c1c'};">
                    ${!isPowerOn ? '✓ Sakelar Terbuka (OFF — Aman)' : '⚠️ Sakelar Tertutup (ON — Live Circuit)'}
                  </span>
                </div>

                <!-- Schematic SVG -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                  <svg width="100%" height="190" viewBox="0 0 460 190" style="display: block; margin: 0 auto; max-width: 460px;">
                    <!-- Circuit Loop -->
                    <rect x="50" y="50" width="360" height="90" fill="none" stroke="#64748b" stroke-width="2.5" rx="4"></rect>
                    
                    <!-- 9V Battery on Left -->
                    <rect x="35" y="75" width="30" height="40" fill="#f8fafc" stroke="#334155" stroke-width="1.5" rx="3"></rect>
                    <text x="25" y="98" font-size="10" font-weight="700" fill="#0f172a" text-anchor="end">9V Batt</text>

                    <!-- Knife Switch at Top-Left (Visibly Open vs Closed) -->
                    <rect x="100" y="38" width="55" height="24" fill="#ffffff"></rect>
                    <circle cx="105" cy="50" r="3.5" fill="#334155"/>
                    <circle cx="145" cy="50" r="3.5" fill="#334155"/>

                    ${isPowerOn ? `
                      <!-- Switch CLOSED (Green Line) -->
                      <line x1="105" y1="50" x2="145" y2="50" stroke="#10b981" stroke-width="3.5"></line>
                      <text x="125" y="34" font-size="8.5" font-weight="800" fill="#10b981" text-anchor="middle">SAKELAR ON</text>
                    ` : `
                      <!-- Switch OPEN (Angled Red Blade with visible air gap) -->
                      <line x1="105" y1="50" x2="135" y2="30" stroke="#ef4444" stroke-width="3.5" stroke-linecap="round"></line>
                      <text x="125" y="26" font-size="8.5" font-weight="800" fill="#ef4444" text-anchor="middle">SAKELAR OFF</text>
                    `}

                    <!-- Resistor Under Test (Top Right) -->
                    <rect x="270" y="42" width="60" height="16" fill="#fef3c7" stroke="#d97706" stroke-width="1.5" rx="2"></rect>
                    <text x="300" y="54" font-size="10" font-weight="700" fill="#92400e" text-anchor="middle">R = 500 Ω</text>

                    <!-- Multimeter Ohmmeter Probe Leads -->
                    <path d="M 270 50 C 270 95, 285 105, 285 135" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-dasharray="3 2"></path>
                    <path d="M 330 50 C 330 95, 315 105, 315 135" fill="none" stroke="#0f172a" stroke-width="2.5" stroke-dasharray="3 2"></path>

                    <!-- Multimeter Box -->
                    <rect x="250" y="135" width="100" height="48" fill="#0f172a" stroke="${isPowerOn ? '#ef4444' : '#0284c7'}" stroke-width="2" rx="6"></rect>
                    <text x="300" y="165" font-family="monospace" font-size="13" font-weight="800" fill="${isPowerOn ? '#ef4444' : '#38bdf8'}" text-anchor="middle">
                      ${isPowerOn ? 'ERR LIVE' : '500.0 Ω'}
                    </text>
                  </svg>
                </div>

                <div style="display: flex; gap: 8px; margin-bottom: 14px;">
                  <button type="button" class="diagnostic-btn ${!isPowerOn ? 'selected' : ''}" style="flex: 1;" onclick="togglePowerSource(false)">
                    ✓ Matikan Sakelar (Power OFF)
                  </button>
                  <button type="button" class="diagnostic-btn ${isPowerOn ? 'selected' : ''}" style="flex: 1;" onclick="togglePowerSource(true)">
                    ⚠️ Nyalakan Sakelar (Power ON)
                  </button>
                </div>

                ${isPowerOn ? `
                  <div style="padding: 12px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px; font-size: 0.84rem; color: #991b1b; line-height: 1.5;">
                    <strong>⚠️ PERINGATAN FLUXUS: TEGANGAN AKTIF TERDETEKSI (LIVE CIRCUIT)</strong><br>
                    Pengukuran resistansi dilakukan pada rangkaian tanpa sumber aktif. Fluxus mendeteksi keberadaan tegangan eksternal dan secara otomatis menolak pengukuran dengan pesan <strong>ERR LIVE CIRCUIT</strong> untuk melindungi instrumen.
                  </div>
                ` : `
                  <div style="padding: 12px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; font-size: 0.84rem; color: #166534; line-height: 1.5;">
                    <strong>✅ KONDISI PENGUKURAN AMAN (DE-ENERGIZED):</strong><br>
                    Rangkaian bebas dari tegangan aktif eksternal. Multimeter mengalirkan arus uji internal kecil melalui resistor dan menampilkan nilai akurat <strong>500.0 Ω</strong>.
                  </div>
                `}

                <div style="margin-top: 14px; text-align: right;">
                  <button type="button" class="btn-step-nav btn-step-next" onclick="goToMeterStep(5)">
                    Lanjut ke Tantangan Diagnostik →
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function togglePowerSource(state) {
      learningMeter.powerSourceOn = state;
      learningMeter.ohmAttempted = true;
      renderCurrentMeterStep(true);
    }

    /* --------------------------------------------------------------------------
       STEP 5: KESALAHAN NYATA & TANTANGAN DIAGNOSTIK (ALIGNED WITH FLUXUS)
       -------------------------------------------------------------------------- */
    const DIAGNOSTIC_CASES = [
      {
        id: 1,
        title: "Kasus 1: Mengukur Kuat Arus Paralel pada Sumber",
        symptom: "Siswa menghubungkan probe multimeter pada mode 10A / mA secara paralel langsung ke kedua kutub aki 12V. Terjadi lonjakan percikan api dan multimeter mati.",
        options: [
          "Ganti kabel probe dengan kabel berpenampang lebih tebal.",
          "Amperemeter memiliki hambatan internal sangat rendah (~1 mΩ); koneksi paralel pada sumber tegangan memicu korsleting seketika. Amperemeter harus dipasang seri memutus jalur.",
          "Cukup ubah selektor multimeter ke mode AC."
        ],
        correct: 1,
        explanation: "Amperemeter dirancang sebagai lintasan arus berhambatan sangat rendah (~1 mΩ). Menghubungkannya paralel langsung pada sumber tegangan menghasilkan korsleting masif."
      },
      {
        id: 2,
        title: "Kasus 2: Mengukur Tegangan Lampu Secara Seri",
        symptom: "Siswa memotong kawat rangkaian dan memasang voltmeter secara seri di jalur lampu. Lampu langsung padam total dan voltmeter membaca mendekati tegangan sumber.",
        options: [
          "Baterai telah habis energinya sehingga lampu mati.",
          "Karena hambatan input voltmeter sangat tinggi (~10 MΩ), memasangnya secara seri sangat membatasi arus rangkaian sehingga beban tidak dapat bekerja sebagaimana mestinya. Voltmeter harus dipasang paralel.",
          "Lampu putus karena kelebihan daya."
        ],
        correct: 1,
        explanation: "Karena hambatan input voltmeter sangat tinggi (~10 MΩ), memasangnya secara seri sangat membatasi arus rangkaian, sehingga beban dapat tidak bekerja sebagaimana mestinya. Voltmeter seharusnya dipasang paralel."
      },
      {
        id: 3,
        title: "Kasus 3: Mengukur Resistansi Saat Sirkuit Masih Bekerja",
        symptom: "Pengguna ingin mengukur nilai resistor pada papan sirkuit yang masih terhubung catu daya. Layar multimeter menampilkan pesan error dan pembacaan ditolak.",
        options: [
          "Multimeter kehabisan baterai internal.",
          "Mode Ω digunakan saat rangkaian tidak diberi sumber aktif. Pada rangkaian hidup, Fluxus menampilkan LIVE CIRCUIT dan menolak pembacaan resistansi.",
          "Resistor tersebut rusak karena nilai hambatan berubah drastis."
        ],
        correct: 1,
        explanation: "Mode Ω digunakan saat rangkaian tidak diberi sumber aktif. Pada rangkaian hidup, Fluxus menampilkan LIVE CIRCUIT dan menolak pembacaan resistansi."
      },
      {
        id: 4,
        title: "Kasus 4: Hasil Pengukuran Tegangan Bertanda Negatif (-12.0 V)",
        symptom: "Saat memeriksa tegangan power supply DC 12V, layar multimeter menampilkan angka bertanda negatif (-12.00 V).",
        options: [
          "Power supply mengalami kebocoran fasa tegangan AC.",
          "Probe merah terhubung ke potensial lebih rendah daripada probe hitam (COM). Tukar posisi kedua probe jika ingin pembacaan bernilai positif.",
          "Multimeter mengalami kerusakan kalibrasi internal."
        ],
        correct: 1,
        explanation: "Multimeter membaca V_display = V_merah - V_hitam. Tanda negatif adalah indikator polaritas alami bahwa probe merah menempel di titik berpotensial lebih rendah daripada COM."
      }
    ];

    function renderMeterStep5(container) {
      let completedCount = 0;
      DIAGNOSTIC_CASES.forEach(c => {
        if (learningMeter.diagnosticChecked[c.id]) completedCount++;
      });

      let casesHtml = '';
      DIAGNOSTIC_CASES.forEach(c => {
        const selected = learningMeter.diagnosticAnswers[c.id];
        const isChecked = learningMeter.diagnosticChecked[c.id];
        const isCorrect = selected === c.correct;

        casesHtml += `
          <div class="diagnostic-card">
            <div class="diagnostic-header">
              <span class="diagnostic-title">${c.title}</span>
              <span class="diagnostic-badge ${isChecked ? (isCorrect ? 'badge-success' : 'badge-danger') : ''}">
                ${isChecked ? (isCorrect ? '✓ Terpecahkan' : '✗ Belum Tepat') : '○ Belum Diuji'}
              </span>
            </div>
            <p style="font-size: 0.84rem; color: #475569; margin: 4px 0 12px 0; line-height: 1.5;">
              <strong>Gejala:</strong> ${c.symptom}
            </p>
            <div class="diagnostic-btn-group">
              ${c.options.map((opt, idx) => `
                <button type="button" class="diagnostic-btn ${selected === idx ? 'selected' : ''}" onclick="selectDiagnosticAnswer(${c.id}, ${idx})">
                  ${String.fromCharCode(65 + idx)}. ${opt}
                </button>
              `).join('')}
            </div>
            ${isChecked ? `
              <div class="diagnostic-feedback ${isCorrect ? 'correct' : 'incorrect'}">
                ${isCorrect ? '🎉 <strong>Benar!</strong> ' : '❌ <strong>Kurang Tepat.</strong> '} ${c.explanation}
              </div>
            ` : ''}
          </div>
        `;
      });

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 5: Kesalahan Nyata & Tantangan Diagnostik</h3>
            <p class="sp-step-desc">
              Pahami 4 skenario kesalahan paling umum saat menggunakan multimeter di laboratorium virtual. Analisis setiap kasus dan tentukan solusi teknis yang tepat.
            </p>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
            <span style="font-size: 0.84rem; color: #475569; font-weight: 600;">
              Progres Diagnostik: <strong style="color: #0284c7;">${completedCount} / 4 Kasus Selesai</strong>
            </span>
            ${completedCount === 4 ? '<span style="font-size: 0.8rem; color: #10b981; font-weight: 700;">Semua kasus tuntas!</span>' : ''}
          </div>

          <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
            ${casesHtml}
          </div>

          <div style="text-align: right; padding-top: 12px; border-top: 1px solid #e2e8f0;">
            <button type="button" class="btn-step-nav btn-step-next" onclick="goToMeterStep(6)">
              Lanjut ke Evaluasi & Simulator →
            </button>
          </div>
        </div>
      `;
    }

    function selectDiagnosticAnswer(caseId, optIdx) {
      learningMeter.diagnosticAnswers[caseId] = optIdx;
      learningMeter.diagnosticChecked[caseId] = true;
      renderCurrentMeterStep(true);
    }

    /* --------------------------------------------------------------------------
       STEP 6: QUIZ EVALUASI & TANTANGAN SIMULATOR
       -------------------------------------------------------------------------- */
    function renderMeterStep6(container) {
      const isSubmitted = learningMeter.quizSubmitted;
      let score = 0;
      if (isSubmitted) {
        METER_QUIZ_QUESTIONS.forEach((q, idx) => {
          if (learningMeter.quizAnswers[idx] === q.correct) score++;
        });
      }

      // Completion checklist
      const hasExploredControls = getExploredControlsCount() >= 7;
      const hasTestedVoltage = learningMeter.voltAttempted;
      const hasTestedAmp = learningMeter.ampAttempted || learningMeter.ampWarningAttempted;
      const hasTestedOhm = learningMeter.ohmAttempted;
      const hasDoneDiagnostics = Object.keys(learningMeter.diagnosticChecked).filter(k => learningMeter.diagnosticChecked[k]).length >= 4;
      const hasPassedQuiz = isSubmitted && (score >= 6);

      const canComplete = hasExploredControls && hasTestedVoltage && hasTestedAmp && hasTestedOhm && hasDoneDiagnostics && hasPassedQuiz;

      let quizHtml = '';
      METER_QUIZ_QUESTIONS.forEach((q, idx) => {
        const userAns = learningMeter.quizAnswers[idx];
        const isCorrect = userAns === q.correct;

        quizHtml += `
          <div style="padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; background: #ffffff;">
            <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
              ${idx + 1}. ${q.q}
            </div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
              ${q.options.map((opt, optIdx) => `
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem; color: #334155; padding: 6px 10px; border-radius: 6px; cursor: pointer; background: ${userAns === optIdx ? 'rgba(56, 189, 248, 0.1)' : '#f8fafc'}; border: 1px solid ${userAns === optIdx ? '#38bdf8' : '#e2e8f0'};">
                  <input type="radio" name="meter-quiz-${idx}" value="${optIdx}" ${userAns === optIdx ? 'checked' : ''} ${isSubmitted ? 'disabled' : ''} onchange="selectMeterQuizAnswer(${idx}, ${optIdx})">
                  <span>${opt}</span>
                </label>
              `).join('')}
            </div>
            ${isSubmitted ? `
              <div class="diagnostic-feedback ${isCorrect ? 'correct' : 'incorrect'}" style="margin-top: 8px;">
                ${isCorrect ? '✓ <strong>Benar!</strong> ' : '✗ <strong>Salah.</strong> '} ${q.explanation}
              </div>
            ` : ''}
          </div>
        `;
      });

      container.innerHTML = `
        <div class="sp-step-container">
          <div class="sp-step-intro">
            <h3 class="sp-step-title">Langkah 6: Quiz Evaluasi & Praktik di Simulator</h3>
            <p class="sp-step-desc">
              Uji pemahaman Anda mengenai multimeter digital Fluxus dengan 8 soal evaluasi berikut, 
              lalu terapkan keahlian Anda di Simulator Laboratorium Virtual.
            </p>
          </div>

          <div class="sp-workbench-layout" style="grid-template-columns: 1.2fr 0.8fr; gap: 20px; align-items: start;">
            <!-- Left: 8 Quiz Questions -->
            <div class="sp-card" style="padding: 16px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Evaluasi Teori Multimeter (8 Soal)</h4>
                ${isSubmitted ? `
                  <span style="font-size: 0.88rem; font-weight: 800; color: ${score >= 6 ? '#10b981' : '#ef4444'};">
                    Skor: ${score} / 8 (${Math.round((score/8)*100)}%)
                  </span>
                ` : ''}
              </div>

              ${quizHtml}

              <div style="margin-top: 14px;">
                ${!isSubmitted ? `
                  <button type="button" class="btn-step-nav btn-step-next" style="width: 100%; justify-content: center;" onclick="submitMeterQuiz()">
                    Kirim & Periksa Jawaban Kuis
                  </button>
                ` : `
                  <button type="button" class="btn-step-nav btn-step-prev" style="width: 100%; justify-content: center;" onclick="retryMeterQuiz()">
                    Ulangi Kuis
                  </button>
                `}
              </div>
            </div>

            <!-- Right: Simulator Challenges & Completion Lock -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
              <div class="sp-card" style="padding: 16px;">
                <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 10px;">
                  4 Tantangan Praktik Simulator
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; font-size: 0.82rem; color: #334155; line-height: 1.45;">
                  <div style="padding: 8px 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <strong>1. Pengukuran Tegangan Drop:</strong> Pasang probe voltmeter secara paralel pada resistor di simulator.
                  </div>
                  <div style="padding: 8px 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <strong>2. Pengukuran Arus Seri:</strong> Putus kawat rangkaian dan hubungkan multimeter mode Amperemeter seri.
                  </div>
                  <div style="padding: 8px 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <strong>3. Uji Hambatan De-energized:</strong> Matikan sakelar baterai terlebih dahulu, lalu ukur resistor dengan mode Ohmmeter.
                  </div>
                  <div style="padding: 8px 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <strong>4. Eksplorasi Polaritas:</strong> Balikkan posisi probe merah dan hitam untuk mengamati tanda minus (-) pada pembacaan DC.
                  </div>
                </div>

                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-step-nav btn-step-next" style="width: 100%; justify-content: center; text-decoration: none; margin-bottom: 16px;">
                  Buka Simulator Virtual Lab ↗
                </a>

                <!-- Completion Checklist -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                  <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 8px;">
                    Syarat Kelulusan Modul:
                  </span>
                  <div style="display: flex; flex-direction: column; gap: 4px; font-size: 0.8rem; color: #334155; margin-bottom: 14px;">
                    <div>${hasExploredControls ? '✅' : '○'} Pelajari 7 Kendali Multimeter Fluxus</div>
                    <div>${hasTestedVoltage ? '✅' : '○'} Uji Pengukuran Tegangan Paralel & Polaritas</div>
                    <div>${hasTestedAmp ? '✅' : '○'} Pahami Arus Seri & Bahaya Korslet</div>
                    <div>${hasTestedOhm ? '✅' : '○'} Pahami Syarat Sirkuit De-energized</div>
                    <div>${hasDoneDiagnostics ? '✅' : '○'} Tuntaskan 4 Kasus Diagnostik</div>
                    <div>${hasPassedQuiz ? '✅' : '○'} Lulus Kuis Evaluasi (Min. 75%)</div>
                  </div>

                  <button type="button" class="btn-step-nav btn-step-next" id="btn-finish-meter-module" style="width: 100%; justify-content: center;" onclick="finishAndSaveMeterModule(${currentDbMeterModuleId})" ${!canComplete ? 'disabled' : ''}>
                    ${canComplete ? '🎉 Selesaikan & Simpan Modul' : '🔒 Lengkapi Semua Syarat'}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function selectMeterQuizAnswer(qIdx, optIdx) {
      learningMeter.quizAnswers[qIdx] = optIdx;
    }

    function submitMeterQuiz() {
      learningMeter.quizSubmitted = true;
      learningMeter.quizAttempted = true;
      renderCurrentMeterStep(true);
    }

    function retryMeterQuiz() {
      learningMeter.quizSubmitted = false;
      learningMeter.quizAnswers = {};
      renderCurrentMeterStep(true);
    }

    function finishAndSaveMeterModule(dbId) {
      updateModuleProgress(dbId, 'selesai');
      closeModuleModal();
    }

    /* ==========================================================================
       Interactive Learning Module Engine (Modul 04: Rangkaian Seri & Paralel)
       FULL-SCREEN IMMERSIVE WORKSPACE IMPLEMENTATION
       ========================================================================== */

    let currentSPStep = 1;
    let completedSPSteps = new Set([1]);
    let currentDbSPModuleId = 4;

    const spState = {
      // Step 1
      exploredTypes: { seri: false, paralel: false },
      // Step 2: Series
      seriesVs: 12,
      seriesR1: 6,
      seriesR2: 3,
      seriesSwitchClosed: true,
      selectedSeriesElement: 'R1',
      seriesPredictionAnswer: null,
      seriesPredictionAttempted: false,
      seriesExplored: false,
      // Step 3: Parallel
      parallelVs: 12,
      parallelR1: 6,
      parallelR2: 8,
      branch1Active: true,
      branch2Active: true,
      selectedParallelElement: 'R1',
      parallelWhatIfAnswer: null,
      parallelWhatIfAttempted: false,
      parallelExplored: false,
      // Step 4: Comparison & Classification
      pathHighlighted: false,
      classifyAnswers: { 1: null, 2: null, 3: null },
      classifyAttempted: false,
      // Step 5: Calculation Practice
      practiceAnswers: {
        rTotSeries: "",
        iTotSeries: "",
        vR1Series: "",
        vR2Series: "",
        i1Parallel: "",
        i2Parallel: "",
        iTotParallel: "",
        rTotParallel: ""
      },
      practiceAttempted: false,
      // Step 6: Quiz
      quizAnswers: {},
      quizAttempted: false,
      quizSubmitted: false
    };

    const SP_QUIZ_QUESTIONS = [
      {
        q: "Pada rangkaian seri, besar kuat arus listrik yang mengalir melalui setiap resistor adalah...",
        options: ["Sama besar di setiap resistor", "Berbeda-beda tergantung nilai hambatan"],
        correct: 0,
        explanation: "Hanya terdapat satu jalur kawat pada rangkaian seri, sehingga laju muatan (arus) di setiap elemen adalah sama persis (I = I1 = I2)."
      },
      {
        q: "Pada rangkaian paralel, beda potensial (tegangan) yang melintasi setiap cabang adalah...",
        options: ["Sama dengan tegangan sumber", "Selalu berbeda di setiap cabang"],
        correct: 0,
        explanation: "Setiap cabang paralel terhubung langsung ke kedua kutub sumber tegangan yang sama, sehingga tegangannya identik (Vs = V1 = V2)."
      },
      {
        q: "Dua buah resistor R1 = 10 Ω dan R2 = 20 Ω dirangkai secara seri. Hambatan pengganti total (Rtot) adalah...",
        options: ["6.67 Ω", "15 Ω", "30 Ω"],
        correct: 2,
        explanation: "Pada rangkaian seri, hambatan dijumlahkan secara langsung: Rtot = R1 + R2 = 10 + 20 = 30 Ω."
      },
      {
        q: "Sebuah resistor 6 Ω terhubung paralel pada sumber tegangan 12 Volt. Kuat arus pada cabang resistor tersebut adalah...",
        options: ["0.5 A", "2 A", "72 A"],
        correct: 1,
        explanation: "Tegangan pada cabang adalah 12 V. Sesuai Hukum Ohm: I = V / R = 12 / 6 = 2 A."
      },
      {
        q: "Sebuah rangkaian paralel memiliki dua cabang dengan arus masing-masing 2 A dan 1.5 A. Arus total yang ditarik dari sumber adalah...",
        options: ["0.5 A", "3 A", "3.5 A"],
        correct: 2,
        explanation: "Sesuai Hukum Kirchoff I, arus total adalah penjumlahan arus dari seluruh cabang: Itot = I1 + I2 = 2 + 1.5 = 3.5 A."
      },
      {
        q: "Jika salah satu cabang pada rangkaian paralel terputus (open circuit), maka cabang lain yang masih terhubung...",
        options: ["Tetap dapat dialiri arus listrik", "Pasti ikut mati dan berhenti mengalirkan arus"],
        correct: 0,
        explanation: "Setiap cabang paralel memiliki lintasan tertutup tersendiri ke sumber tegangan, sehingga putusnya satu cabang tidak menghentikan cabang lainnya."
      }
    ];

    function openSeriesParallelModule(dbId, moduleNum = 4) {
      currentDbSPModuleId = dbId;
      currentSPStep = 1;
      completedSPSteps = new Set([1]);

      const container = document.getElementById("materi-modal-container");
      container.innerHTML = `
        <div class="sp-fullscreen-backdrop" onclick="closeModuleModal()">
          <div class="sp-fullscreen-container" onclick="event.stopPropagation()">
            
            <!-- 1. Full-Screen Module Header -->
            <header class="sp-fullscreen-header">
              <div class="sp-header-left">
                <span class="interactive-module-badge">⚡ Modul 04 • Rangkaian Listrik</span>
                <h2 class="sp-header-title">Rangkaian Seri & Paralel</h2>
              </div>

              <div class="sp-header-center">
                <div class="sp-progress-wrapper">
                  <div class="sp-progress-bar">
                    <div class="sp-progress-fill" id="sp-progress-fill" style="width: 16.7%;"></div>
                  </div>
                  <span class="sp-progress-text" id="sp-progress-text">Langkah 1 dari 6 (17%)</span>
                </div>
              </div>

              <div class="sp-header-right">
                <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" title="Buka Rangkaian di Simulator">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                  <span>Coba di Simulator</span>
                </a>
                <button class="btn-close-modal" onclick="closeModuleModal()" aria-label="Tutup Modul">✕</button>
              </div>
            </header>

            <!-- 2. Full-Screen Step Navigation Tabs Bar -->
            <nav class="sp-tabs-bar" role="tablist">
              <button class="sp-tab-item active" id="sp-tab-btn-1" onclick="goToSPStep(1)">
                <span class="tab-badge">1</span>
                <span>Kenali Seri & Paralel</span>
              </button>
              <button class="sp-tab-item" id="sp-tab-btn-2" onclick="goToSPStep(2)">
                <span class="tab-badge">2</span>
                <span>Eksplorasi Seri</span>
              </button>
              <button class="sp-tab-item" id="sp-tab-btn-3" onclick="goToSPStep(3)">
                <span class="tab-badge">3</span>
                <span>Eksplorasi Paralel</span>
              </button>
              <button class="sp-tab-item" id="sp-tab-btn-4" onclick="goToSPStep(4)">
                <span class="tab-badge">4</span>
                <span>Bandingkan & Klasifikasi</span>
              </button>
              <button class="sp-tab-item" id="sp-tab-btn-5" onclick="goToSPStep(5)">
                <span class="tab-badge">5</span>
                <span>Latihan Perhitungan</span>
              </button>
              <button class="sp-tab-item" id="sp-tab-btn-6" onclick="goToSPStep(6)">
                <span class="tab-badge">6</span>
                <span>Quiz & Simulator</span>
              </button>
            </nav>

            <!-- 3. Full-Screen Scrollable Body -->
            <main class="sp-fullscreen-body" id="sp-modal-body">
              
              <!-- ================================================================
                   LANGKAH 1: KENALI SERI & PARALEL
                   ================================================================ -->
              <div class="step-content-panel active" id="sp-panel-1">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 1 DARI 6 • KENALI SERI & PARALEL</span>
                  <h3 class="step-title">Dua Metode Fundamental Menghubungkan Komponen Listrik</h3>
                  <p class="step-desc">
                    Komponen elektronika dapat disusun secara <strong>Seri</strong> (satu jalur berurutan) atau <strong>Paralel</strong> (bercabang). <strong>Klik kedua kartu di bawah untuk mempelajari karakteristik dan perbedaan kuncinya:</strong>
                  </p>
                </div>

                <div class="sp-type-grid">
                  <!-- Card Seri -->
                  <div class="sp-type-card" id="sp-card-seri" onclick="exploreSPType('seri')">
                    <span class="sp-type-card-badge">JALUR TUNGGAL</span>
                    <h4 class="sp-type-card-title">RANGKAIAN SERI</h4>
                    
                    <!-- SVG Schematic Preview Seri -->
                    <div style="background: var(--color-bg-surface-secondary, #f8fafc); border-radius: 8px; padding: 12px; margin-bottom: 14px; border: 1px solid var(--color-border, #dce5f0);">
                      <svg viewBox="0 0 320 80" style="width: 100%; height: 75px; display: block;">
                        <rect x="20" y="24" width="30" height="32" rx="4" fill="#1e293b" stroke="#38bdf8" stroke-width="2"/>
                        <text x="35" y="44" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">12V</text>
                        <path d="M50 40 L90 40" stroke="#38bdf8" stroke-width="2.5"/>
                        <!-- R1 -->
                        <rect x="90" y="30" width="54" height="20" rx="3" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="117" y="44" fill="#facc15" font-size="11" font-weight="bold" text-anchor="middle">R1</text>
                        <path d="M144 40 L180 40" stroke="#38bdf8" stroke-width="2.5"/>
                        <!-- R2 -->
                        <rect x="180" y="30" width="54" height="20" rx="3" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="207" y="44" fill="#facc15" font-size="11" font-weight="bold" text-anchor="middle">R2</text>
                        <path d="M234 40 L280 40 L280 70 L20 70 L20 56" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                      </svg>
                    </div>

                    <ul class="sp-type-list">
                      <li><span>🛤️</span> <div><strong>Satu Jalur Arus:</strong> Seluruh muatan mengalir melewati setiap komponen secara berurutan tanpa percabangan.</div></li>
                      <li><span>⚡</span> <div><strong>Arus Sama:</strong> Kuat arus di setiap komponen identik (I<sub>tot</sub> = I<sub>1</sub> = I<sub>2</sub>).</div></li>
                      <li><span>🔻</span> <div><strong>Tegangan Terbagi:</strong> Tegangan sumber terbagi ke masing-masing beban (V<sub>s</sub> = V<sub>1</sub> + V<sub>2</sub>).</div></li>
                      <li><span>📈</span> <div><strong>Hambatan Bertambah:</strong> Hambatan total adalah penjumlahan langsung: R<sub>tot</sub> = R<sub>1</sub> + R<sub>2</sub>.</div></li>
                    </ul>
                  </div>

                  <!-- Card Paralel -->
                  <div class="sp-type-card" id="sp-card-paralel" onclick="exploreSPType('paralel')">
                    <span class="sp-type-card-badge" style="color: #34d399; background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3);">JALUR BERCABANG</span>
                    <h4 class="sp-type-card-title">RANGKAIAN PARALEL</h4>
                    
                    <!-- SVG Schematic Preview Paralel -->
                    <div style="background: var(--color-bg-surface-secondary, #f8fafc); border-radius: 8px; padding: 12px; margin-bottom: 14px; border: 1px solid var(--color-border, #dce5f0);">
                      <svg viewBox="0 0 320 80" style="width: 100%; height: 75px; display: block;">
                        <rect x="20" y="24" width="30" height="32" rx="4" fill="#1e293b" stroke="#34d399" stroke-width="2"/>
                        <text x="35" y="44" fill="#34d399" font-size="11" font-weight="bold" text-anchor="middle">12V</text>
                        <!-- Top branch -->
                        <path d="M50 40 L90 40 L90 20 L140 20" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <rect x="140" y="10" width="54" height="20" rx="3" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                        <text x="167" y="24" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">R1</text>
                        <path d="M194 20 L250 20 L250 40" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <!-- Bottom branch -->
                        <path d="M90 40 L90 60 L140 60" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <rect x="140" y="50" width="54" height="20" rx="3" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                        <text x="167" y="64" fill="#38bdf8" font-size="11" font-weight="bold" text-anchor="middle">R2</text>
                        <path d="M194 60 L250 60 L250 40 L250 74 L20 74 L20 56" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <!-- Nodes -->
                        <circle cx="90" cy="40" r="3.5" fill="#facc15"/>
                        <circle cx="250" cy="40" r="3.5" fill="#facc15"/>
                      </svg>
                    </div>

                    <ul class="sp-type-list">
                      <li><span>🔀</span> <div><strong>Lebih Dari Satu Jalur:</strong> Terdapat simpul percabangan sehingga muatan memiliki rute alternatif.</div></li>
                      <li><span>⚡</span> <div><strong>Tegangan Cabang Sama:</strong> Tiap cabang langsung terhubung ke tegangan sumber (V<sub>s</sub> = V<sub>1</sub> = V<sub>2</sub>).</div></li>
                      <li><span>🌊</span> <div><strong>Arus Terbagi:</strong> Arus total merupakan jumlah arus cabang: I<sub>tot</sub> = I<sub>1</sub> + I<sub>2</sub>.</div></li>
                      <li><span>📉</span> <div><strong>Hambatan Berkurang:</strong> Hambatan pengganti selalu lebih kecil dari hambatan cabang terkecil (1/R<sub>tot</sub> = 1/R<sub>1</sub> + 1/R<sub>2</sub>).</div></li>
                    </ul>
                  </div>
                </div>

                <!-- Exploration Status Indicator -->
                <div id="sp-type-status" style="margin-top: 14px; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; display: none; background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #a7f3d0;">
                  ✓ Kedua tipe rangkaian (Seri & Paralel) telah dipelajari! Silakan lanjutkan ke Langkah 2 untuk eksperimen interaktif rangkaian seri.
                </div>

                <!-- Collapsible Real-World Explanation -->
                <div class="collapsible-box" id="sp-collapsible-step1" style="margin-top: 16px;">
                  <button class="collapsible-header" onclick="toggleCollapsible('sp-collapsible-step1')">
                    <span>💡 Mengapa instalasi listrik rumah tangga menggunakan rangkaian paralel?</span>
                    <span class="collapsible-icon">▼</span>
                  </button>
                  <div class="collapsible-body">
                    Instalasi kelistrikan di rumah selalu memakai rangkaian paralel karena:
                    <ol style="margin: 8px 0 0 20px; padding: 0; line-height: 1.6;">
                      <li><strong>Kemandirian Beban:</strong> Setiap lampu atau alat elektronik memiliki saklar sendiri. Jika satu lampu dimatikan atau putus, peralatan lain di cabang yang berbeda tetap bekerja normal.</li>
                      <li><strong>Tegangan Seragam:</strong> Semua stopkontak mendapatkan tegangan nominal penuh (220V AC atau 12V DC pada sistem laboratorium) tanpa mengalami penurunan tegangan saat alat lain dinyalakan.</li>
                    </ol>
                  </div>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 2: EKSPLORASI RANGKAIAN SERI
                   ================================================================ -->
              <div class="step-content-panel" id="sp-panel-2">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 2 DARI 6 • EKSPLORASI RANGKAIAN SERI</span>
                  <h3 class="step-title">Arus Sama & Tegangan Terbagi pada Rangkaian Seri</h3>
                  <p class="step-desc">
                    Amati bagaimana muatan listrik mengalir melalui satu loop tunggal. Klik komponen untuk memeriksa parameternya, buka saklar untuk menguji konsep continuous path, dan perhatikan tegangan jatuh pada setiap resistor.
                  </p>
                </div>

                <!-- 2-Column Workbench (Desktop) -->
                <div class="sp-workbench-layout">
                  
                  <!-- Left: Large Circuit Schematic & Inspector -->
                  <div class="sp-workbench-left">
                    <div class="sp-schematic-container">
                      <svg id="sp-series-svg" class="sp-schematic-svg" viewBox="0 0 720 220">
                        <!-- Single Continuous Loop Path -->
                        <path id="sp-series-wire" class="sp-wire current-flow" d="M 80 45 L 180 45 M 270 45 L 360 45 M 450 45 L 530 45 M 580 45 L 650 45 L 650 175 L 80 175 L 80 145" />

                        <!-- DC Battery Symbol (Left) -->
                        <g id="sp-series-battery-elem" class="sp-component-box" transform="translate(80, 95)" onclick="inspectSPElement('battery_series')">
                          <rect x="-35" y="-30" width="70" height="60" rx="8" fill="#0f172a" stroke="#ef4444" stroke-width="1.5" opacity="0.4"/>
                          <line x1="-24" y1="-10" x2="24" y2="-10" stroke="#ef4444" stroke-width="4.5" stroke-linecap="round"/>
                          <line x1="-14" y1="8" x2="14" y2="8" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                          <text x="-32" y="-7" fill="#ef4444" font-size="14" font-weight="bold">+</text>
                          <text x="-32" y="12" fill="#94a3b8" font-size="14" font-weight="bold">-</text>
                          <text id="sp-series-vs-label" x="0" y="-38" fill="#f8fafc" font-size="13" font-weight="bold" text-anchor="middle">Vs = 12V</text>
                        </g>

                        <!-- Resistor 1 (R1) -->
                        <g id="sp-resistor1-box" class="sp-component-box selected" transform="translate(180, 30)" onclick="inspectSPElement('R1_series')">
                          <rect x="0" y="0" width="90" height="30" rx="4" fill="#0f172a" stroke="#38bdf8" stroke-width="2.5"/>
                          <text x="45" y="19" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-r1-label">R1 = 6 Ω</text>
                          <!-- VR1 Drop Bracket Label -->
                          <text x="45" y="-10" fill="#facc15" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-vr1-label">VR1 = 8.00 V</text>
                        </g>

                        <!-- Resistor 2 (R2) -->
                        <g id="sp-resistor2-box" class="sp-component-box" transform="translate(360, 30)" onclick="inspectSPElement('R2_series')">
                          <rect x="0" y="0" width="90" height="30" rx="4" fill="#0f172a" stroke="#38bdf8" stroke-width="2.5"/>
                          <text x="45" y="19" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-r2-label">R2 = 3 Ω</text>
                          <!-- VR2 Drop Bracket Label -->
                          <text x="45" y="-10" fill="#facc15" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-vr2-label">VR2 = 4.00 V</text>
                        </g>

                        <!-- Switch with REAL PHYSICAL GAP -->
                        <g id="sp-series-switch-graphic" class="sp-component-box" transform="translate(530, 45)" onclick="toggleSeriesSwitch()">
                          <circle cx="0" cy="0" r="5" fill="#38bdf8" />
                          <circle cx="50" cy="0" r="5" fill="#38bdf8" />
                          <line id="sp-series-switch-arm" x1="0" y1="0" x2="50" y2="0" stroke="#10b981" stroke-width="4" stroke-linecap="round"/>
                          <text x="25" y="-14" fill="#94a3b8" font-size="11" font-weight="600" text-anchor="middle">Saklar Seri</text>
                          <text id="sp-series-gap-text" class="sp-gap-indicator" x="25" y="24" text-anchor="middle" style="display: none;">[ GAP / TERPUTUS ]</text>
                        </g>

                        <!-- Inline Current Badge on Return Line -->
                        <g transform="translate(365, 175)">
                          <rect x="-70" y="-14" width="140" height="28" rx="14" fill="#1e293b" stroke="#10b981" stroke-width="2"/>
                          <text id="sp-series-current-badge" x="0" y="5" fill="#34d399" font-size="12" font-weight="bold" text-anchor="middle">I = 1.333 A</text>
                        </g>
                      </svg>

                      <!-- Switch and Helper Controls -->
                      <div class="sp-toggle-bar">
                        <button id="sp-series-switch-btn" class="sp-branch-btn active" onclick="toggleSeriesSwitch()">
                          <span id="sp-switch-dot">🟢</span>
                          <span id="sp-switch-text">Saklar Seri: TERTUTUP (Arus Mengalir)</span>
                        </button>
                        <span style="font-size: 0.82rem; color: var(--color-text-muted, #64748b); align-self: center;">💡 Klik komponen pada diagram untuk inspeksi detail.</span>
                      </div>
                    </div>

                    <!-- Element Context Inspector -->
                    <div class="sp-inspector-panel" id="sp-series-inspect-panel">
                      <div class="sp-inspector-badge">
                        <span>🔍</span> <span id="sp-series-inspect-title">INSPEKTOR: RESISTOR R1</span>
                      </div>
                      <div class="sp-inspector-grid">
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Hambatan (R)</div>
                          <div class="sp-inspector-val" id="sp-insp-r1-r">6 Ω</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Beda Potensial (V)</div>
                          <div class="sp-inspector-val" id="sp-insp-r1-v" style="color: #facc15;">8.00 V</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Kuat Arus (I)</div>
                          <div class="sp-inspector-val" id="sp-insp-r1-i" style="color: #34d399;">1.333 A</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Daya Disipasi (P)</div>
                          <div class="sp-inspector-val" id="sp-insp-r1-p" style="color: #38bdf8;">10.67 W</div>
                        </div>
                      </div>
                      <p id="sp-series-insp-desc" style="font-size: 0.84rem; color: var(--color-text-secondary, #475569); margin: 4px 0 0; line-height: 1.5;">
                        Resistor R1 menerima kuat arus penuh (1.333 A) yang sama persis dengan R2. Karena nilai hambatannya (6 Ω) adalah 2 kali lipat dari R2 (3 Ω), R1 menyerap dua per tiga (8 V) dari total tegangan sumber 12 V.
                      </p>
                    </div>

                    <!-- Prediction Exercise -->
                    <div class="sp-whatif-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase;">❓ Uji Prediksi Konseptual Seri</span>
                      <p style="font-size: 0.92rem; margin: 0; color: var(--color-text-primary, #0f172a); line-height: 1.5;">
                        Jika nilai hambatan <strong>R2 dinaikkan</strong> sementara tegangan sumber tetap (12V), apa yang akan terjadi pada <strong>kuat arus total (I)</strong>?
                      </p>
                      <div class="prediction-options">
                        <button class="prediction-btn" id="pred-sp-1" onclick="answerSPPrediction(1)">A. Arus Total Bertambah (Naik)</button>
                        <button class="prediction-btn" id="pred-sp-2" onclick="answerSPPrediction(2)">B. Arus Total Berkurang (Turun)</button>
                        <button class="prediction-btn" id="pred-sp-3" onclick="answerSPPrediction(3)">C. Arus Total Tetap Sama</button>
                      </div>
                      <div class="prediction-feedback" id="pred-sp-feedback" style="display: none;"></div>
                    </div>
                  </div>

                  <!-- Right: Controls & Live Formula Substitution -->
                  <div class="sp-workbench-right">
                    <!-- Controls Card -->
                    <div class="sp-controls-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #38bdf8; text-transform: uppercase;">Eksperimen Nilai Seri</span>
                      
                      <!-- Resistor R1 -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Resistor 1 (R1):</span>
                          <span id="txt-series-r1" style="font-weight: 700; color: #38bdf8;">6 Ω</span>
                        </div>
                        <input type="range" min="1" max="50" step="1" value="6" id="slider-series-r1" oninput="handleSeriesInput('r1', this.value)" style="width: 100%;">
                      </div>

                      <!-- Resistor R2 -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Resistor 2 (R2):</span>
                          <span id="txt-series-r2" style="font-weight: 700; color: #38bdf8;">3 Ω</span>
                        </div>
                        <input type="range" min="1" max="50" step="1" value="3" id="slider-series-r2" oninput="handleSeriesInput('r2', this.value)" style="width: 100%;">
                      </div>

                      <!-- Source Voltage Vs -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Tegangan Sumber (Vs):</span>
                          <span id="txt-series-vs" style="font-weight: 700; color: #ef4444;">12 V</span>
                        </div>
                        <input type="range" min="1" max="24" step="1" value="12" id="slider-series-vs" oninput="handleSeriesInput('vs', this.value)" style="width: 100%;">
                      </div>
                    </div>

                    <!-- Live Math Formula Card -->
                    <div class="sp-math-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: var(--color-text-muted, #64748b); text-transform: uppercase;">Substitusi Rumus Seri Matematis</span>
                      <div id="sp-series-math-display" style="font-family: var(--font-mono, monospace); font-size: 0.95rem; line-height: 1.65; color: var(--color-text-secondary, #475569);">
                        R<sub>tot</sub> = R1 + R2 = 6 + 3 = <strong>9 Ω</strong><br>
                        I = Vs / R<sub>tot</sub> = 12 / 9 ≈ <strong>1.333 A</strong><br>
                        V<sub>R1</sub> = I × R1 = 1.333 × 6 = <strong>8.00 V</strong><br>
                        V<sub>R2</sub> = I × R2 = 1.333 × 3 = <strong>4.00 V</strong><br>
                        <span style="color: #34d399;">V<sub>R1</sub> + V<sub>R2</sub> = 8.00 + 4.00 = 12.00 V (Sesuai Hukum Tegangan Kirchhoff)</span>
                      </div>
                    </div>

                    <!-- Real-Time Educational Explanation -->
                    <div class="sp-explanation-panel" id="sp-series-why-panel">
                      <strong>💡 Analisis Fisika Rangkaian:</strong><br>
                      Pada rangkaian seri, tidak ada percabangan kawat. Seluruh elektron dipaksa mengalir melalui jalur yang sama secara beruntun. Akibatnya:
                      <ul style="margin: 6px 0 0 16px; padding: 0;">
                        <li>Kuat arus di setiap titik selalu identik: <strong>I<sub>R1</sub> = I<sub>R2</sub> = 1.333 A</strong>.</li>
                        <li>Tegangan sumber terbagi proporsional terhadap resistansi beban.</li>
                        <li>Jika saklar dibuka atau salah satu kawat diputus, seluruh sirkuit mati seketika (I = 0 A).</li>
                      </ul>
                    </div>
                  </div>

                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 3: EKSPLORASI RANGKAIAN PARALEL
                   ================================================================ -->
              <div class="step-content-panel" id="sp-panel-3">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 3 DARI 6 • EKSPLORASI RANGKAIAN PARALEL</span>
                  <h3 class="step-title">Tegangan Cabang Sama & Arus Terbagi pada Paralel</h3>
                  <p class="step-desc">
                    Amati bagaimana arus dari baterai terbagi ke dua cabang independen. Klik komponen untuk analisis interaktif, dan cobalah <strong>memutus salah satu cabang</strong> untuk membuktikan bahwa cabang lainnya tetap bekerja normal.
                  </p>
                </div>

                <!-- 2-Column Workbench (Desktop) -->
                <div class="sp-workbench-layout">
                  
                  <!-- Left: Physically Correct Parallel Schematic & Inspector -->
                  <div class="sp-workbench-left">
                    <div class="sp-schematic-container">
                      <svg id="sp-parallel-svg" class="sp-schematic-svg" viewBox="0 0 740 260">
                        <!-- Common Supply Trunk from Battery (+) to Junction Node A -->
                        <path id="sp-par-trunk-supply" class="sp-wire current-flow" d="M 70 50 L 170 50" />
                        
                        <!-- Common Supply Current Badge (Itotal) placed on MAIN PATH only -->
                        <g transform="translate(120, 26)">
                          <rect x="-44" y="-12" width="88" height="24" rx="12" fill="#1e293b" stroke="#facc15" stroke-width="2"/>
                          <text id="sp-par-itot-badge" x="0" y="4" fill="#facc15" font-size="11" font-weight="bold" text-anchor="middle">Itot = 3.50 A</text>
                        </g>

                        <!-- Input Junction Node A -->
                        <circle cx="170" cy="50" r="6" fill="#facc15" stroke="#0f172a" stroke-width="2"/>
                        <text x="170" y="72" fill="#facc15" font-size="10" font-weight="bold" text-anchor="middle">Simpul A</text>

                        <!-- ================= Branch 1 (Top) ================= -->
                        <!-- Path: Node A -> up -> switch -> R1 -> Node B -->
                        <path id="sp-par-wire-b1" class="sp-wire current-flow" d="M 170 50 L 170 25 L 230 25 M 280 25 L 370 25 M 460 25 L 540 25 L 540 50" />
                        
                        <!-- Switch 1 (Physical Gap Demonstration) -->
                        <g id="sp-sw1-group" class="sp-component-box" transform="translate(230, 25)" onclick="toggleParallelBranch(1)">
                          <circle cx="0" cy="0" r="4.5" fill="#38bdf8" />
                          <circle cx="50" cy="0" r="4.5" fill="#38bdf8" />
                          <line id="sp-sw1-blade" x1="0" y1="0" x2="50" y2="0" stroke="#10b981" stroke-width="4" stroke-linecap="round"/>
                          <text id="sp-sw1-gap-text" class="sp-gap-indicator" x="25" y="-12" text-anchor="middle" style="display: none;">[ GAP / TERPUTUS ]</text>
                        </g>

                        <!-- R1 Box -->
                        <g id="sp-par-r1-box" class="sp-component-box selected" transform="translate(370, 10)" onclick="inspectSPElement('R1_parallel')">
                          <rect x="0" y="0" width="90" height="30" rx="4" fill="#0f172a" stroke="#38bdf8" stroke-width="2.5"/>
                          <text x="45" y="19" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-par-r1-label">R1 = 6 Ω</text>
                          <text x="45" y="-8" fill="#f8fafc" font-size="11" font-weight="bold" text-anchor="middle" id="sp-svg-par-v1-label">V1 = 12.00 V</text>
                          <text x="45" y="44" fill="#34d399" font-size="11" font-weight="bold" text-anchor="middle" id="sp-svg-par-i1-label">I1 = 2.00 A</text>
                        </g>

                        <!-- ================= Branch 2 (Bottom) ================= -->
                        <!-- Path: Node A -> down -> switch -> R2 -> Node B -->
                        <path id="sp-par-wire-b2" class="sp-wire current-flow" d="M 170 50 L 170 115 L 230 115 M 280 115 L 370 115 M 460 115 L 540 115 L 540 50" />
                        
                        <!-- Switch 2 (Physical Gap Demonstration) -->
                        <g id="sp-sw2-group" class="sp-component-box" transform="translate(230, 115)" onclick="toggleParallelBranch(2)">
                          <circle cx="0" cy="0" r="4.5" fill="#38bdf8" />
                          <circle cx="50" cy="0" r="4.5" fill="#38bdf8" />
                          <line id="sp-sw2-blade" x1="0" y1="0" x2="50" y2="0" stroke="#10b981" stroke-width="4" stroke-linecap="round"/>
                          <text id="sp-sw2-gap-text" class="sp-gap-indicator" x="25" y="-12" text-anchor="middle" style="display: none;">[ GAP / TERPUTUS ]</text>
                        </g>

                        <!-- R2 Box -->
                        <g id="sp-par-r2-box" class="sp-component-box" transform="translate(370, 100)" onclick="inspectSPElement('R2_parallel')">
                          <rect x="0" y="0" width="90" height="30" rx="4" fill="#0f172a" stroke="#38bdf8" stroke-width="2.5"/>
                          <text x="45" y="19" fill="#38bdf8" font-size="12" font-weight="bold" text-anchor="middle" id="sp-svg-par-r2-label">R2 = 8 Ω</text>
                          <text x="45" y="-8" fill="#f8fafc" font-size="11" font-weight="bold" text-anchor="middle" id="sp-svg-par-v2-label">V2 = 12.00 V</text>
                          <text x="45" y="44" fill="#34d399" font-size="11" font-weight="bold" text-anchor="middle" id="sp-svg-par-i2-label">I2 = 1.50 A</text>
                        </g>

                        <!-- Output Junction Node B -->
                        <circle cx="540" cy="50" r="6" fill="#facc15" stroke="#0f172a" stroke-width="2"/>
                        <text x="540" y="72" fill="#facc15" font-size="10" font-weight="bold" text-anchor="middle">Simpul B</text>

                        <!-- Common Return Trunk from Node B back to Battery (-) -->
                        <path id="sp-par-trunk-return" class="sp-wire current-flow" d="M 540 50 L 640 50 L 640 210 L 70 210 L 70 170" />

                        <!-- DC Battery Symbol (Left) -->
                        <g id="sp-parallel-battery-elem" class="sp-component-box" transform="translate(70, 110)" onclick="inspectSPElement('battery_parallel')">
                          <rect x="-32" y="-35" width="64" height="70" rx="8" fill="#0f172a" stroke="#ef4444" stroke-width="1.5" opacity="0.4"/>
                          <line x1="-24" y1="-10" x2="24" y2="-10" stroke="#ef4444" stroke-width="4.5" stroke-linecap="round"/>
                          <line x1="-14" y1="8" x2="14" y2="8" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
                          <text x="-30" y="-7" fill="#ef4444" font-size="14" font-weight="bold">+</text>
                          <text x="-30" y="12" fill="#94a3b8" font-size="14" font-weight="bold">-</text>
                          <text id="sp-par-vs-label" x="0" y="-42" fill="#f8fafc" font-size="13" font-weight="bold" text-anchor="middle">Vs = 12V</text>
                        </g>
                      </svg>

                      <!-- Laboratory Branch Switches -->
                      <div class="sp-toggle-bar">
                        <button id="sp-btn-branch1" class="sp-branch-btn active" onclick="toggleParallelBranch(1)">
                          <span id="sp-b1-dot">🟢</span>
                          <span id="sp-b1-text">Cabang 1 (R1): AKTIF</span>
                        </button>
                        <button id="sp-btn-branch2" class="sp-branch-btn active" onclick="toggleParallelBranch(2)">
                          <span id="sp-b2-dot">🟢</span>
                          <span id="sp-b2-text">Cabang 2 (R2): AKTIF</span>
                        </button>
                      </div>
                    </div>

                    <!-- Element Context Inspector -->
                    <div class="sp-inspector-panel" id="sp-parallel-inspect-panel">
                      <div class="sp-inspector-badge">
                        <span>🔍</span> <span id="sp-parallel-inspect-title">INSPEKTOR: CABANG 1 (RESISTOR R1)</span>
                      </div>
                      <div class="sp-inspector-grid">
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Hambatan (R)</div>
                          <div class="sp-inspector-val" id="sp-insp-par-r">6 Ω</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Tegangan Cabang (V)</div>
                          <div class="sp-inspector-val" id="sp-insp-par-v" style="color: #facc15;">12.00 V</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Arus Cabang (I)</div>
                          <div class="sp-inspector-val" id="sp-insp-par-i" style="color: #34d399;">2.000 A</div>
                        </div>
                        <div class="sp-inspector-item">
                          <div class="sp-inspector-label">Daya Disipasi (P)</div>
                          <div class="sp-inspector-val" id="sp-insp-par-p" style="color: #38bdf8;">24.00 W</div>
                        </div>
                      </div>
                      <p id="sp-parallel-insp-desc" style="font-size: 0.84rem; color: var(--color-text-secondary, #475569); margin: 4px 0 0; line-height: 1.5;">
                        Cabang 1 terhubung langsung ke terminal baterai melalui Simpul A dan Simpul B, sehingga mendapatkan tegangan penuh 12 V. Sesuai I1 = Vs / R1 = 12 / 6 = 2.00 A.
                      </p>
                    </div>

                    <!-- "Bagaimana Jika...?" What-If Prediction Section -->
                    <div class="sp-whatif-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase;">🔮 Mode Eksplorasi "Bagaimana Jika...?"</span>
                      <p style="font-size: 0.92rem; margin: 0; color: var(--color-text-primary, #0f172a); line-height: 1.5;">
                        Bagaimana jika nilai <strong>R1 diubah dari 6 Ω menjadi 12 Ω</strong> sementara tegangan sumber tetap 12 V? Apa yang terjadi pada arus cabang <strong>I1</strong>?
                      </p>
                      <div class="prediction-options">
                        <button class="prediction-btn" id="whatif-sp-1" onclick="answerSPWhatIf(1, 'naik')">A. Arus I1 Bertambah (Naik)</button>
                        <button class="prediction-btn" id="whatif-sp-2" onclick="answerSPWhatIf(1, 'turun')">B. Arus I1 Berkurang (Turun)</button>
                        <button class="prediction-btn" id="whatif-sp-3" onclick="answerSPWhatIf(1, 'tetap')">C. Arus I1 Tetap Sama</button>
                      </div>
                      <div class="prediction-feedback" id="whatif-sp-feedback" style="display: none;"></div>
                    </div>
                  </div>

                  <!-- Right: Controls & Live Formula Substitution -->
                  <div class="sp-workbench-right">
                    <!-- Controls Card -->
                    <div class="sp-controls-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #34d399; text-transform: uppercase;">Eksperimen Nilai Paralel</span>
                      
                      <!-- Resistor R1 -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Resistor Cabang 1 (R1):</span>
                          <span id="txt-parallel-r1" style="font-weight: 700; color: #38bdf8;">6 Ω</span>
                        </div>
                        <input type="range" min="1" max="50" step="1" value="6" id="slider-parallel-r1" oninput="handleParallelInput('r1', this.value)" style="width: 100%;">
                      </div>

                      <!-- Resistor R2 -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Resistor Cabang 2 (R2):</span>
                          <span id="txt-parallel-r2" style="font-weight: 700; color: #38bdf8;">8 Ω</span>
                        </div>
                        <input type="range" min="1" max="50" step="1" value="8" id="slider-parallel-r2" oninput="handleParallelInput('r2', this.value)" style="width: 100%;">
                      </div>

                      <!-- Source Voltage Vs -->
                      <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 4px;">
                          <span>Tegangan Sumber (Vs):</span>
                          <span id="txt-parallel-vs" style="font-weight: 700; color: #ef4444;">12 V</span>
                        </div>
                        <input type="range" min="1" max="24" step="1" value="12" id="slider-parallel-vs" oninput="handleParallelInput('vs', this.value)" style="width: 100%;">
                      </div>
                    </div>

                    <!-- Live Math Formula Card -->
                    <div class="sp-math-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: var(--color-text-muted, #64748b); text-transform: uppercase;">Substitusi Rumus Paralel Matematis</span>
                      <div id="sp-parallel-math-display" style="font-family: var(--font-mono, monospace); font-size: 0.95rem; line-height: 1.65; color: var(--color-text-secondary, #475569);">
                        I<sub>1</sub> = Vs / R1 = 12 / 6 = <strong>2.000 A</strong><br>
                        I<sub>2</sub> = Vs / R2 = 12 / 8 = <strong>1.500 A</strong><br>
                        I<sub>tot</sub> = I<sub>1</sub> + I<sub>2</sub> = 2.000 + 1.500 = <strong>3.500 A</strong><br>
                        1/R<sub>eq</sub> = 1/6 + 1/8 = 7/24<br>
                        R<sub>eq</sub> = (6 × 8) / (6 + 8) ≈ <strong>3.429 Ω</strong>
                      </div>
                    </div>

                    <!-- Real-Time Educational Explanation -->
                    <div class="sp-explanation-panel" id="sp-parallel-why-panel">
                      <strong>💡 Analisis Fisika Rangkaian:</strong><br>
                      Kedua cabang paralel terhubung langsung ke Simpul A (+) dan Simpul B (-), sehingga <strong>tegangan kedua cabang identik (V1 = V2 = 12 V)</strong>.<br>
                      Nilai hambatan pengganti total (<strong>Req ≈ 3.43 Ω</strong>) selalu <em>lebih kecil</em> daripada hambatan cabang terkecil (6 Ω) karena percabangan membuka jalur aliran muatan ekstra.
                    </div>
                  </div>

                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 4: BANDINGKAN SERI VS PARALEL
                   ================================================================ -->
              <div class="step-content-panel" id="sp-panel-4">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 4 DARI 6 • MATRIKS PERBANDINGAN & KLASIFIKASI</span>
                  <h3 class="step-title">Perbandingan Karakteristik & Uji Klasifikasi Rangkaian</h3>
                  <p class="step-desc">
                    Pelajari tabel komparasi parameter di bawah ini, gunakan tombol sorot jalur untuk memvisualisasikan arah arus, lalu jawab 3 soal klasifikasi konfigurasi rangkaian.
                  </p>
                </div>

                <!-- Toggle Path Highlight Button -->
                <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                  <button id="sp-btn-highlight-path" class="sp-branch-btn" onclick="toggleSPPathHighlight()">
                    <span>✨ Tampilkan Jalur Arus</span>
                  </button>
                </div>

                <!-- Comparison Table -->
                <div class="sp-comparison-table-wrapper">
                  <table class="sp-comparison-table">
                    <thead>
                      <tr>
                        <th>Parameter</th>
                        <th>Rangkaian Seri</th>
                        <th>Rangkaian Paralel</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Jalur Arus</td>
                        <td>1 lintasan tunggal tanpa percabangan</td>
                        <td>Terdapat 2 atau lebih cabang lintasan</td>
                      </tr>
                      <tr>
                        <td>Kuat Arus (I)</td>
                        <td>Sama di setiap beban (I<sub>tot</sub> = I<sub>1</sub> = I<sub>2</sub>)</td>
                        <td>Terbagi ke cabang (I<sub>tot</sub> = I<sub>1</sub> + I<sub>2</sub>)</td>
                      </tr>
                      <tr>
                        <td>Tegangan (V)</td>
                        <td>Terbagi pada tiap beban (V<sub>s</sub> = V<sub>1</sub> + V<sub>2</sub>)</td>
                        <td>Sama di setiap cabang (V<sub>s</sub> = V<sub>1</sub> = V<sub>2</sub>)</td>
                      </tr>
                      <tr>
                        <td>Hambatan Pengganti</td>
                        <td>Bertambah besar (R<sub>tot</sub> = R<sub>1</sub> + R<sub>2</sub>)</td>
                        <td>Makin kecil (1/R<sub>tot</sub> = 1/R<sub>1</sub> + 1/R<sub>2</sub>)</td>
                      </tr>
                      <tr>
                        <td>Jika 1 Jalur Putus</td>
                        <td>Seluruh rangkaian mati total (I = 0)</td>
                        <td>Cabang lain tetap mengalirkan arus normal</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- 3 Classification Challenges -->
                <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--color-text-primary, #0f172a); margin: 24px 0 12px;">Uji Klasifikasi Rangkaian:</h4>
                <div class="sp-classify-grid">
                  
                  <!-- Challenge 1 -->
                  <div class="sp-classify-card" id="sp-classify-1">
                    <div class="sp-classify-header">
                      <span style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">Kasus 1: Dua Beban dalam Satu Lintasan</span>
                    </div>
                    <div style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); padding: 10px; border-radius: 8px; text-align: center;">
                      <svg viewBox="0 0 340 60" style="width: 100%; max-width: 320px; height: 50px;">
                        <rect x="20" y="15" width="26" height="30" rx="3" fill="#1e293b" stroke="#ef4444" stroke-width="2"/>
                        <path d="M46 30 L100 30" stroke="#38bdf8" stroke-width="2.5"/>
                        <circle cx="120" cy="30" r="14" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="120" y="34" fill="#facc15" font-size="10" font-weight="bold" text-anchor="middle">L1</text>
                        <path d="M134 30 L200 30" stroke="#38bdf8" stroke-width="2.5"/>
                        <circle cx="220" cy="30" r="14" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="220" y="34" fill="#facc15" font-size="10" font-weight="bold" text-anchor="middle">L2</text>
                        <path d="M234 30 L300 30 L300 55 L20 55 L20 45" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                      </svg>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
                    <div class="sp-classify-options">
                      <button class="sp-classify-btn" id="btn-clf-1-seri" onclick="answerSPClassify(1, 'seri')">Rangkaian Seri</button>
                      <button class="sp-classify-btn" id="btn-clf-1-paralel" onclick="answerSPClassify(1, 'paralel')">Rangkaian Paralel</button>
                    </div>
                    <div class="sp-classify-feedback" id="feedback-clf-1"></div>
                  </div>

                  <!-- Challenge 2 -->
                  <div class="sp-classify-card" id="sp-classify-2">
                    <div class="sp-classify-header">
                      <span style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">Kasus 2: Dua Beban Terhubung ke Dua Simpul Bersama</span>
                    </div>
                    <div style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); padding: 10px; border-radius: 8px; text-align: center;">
                      <svg viewBox="0 0 340 70" style="width: 100%; max-width: 320px; height: 60px;">
                        <rect x="20" y="20" width="26" height="30" rx="3" fill="#1e293b" stroke="#ef4444" stroke-width="2"/>
                        <path d="M46 35 L90 35 L90 18 L150 18" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <circle cx="165" cy="18" r="12" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="165" y="22" fill="#facc15" font-size="9" font-weight="bold" text-anchor="middle">L1</text>
                        <path d="M177 18 L250 18 L250 35" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <path d="M90 35 L90 52 L150 52" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <circle cx="165" cy="52" r="12" fill="#0f172a" stroke="#facc15" stroke-width="2"/>
                        <text x="165" y="56" fill="#facc15" font-size="9" font-weight="bold" text-anchor="middle">L2</text>
                        <path d="M177 52 L250 52 L250 35 L250 68 L20 68 L20 50" stroke="#34d399" stroke-width="2.5" fill="none"/>
                        <circle cx="90" cy="35" r="3.5" fill="#facc15"/>
                        <circle cx="250" cy="35" r="3.5" fill="#facc15"/>
                      </svg>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
                    <div class="sp-classify-options">
                      <button class="sp-classify-btn" id="btn-clf-2-seri" onclick="answerSPClassify(2, 'seri')">Rangkaian Seri</button>
                      <button class="sp-classify-btn" id="btn-clf-2-paralel" onclick="answerSPClassify(2, 'paralel')">Rangkaian Paralel</button>
                    </div>
                    <div class="sp-classify-feedback" id="feedback-clf-2"></div>
                  </div>

                  <!-- Challenge 3 -->
                  <div class="sp-classify-card" id="sp-classify-3">
                    <div class="sp-classify-header">
                      <span style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">Kasus 3: Cabang Paralel dengan Dua Resistor Berurutan</span>
                    </div>
                    <div style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); padding: 10px; border-radius: 8px; text-align: center;">
                      <svg viewBox="0 0 350 70" style="width: 100%; max-width: 340px; height: 60px;">
                        <rect x="15" y="20" width="24" height="30" rx="3" fill="#1e293b" stroke="#ef4444" stroke-width="2"/>
                        <path d="M39 35 L80 35 L80 18 L130 18" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                        <rect x="130" y="10" width="38" height="16" rx="2" fill="#0f172a" stroke="#38bdf8" stroke-width="1.5"/>
                        <path d="M168 18 L270 18 L270 35" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                        <!-- Bottom branch with two series resistors -->
                        <path d="M80 35 L80 52 L110 52" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                        <rect x="110" y="44" width="38" height="16" rx="2" fill="#0f172a" stroke="#facc15" stroke-width="1.5"/>
                        <path d="M148 52 L175 52" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                        <rect x="175" y="44" width="38" height="16" rx="2" fill="#0f172a" stroke="#facc15" stroke-width="1.5"/>
                        <path d="M213 52 L270 52 L270 35 L270 68 L15 68 L15 50" stroke="#38bdf8" stroke-width="2.5" fill="none"/>
                        <circle cx="80" cy="35" r="3.5" fill="#facc15"/>
                        <circle cx="270" cy="35" r="3.5" fill="#facc15"/>
                      </svg>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--color-text-secondary, #475569); margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
                    <div class="sp-classify-options">
                      <button class="sp-classify-btn" id="btn-clf-3-seri" onclick="answerSPClassify(3, 'seri')">Seri Murni</button>
                      <button class="sp-classify-btn" id="btn-clf-3-paralel" onclick="answerSPClassify(3, 'paralel')">Paralel Murni</button>
                      <button class="sp-classify-btn" id="btn-clf-3-campuran" onclick="answerSPClassify(3, 'campuran')">Campuran (Seri-Paralel)</button>
                    </div>
                    <div class="sp-classify-feedback" id="feedback-clf-3"></div>
                  </div>

                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 5: LATIHAN PERHITUNGAN
                   ================================================================ -->
              <div class="step-content-panel" id="sp-panel-5">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 5 DARI 6 • LATIHAN PERHITUNGAN</span>
                  <h3 class="step-title">Uji Kemampuan Analisis Numerik Rangkaian</h3>
                  <p class="step-desc">
                    Kerjakan 4 soal perhitungan di bawah ini berdasarkan prinsip pembagian arus, tegangan, dan hambatan total. Klik tombol <strong>Periksa Jawaban</strong> setelah mengisi.
                  </p>
                </div>

                <!-- Soal 1: Seri Rtot & Itot -->
                <div class="practice-problem-card">
                  <div style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">SOAL 1 • Hambatan & Arus Seri</div>
                  <p style="font-size: 0.9rem; color: #e2e8f0; margin: 4px 0 8px;">
                    Sebuah sumber tegangan DC <strong>Vs = 12 V</strong> dihubungkan ke dua buah resistor <strong>R1 = 6 Ω</strong> dan <strong>R2 = 3 Ω</strong> secara <strong>seri</strong>.
                  </p>
                  <div class="sp-practice-inputs">
                    <div class="sp-input-cell">
                      <label>Hambatan Total (Rtot) [Ω]:</label>
                      <input type="number" step="any" id="sp-input-rtot-series" class="practice-num-input" placeholder="contoh: 9">
                    </div>
                    <div class="sp-input-cell">
                      <label>Kuat Arus Total (Itot) [A]:</label>
                      <input type="number" step="any" id="sp-input-itot-series" class="practice-num-input" placeholder="contoh: 1.33">
                    </div>
                  </div>
                  <div id="sp-feedback-q1" class="quiz-feedback-box" style="display: none; margin-top: 8px;"></div>
                </div>

                <!-- Soal 2: Seri Voltage Drop -->
                <div class="practice-problem-card" style="margin-top: 14px;">
                  <div style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">SOAL 2 • Pembagian Tegangan Seri</div>
                  <p style="font-size: 0.9rem; color: #e2e8f0; margin: 4px 0 8px;">
                    Pada rangkaian seri Soal 1 di atas (Vs = 12 V, R1 = 6 Ω, R2 = 3 Ω, I ≈ 1.333 A), hitung tegangan jatuh pada masing-masing resistor:
                  </p>
                  <div class="sp-practice-inputs">
                    <div class="sp-input-cell">
                      <label>Tegangan pada R1 (VR1) [Volt]:</label>
                      <input type="number" step="any" id="sp-input-vr1-series" class="practice-num-input" placeholder="contoh: 8">
                    </div>
                    <div class="sp-input-cell">
                      <label>Tegangan pada R2 (VR2) [Volt]:</label>
                      <input type="number" step="any" id="sp-input-vr2-series" class="practice-num-input" placeholder="contoh: 4">
                    </div>
                  </div>
                  <div id="sp-feedback-q2" class="quiz-feedback-box" style="display: none; margin-top: 8px;"></div>
                </div>

                <!-- Soal 3: Paralel Current -->
                <div class="practice-problem-card" style="margin-top: 14px;">
                  <div style="font-size: 0.88rem; font-weight: 800; color: #34d399;">SOAL 3 • Arus Percabangan Paralel</div>
                  <p style="font-size: 0.9rem; color: #e2e8f0; margin: 4px 0 8px;">
                    Sumber tegangan <strong>Vs = 12 V</strong> dihubungkan secara <strong>paralel</strong> dengan dua cabang resistor: <strong>R1 = 6 Ω</strong> dan <strong>R2 = 8 Ω</strong>.
                  </p>
                  <div class="sp-practice-inputs">
                    <div class="sp-input-cell">
                      <label>Arus Cabang 1 (I1) [A]:</label>
                      <input type="number" step="any" id="sp-input-i1-parallel" class="practice-num-input" placeholder="contoh: 2">
                    </div>
                    <div class="sp-input-cell">
                      <label>Arus Cabang 2 (I2) [A]:</label>
                      <input type="number" step="any" id="sp-input-i2-parallel" class="practice-num-input" placeholder="contoh: 1.5">
                    </div>
                    <div class="sp-input-cell">
                      <label>Arus Total (Itot) [A]:</label>
                      <input type="number" step="any" id="sp-input-itot-parallel" class="practice-num-input" placeholder="contoh: 3.5">
                    </div>
                  </div>
                  <div id="sp-feedback-q3" class="quiz-feedback-box" style="display: none; margin-top: 8px;"></div>
                </div>

                <!-- Soal 4: Paralel Resistance -->
                <div class="practice-problem-card" style="margin-top: 14px;">
                  <div style="font-size: 0.88rem; font-weight: 800; color: #34d399;">SOAL 4 • Hambatan Pengganti Paralel</div>
                  <p style="font-size: 0.9rem; color: #e2e8f0; margin: 4px 0 8px;">
                    Hitunglah nilai hambatan pengganti total (<strong>Rtot</strong>) dari kombinasi paralel dua resistor <strong>6 Ω || 8 Ω</strong>:
                  </p>
                  <div class="sp-practice-inputs">
                    <div class="sp-input-cell">
                      <label>Hambatan Total (Rtot) [Ω]:</label>
                      <input type="number" step="any" id="sp-input-rtot-parallel" class="practice-num-input" placeholder="contoh: 3.43">
                    </div>
                  </div>
                  <div id="sp-feedback-q4" class="quiz-feedback-box" style="display: none; margin-top: 8px;"></div>
                </div>

                <!-- Check Practice Button -->
                <div style="margin-top: 18px; display: flex; justify-content: flex-end;">
                  <button class="btn-learn" style="padding: 11px 26px; font-size: 0.95rem;" onclick="checkSPPractice()">
                    <span>🔍 Periksa Jawaban</span>
                  </button>
                </div>
              </div>

              <!-- ================================================================
                   LANGKAH 6: QUIZ & COBA DI SIMULATOR
                   ================================================================ -->
              <div class="step-content-panel" id="sp-panel-6">
                <div class="step-intro-banner">
                  <span class="step-badge">LANGKAH 6 DARI 6 • EVALUASI PEMAHAMAN & SIMULASI</span>
                  <h3 class="step-title">Evaluasi Mandiri & Praktikum Laboratorium Virtual</h3>
                  <p class="step-desc">
                    Jawab 6 pertanyaan konseptual dan kalkulasi berikut untuk menguji pemahamanmu secara menyeluruh sebelum mencoba merangkai komponen di Simulator.
                  </p>
                </div>

                <!-- 6 Quiz Question Cards -->
                <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                  ${SP_QUIZ_QUESTIONS.map((q, idx) => `
                    <div class="quiz-question-card" id="sp-quiz-card-${idx}">
                      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #38bdf8;">PERTANYAAN ${idx + 1} DARI 6</span>
                      </div>
                      <p style="font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary, #0f172a); margin-bottom: 12px; line-height: 1.5;">${q.q}</p>
                      <div style="display: flex; flex-direction: column; gap: 8px;">
                        ${q.options.map((opt, optIdx) => `
                          <label class="quiz-option-label" id="sp-opt-lbl-${idx}-${optIdx}" onclick="selectSPQuizOption(${idx}, ${optIdx})">
                            <input type="radio" name="sp_quiz_${idx}" value="${optIdx}" class="quiz-option-radio">
                            <span style="font-size: 0.9rem; color: var(--color-text-secondary, #475569);">${opt}</span>
                          </label>
                        `).join("")}
                      </div>
                      <div class="quiz-feedback-box" id="sp-quiz-feedback-${idx}"></div>
                    </div>
                  `).join("")}
                </div>

                <!-- Quiz Actions -->
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px;">
                  <button class="btn-practice" onclick="resetSPQuiz()">Ulangi Kuis</button>
                  <button class="btn-learn" onclick="submitSPQuiz()">Kirim Jawaban Kuis</button>
                </div>

                <!-- Quiz Result Banner -->
                <div id="sp-quiz-result-card" style="display: none; margin-top: 16px; padding: 18px; border-radius: 10px; background: rgba(30, 41, 59, 0.85); border: 1px solid #38bdf8;">
                  <h4 style="font-size: 1.15rem; color: var(--color-text-primary, #0f172a); margin-bottom: 4px;">Hasil Pemahaman:</h4>
                  <div id="sp-quiz-score-display" style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; font-family: var(--font-mono, monospace);"></div>
                  <p id="sp-quiz-feedback-msg" style="font-size: 0.92rem; color: var(--color-text-secondary, #475569); margin-top: 6px;"></p>
                </div>

                <!-- Multimeter Reminder Callout -->
                <div class="sp-multimeter-callout">
                  <span style="font-size: 1.5rem;">💡</span>
                  <div>
                    <strong>Panduan Pengukuran Multimeter:</strong>
                    <ul style="margin: 4px 0 0 16px; padding: 0;">
                      <li><strong>Pengukuran Tegangan (Volt):</strong> Pasang probe Voltmeter secara <strong>PARALEL</strong> melintasi resistor untuk mengukur beda potensialnya tanpa memutus jalur kawat.</li>
                      <li><strong>Pengukuran Arus (Ampere):</strong> Pasang probe Amperemeter secara <strong>SERI</strong> dengan memutus kawat agar seluruh arus mengalir melalui instrumen ukur.</li>
                    </ul>
                  </div>
                </div>

                <!-- Simulator Challenge Cards -->
                <div style="margin-top: 24px;">
                  <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--color-text-primary, #0f172a); margin-bottom: 14px;">🚀 Tantangan Laboratorium Simulasi:</h4>
                  
                  <div class="sp-type-grid" style="margin-top: 0;">
                    <!-- Challenge Series -->
                    <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 12px; padding: 18px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(15,23,42,0.06));">
                      <span class="sp-type-card-badge">TANTANGAN 1 • SERI</span>
                      <h5 style="color: var(--color-text-primary, #0f172a); font-size: 1.05rem; margin: 6px 0;">Rangkaikan Rangkaian Seri</h5>
                      <p style="font-size: 0.88rem; color: var(--color-text-secondary, #475569); line-height: 1.55;">
                        Susun <strong>Baterai 12V</strong> dengan dua resistor <strong>6 Ω</strong> dan <strong>3 Ω</strong> secara seri. Verifikasi bahwa arus sirkuit bernilai <strong>1.33 A</strong> dan tegangan jatuh pada resistor 6 Ω adalah <strong>8 V</strong>.
                      </p>
                      <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-learn" style="text-decoration: none; margin-top: 12px; display: inline-flex; width: 100%; justify-content: center;">
                        <span>Coba Rangkaian Seri di Simulator</span>
                      </a>
                    </div>

                    <!-- Challenge Parallel -->
                    <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 12px; padding: 18px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(15,23,42,0.06));">
                      <span class="sp-type-card-badge" style="color: #34d399; background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3);">TANTANGAN 2 • PARALEL</span>
                      <h5 style="color: var(--color-text-primary, #0f172a); font-size: 1.05rem; margin: 6px 0;">Rangkaikan Rangkaian Paralel</h5>
                      <p style="font-size: 0.88rem; color: var(--color-text-secondary, #475569); line-height: 1.55;">
                        Susun <strong>Baterai 12V</strong> dengan cabang paralel resistor <strong>6 Ω</strong> dan <strong>8 Ω</strong>. Verifikasi arus cabang masing-masing adalah <strong>2 A</strong> dan <strong>1.5 A</strong>, serta arus total bernilai <strong>3.5 A</strong>.
                      </p>
                      <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-learn" style="text-decoration: none; margin-top: 12px; display: inline-flex; width: 100%; justify-content: center;">
                        <span>Coba Rangkaian Paralel di Simulator</span>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Completion Lock Box (Section 33) -->
                <div class="completion-lock-box" style="margin-top: 26px;">
                  <h4 style="font-size: 1.05rem; color: var(--color-text-primary, #0f172a); margin-bottom: 8px;">Kriteria Kelulusan Modul:</h4>
                  
                  <div class="completion-checklist">
                    <div class="checklist-item" id="chk-sp-intro">
                      <span>○</span> 1. Kenali Rangkaian Seri & Paralel
                    </div>
                    <div class="checklist-item" id="chk-sp-series">
                      <span>○</span> 2. Eksplorasi Rangkaian Seri
                    </div>
                    <div class="checklist-item" id="chk-sp-parallel">
                      <span>○</span> 3. Eksplorasi Rangkaian Paralel
                    </div>
                    <div class="checklist-item" id="chk-sp-classify">
                      <span>○</span> 4. Selesaikan Uji Klasifikasi Rangkaian
                    </div>
                    <div class="checklist-item" id="chk-sp-practice">
                      <span>○</span> 5. Latihan Perhitungan Numerik
                    </div>
                    <div class="checklist-item" id="chk-sp-quiz">
                      <span>○</span> 6. Selesaikan Kuis Akhir
                    </div>
                  </div>

                  <div style="margin-top: 18px;">
                    <button id="btn-finish-sp-module" class="btn-finish-module" disabled onclick="finishAndSaveSPModule(${dbId})">
                      <span>✓ Tandai Selesai & Simpan Progres</span>
                    </button>
                    <div class="completion-lock-helper" id="sp-completion-lock-helper">
                      🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 6 untuk membuka tombol selesai.
                    </div>
                  </div>
                </div>

              </div>

            </main>

            <!-- 4. Full-Screen Modal Footer -->
            <footer class="sp-fullscreen-footer">
              <button class="btn-step-nav btn-step-prev" id="btn-sp-step-prev" disabled onclick="goToSPStep(currentSPStep - 1)">
                <span>← Langkah Sebelumnya</span>
              </button>
              <div class="footer-step-counter" id="sp-step-counter-footer">
                Langkah 1 dari 6
              </div>
              <button class="btn-step-nav btn-step-next" id="btn-sp-step-next" onclick="goToSPStep(currentSPStep + 1)">
                <span>Langkah Selanjutnya →</span>
              </button>
            </footer>

          </div>
        </div>
      `;

      updateSPChecklist();
      updateSeriesVisuals();
      updateParallelVisuals();
    }

    /* ==========================================================================
       Step Navigation
       ========================================================================== */
    function goToSPStep(step) {
      if (step < 1 || step > 6) return;

      currentSPStep = step;
      completedSPSteps.add(step);

      // Update Tab Styles
      for (let i = 1; i <= 6; i++) {
        const tab = document.getElementById(`sp-tab-btn-${i}`);
        const panel = document.getElementById(`sp-panel-${i}`);
        if (tab) {
          tab.classList.toggle("active", i === step);
          if (completedSPSteps.has(i) && i !== step) {
            tab.classList.add("completed");
          }
        }
        if (panel) panel.classList.toggle("active", i === step);
      }

      // Update Progress Bar
      const percent = Math.round((step / 6) * 100);
      const fill = document.getElementById("sp-progress-fill");
      const txt = document.getElementById("sp-progress-text");
      const footerCounter = document.getElementById("sp-step-counter-footer");

      if (fill) fill.style.width = `${percent}%`;
      if (txt) txt.textContent = `Langkah ${step} dari 6 (${percent}%)`;
      if (footerCounter) footerCounter.textContent = `Langkah ${step} dari 6`;

      // Update Prev / Next Buttons
      const prevBtn = document.getElementById("btn-sp-step-prev");
      const nextBtn = document.getElementById("btn-sp-step-next");

      if (prevBtn) prevBtn.disabled = (step === 1);
      if (nextBtn) {
        if (step === 6) {
          nextBtn.style.display = "none";
        } else {
          nextBtn.style.display = "inline-flex";
          nextBtn.innerHTML = `<span>Langkah Selanjutnya →</span>`;
          nextBtn.onclick = () => goToSPStep(step + 1);
        }
      }

      updateSPChecklist();

      // Scroll modal body to top
      const body = document.getElementById("sp-modal-body");
      if (body) body.scrollTop = 0;
    }

    /* ==========================================================================
       Step 1: Seri & Paralel Choice Cards
       ========================================================================== */
    function exploreSPType(type) {
      spState.exploredTypes[type] = true;

      const card = document.getElementById(`sp-card-${type}`);
      if (card) card.classList.toggle("selected");

      const bothExplored = spState.exploredTypes.seri && spState.exploredTypes.paralel;
      const statusBox = document.getElementById("sp-type-status");
      if (statusBox && bothExplored) {
        statusBox.style.display = "block";
      }

      updateSPChecklist();
    }

    /* ==========================================================================
       Step 2: Series Circuit Logic
       ========================================================================== */
    function handleSeriesInput(param, val) {
      const num = parseFloat(val) || 1;
      if (param === 'r1') spState.seriesR1 = Math.max(1, num);
      if (param === 'r2') spState.seriesR2 = Math.max(1, num);
      if (param === 'vs') spState.seriesVs = Math.max(1, num);

      const txtR1 = document.getElementById("txt-series-r1");
      const txtR2 = document.getElementById("txt-series-r2");
      const txtVs = document.getElementById("txt-series-vs");

      if (txtR1) txtR1.textContent = `${spState.seriesR1} Ω`;
      if (txtR2) txtR2.textContent = `${spState.seriesR2} Ω`;
      if (txtVs) txtVs.textContent = `${spState.seriesVs} V`;

      spState.seriesExplored = true;
      updateSeriesVisuals();
      inspectSPElement(spState.selectedSeriesElement || 'R1_series');
      updateSPChecklist();
    }

    function toggleSeriesSwitch() {
      spState.seriesSwitchClosed = !spState.seriesSwitchClosed;
      const btn = document.getElementById("sp-series-switch-btn");
      const dot = document.getElementById("sp-switch-dot");
      const text = document.getElementById("sp-switch-text");
      const arm = document.getElementById("sp-series-switch-arm");
      const wire = document.getElementById("sp-series-wire");
      const gapText = document.getElementById("sp-series-gap-text");

      if (spState.seriesSwitchClosed) {
        if (btn) btn.className = "sp-branch-btn active";
        if (dot) dot.textContent = "🟢";
        if (text) text.textContent = "Saklar Seri: TERTUTUP (Arus Mengalir)";
        if (arm) { arm.setAttribute("stroke", "#10b981"); arm.setAttribute("x2", "50"); arm.setAttribute("y2", "0"); }
        if (wire) wire.className = "sp-wire current-flow";
        if (gapText) gapText.style.display = "none";
      } else {
        if (btn) btn.className = "sp-branch-btn open";
        if (dot) dot.textContent = "🔴";
        if (text) text.textContent = "Saklar Seri: TERBUKA (Jalur Putus)";
        // Real visible electrical gap: arm tilts up to (40, -22) leaving gap to (50, 0)
        if (arm) { arm.setAttribute("stroke", "#ef4444"); arm.setAttribute("x2", "40"); arm.setAttribute("y2", "-22"); }
        if (wire) wire.className = "sp-wire inactive";
        if (gapText) gapText.style.display = "block";
      }

      spState.seriesExplored = true;
      updateSeriesVisuals();
      inspectSPElement('switch_series');
      updateSPChecklist();
    }

    function updateSeriesVisuals() {
      const vs = spState.seriesVs;
      const r1 = spState.seriesR1;
      const r2 = spState.seriesR2;
      const rTot = r1 + r2;

      let current = 0;
      let vr1 = 0;
      let vr2 = 0;

      if (spState.seriesSwitchClosed && rTot > 0) {
        current = vs / rTot;
        vr1 = current * r1;
        vr2 = current * r2;
      }

      // Update SVG Labels
      const svgVs = document.getElementById("sp-series-vs-label");
      const svgR1 = document.getElementById("sp-svg-r1-label");
      const svgR2 = document.getElementById("sp-svg-r2-label");
      const svgVR1 = document.getElementById("sp-svg-vr1-label");
      const svgVR2 = document.getElementById("sp-svg-vr2-label");
      const svgI = document.getElementById("sp-series-current-badge");

      if (svgVs) svgVs.textContent = `Vs = ${vs}V`;
      if (svgR1) svgR1.textContent = `R1 = ${r1} Ω`;
      if (svgR2) svgR2.textContent = `R2 = ${r2} Ω`;
      if (svgVR1) svgVR1.textContent = `VR1 = ${vr1.toFixed(2)} V`;
      if (svgVR2) svgVR2.textContent = `VR2 = ${vr2.toFixed(2)} V`;
      if (svgI) svgI.textContent = `I = ${current.toFixed(3)} A`;

      // Update Math Display
      const mathDisplay = document.getElementById("sp-series-math-display");
      if (mathDisplay) {
        if (!spState.seriesSwitchClosed) {
          mathDisplay.innerHTML = `
            <span style="color: #ef4444; font-weight: bold;">⚠️ SAKLAR TERBUKA: Sirkuit terputus (Open Circuit).</span><br>
            R<sub>tot</sub> = R1 + R2 = ${r1} + ${r2} = <strong>${rTot} Ω</strong><br>
            I = <strong>0.000 A</strong> (Tidak ada aliran muatan elektron)<br>
            V<sub>R1</sub> = <strong>0.00 V</strong> | V<sub>R2</sub> = <strong>0.00 V</strong>
          `;
        } else {
          mathDisplay.innerHTML = `
            R<sub>tot</sub> = R1 + R2 = ${r1} + ${r2} = <strong>${rTot} Ω</strong><br>
            I = Vs / R<sub>tot</sub> = ${vs} / ${rTot} ≈ <strong>${current.toFixed(3)} A</strong><br>
            V<sub>R1</sub> = I × R1 = ${current.toFixed(3)} × ${r1} = <strong>${vr1.toFixed(2)} V</strong><br>
            V<sub>R2</sub> = I × R2 = ${current.toFixed(3)} × ${r2} = <strong>${vr2.toFixed(2)} V</strong><br>
            <span style="color: #34d399;">V<sub>R1</sub> + V<sub>R2</sub> = ${vr1.toFixed(2)} + ${vr2.toFixed(2)} = ${(vr1 + vr2).toFixed(2)} V (Sesuai Vs)</span>
          `;
        }
      }

      // Update Pedagogical Why Panel
      const whyPanel = document.getElementById("sp-series-why-panel");
      if (whyPanel) {
        if (!spState.seriesSwitchClosed) {
          whyPanel.innerHTML = `
            <strong>💡 Mengapa Arus Menjadi Nol?</strong><br>
            Pada rangkaian seri hanya ada <strong>satu jalur tertutup tunggal</strong>. Ketika saklar terbuka, muncul celah udara (gap) berhambatan sangat tinggi sehingga elektron bebas tidak dapat menyeberang. Akibatnya arus di seluruh loop terhenti: <strong>I = 0.000 A</strong>.
          `;
        } else {
          whyPanel.innerHTML = `
            <strong>💡 Analisis Fisika Rangkaian:</strong><br>
            Pada rangkaian seri, seluruh muatan dipaksa melalui jalur kawat yang sama. Akibatnya:
            <ul style="margin: 6px 0 0 16px; padding: 0;">
              <li>Kuat arus di setiap titik identik: <strong>I<sub>R1</sub> = I<sub>R2</sub> = ${current.toFixed(3)} A</strong>.</li>
              <li>Beban berhambatan lebih besar (${Math.max(r1, r2)} Ω) menyerap tegangan jatuh lebih tinggi (${Math.max(vr1, vr2).toFixed(2)} V).</li>
              <li>Jumlah seluruh tegangan jatuh (${(vr1+vr2).toFixed(2)} V) tepat sama dengan tegangan sumber (${vs} V).</li>
            </ul>
          `;
        }
      }
    }

    function answerSPPrediction(ans) {
      spState.seriesPredictionAnswer = ans;
      spState.seriesPredictionAttempted = true;

      for (let i = 1; i <= 3; i++) {
        const btn = document.getElementById(`pred-sp-${i}`);
        if (btn) btn.classList.toggle("selected", i === ans);
      }

      const fb = document.getElementById("pred-sp-feedback");
      if (fb) {
        fb.style.display = "block";
        if (ans === 2) {
          fb.className = "prediction-feedback show-correct";
          fb.innerHTML = `
            🎉 <strong>Prediksi Tepat Sekali!</strong><br>
            Sesuai rumus I = Vs / R<sub>tot</sub>, nilai arus berbanding terbalik dengan hambatan total. Jika R2 bertambah, R<sub>tot</sub> membesar sehingga kuat arus listrik pasti <strong>berkurang (turun)</strong>.
          `;
        } else {
          fb.className = "prediction-feedback show-wrong";
          fb.innerHTML = `
            💡 <strong>Kurang Tepat.</strong><br>
            Ingat rumus I = Vs / R<sub>tot</sub>. Sumber tegangan dijaga konstan. Jika R2 bertambah maka pembagi makin besar, sehingga kuat arus (I) akan <strong>berkurang (turun)</strong>.
          `;
        }
      }

      updateSPChecklist();
    }

    /* ==========================================================================
       Step 3: Parallel Circuit Logic
       ========================================================================== */
    function handleParallelInput(param, val) {
      const num = parseFloat(val) || 1;
      if (param === 'r1') spState.parallelR1 = Math.max(1, num);
      if (param === 'r2') spState.parallelR2 = Math.max(1, num);
      if (param === 'vs') spState.parallelVs = Math.max(1, num);

      const txtR1 = document.getElementById("txt-parallel-r1");
      const txtR2 = document.getElementById("txt-parallel-r2");
      const txtVs = document.getElementById("txt-parallel-vs");

      if (txtR1) txtR1.textContent = `${spState.parallelR1} Ω`;
      if (txtR2) txtR2.textContent = `${spState.parallelR2} Ω`;
      if (txtVs) txtVs.textContent = `${spState.parallelVs} V`;

      spState.parallelExplored = true;
      updateParallelVisuals();
      inspectSPElement(spState.selectedParallelElement || 'R1_parallel');
      updateSPChecklist();
    }

    function toggleParallelBranch(branchNum) {
      if (branchNum === 1) {
        spState.branch1Active = !spState.branch1Active;
        const btn = document.getElementById("sp-btn-branch1");
        const dot = document.getElementById("sp-b1-dot");
        const text = document.getElementById("sp-b1-text");
        const wire = document.getElementById("sp-par-wire-b1");
        const blade = document.getElementById("sp-sw1-blade");
        const gap = document.getElementById("sp-sw1-gap-text");

        if (spState.branch1Active) {
          if (btn) btn.className = "sp-branch-btn active";
          if (dot) dot.textContent = "🟢";
          if (text) text.textContent = "Cabang 1 (R1): AKTIF";
          if (wire) wire.className = "sp-wire current-flow";
          if (blade) { blade.setAttribute("stroke", "#10b981"); blade.setAttribute("x2", "50"); blade.setAttribute("y2", "0"); }
          if (gap) gap.style.display = "none";
        } else {
          if (btn) btn.className = "sp-branch-btn open";
          if (dot) dot.textContent = "🔴";
          if (text) text.textContent = "Cabang 1 (R1): TERBUKA";
          if (wire) wire.className = "sp-wire inactive";
          // Visible physical gap
          if (blade) { blade.setAttribute("stroke", "#ef4444"); blade.setAttribute("x2", "42"); blade.setAttribute("y2", "-19"); }
          if (gap) gap.style.display = "block";
        }
      } else {
        spState.branch2Active = !spState.branch2Active;
        const btn = document.getElementById("sp-btn-branch2");
        const dot = document.getElementById("sp-b2-dot");
        const text = document.getElementById("sp-b2-text");
        const wire = document.getElementById("sp-par-wire-b2");
        const blade = document.getElementById("sp-sw2-blade");
        const gap = document.getElementById("sp-sw2-gap-text");

        if (spState.branch2Active) {
          if (btn) btn.className = "sp-branch-btn active";
          if (dot) dot.textContent = "🟢";
          if (text) text.textContent = "Cabang 2 (R2): AKTIF";
          if (wire) wire.className = "sp-wire current-flow";
          if (blade) { blade.setAttribute("stroke", "#10b981"); blade.setAttribute("x2", "50"); blade.setAttribute("y2", "0"); }
          if (gap) gap.style.display = "none";
        } else {
          if (btn) btn.className = "sp-branch-btn open";
          if (dot) dot.textContent = "🔴";
          if (text) text.textContent = "Cabang 2 (R2): TERBUKA";
          if (wire) wire.className = "sp-wire inactive";
          // Visible physical gap
          if (blade) { blade.setAttribute("stroke", "#ef4444"); blade.setAttribute("x2", "42"); blade.setAttribute("y2", "-19"); }
          if (gap) gap.style.display = "block";
        }
      }

      spState.parallelExplored = true;
      updateParallelVisuals();
      inspectSPElement(branchNum === 1 ? 'branch1' : 'branch2');
      updateSPChecklist();
    }

    function updateParallelVisuals() {
      const vs = spState.parallelVs;
      const r1 = spState.parallelR1;
      const r2 = spState.parallelR2;

      let i1 = spState.branch1Active ? (vs / r1) : 0;
      let i2 = spState.branch2Active ? (vs / r2) : 0;
      let iTot = i1 + i2;

      let rTot = 0;
      let rTotText = "";
      if (spState.branch1Active && spState.branch2Active) {
        rTot = (r1 * r2) / (r1 + r2);
        rTotText = `≈ ${rTot.toFixed(3)} Ω`;
      } else if (spState.branch1Active) {
        rTot = r1;
        rTotText = `${rTot.toFixed(2)} Ω (Hanya R1)`;
      } else if (spState.branch2Active) {
        rTot = r2;
        rTotText = `${rTot.toFixed(2)} Ω (Hanya R2)`;
      } else {
        rTotText = "∞ Ω (Rangkaian Terbuka / Open Circuit)";
      }

      // Update SVG Labels
      const svgVs = document.getElementById("sp-par-vs-label");
      const svgR1 = document.getElementById("sp-svg-par-r1-label");
      const svgR2 = document.getElementById("sp-svg-par-r2-label");
      const svgV1 = document.getElementById("sp-svg-par-v1-label");
      const svgV2 = document.getElementById("sp-svg-par-v2-label");
      const svgI1 = document.getElementById("sp-svg-par-i1-label");
      const svgI2 = document.getElementById("sp-svg-par-i2-label");
      const svgITot = document.getElementById("sp-par-itot-badge");

      if (svgVs) svgVs.textContent = `Vs = ${vs}V`;
      if (svgR1) svgR1.textContent = `R1 = ${r1} Ω`;
      if (svgR2) svgR2.textContent = `R2 = ${r2} Ω`;
      if (svgV1) svgV1.textContent = `V1 = ${spState.branch1Active ? vs.toFixed(2) : '0.00'} V`;
      if (svgV2) svgV2.textContent = `V2 = ${spState.branch2Active ? vs.toFixed(2) : '0.00'} V`;
      if (svgI1) svgI1.textContent = `I1 = ${i1.toFixed(2)} A`;
      if (svgI2) svgI2.textContent = `I2 = ${i2.toFixed(2)} A`;
      if (svgITot) svgITot.textContent = `Itot = ${iTot.toFixed(2)} A`;

      // Update Trunk Flow Animation
      const trunkSupply = document.getElementById("sp-par-trunk-supply");
      const trunkReturn = document.getElementById("sp-par-trunk-return");
      if (trunkSupply && trunkReturn) {
        if (iTot > 0) {
          trunkSupply.className = "sp-wire current-flow";
          trunkReturn.className = "sp-wire current-flow";
        } else {
          trunkSupply.className = "sp-wire inactive";
          trunkReturn.className = "sp-wire inactive";
        }
      }

      // Update Math Display
      const mathDisplay = document.getElementById("sp-parallel-math-display");
      if (mathDisplay) {
        let statusAlert = "";
        if (!spState.branch1Active && !spState.branch2Active) {
          statusAlert = `<span style="color: #ef4444; font-weight: bold;">⚠️ KEDUA CABANG TERBUKA: Arus total = 0.000 A. Req = ∞ Ω.</span><br>`;
        } else if (!spState.branch1Active) {
          statusAlert = `<span style="color: #facc15; font-weight: bold;">ℹ️ Cabang 1 Terputus. Cabang 2 tetap menerima ${vs}V & arus ${i2.toFixed(2)} A!</span><br>`;
        } else if (!spState.branch2Active) {
          statusAlert = `<span style="color: #facc15; font-weight: bold;">ℹ️ Cabang 2 Terputus. Cabang 1 tetap menerima ${vs}V & arus ${i1.toFixed(2)} A!</span><br>`;
        }

        mathDisplay.innerHTML = `
          ${statusAlert}
          I<sub>1</sub> = Vs / R1 = ${vs} / ${r1} = <strong>${i1.toFixed(3)} A</strong> ${spState.branch1Active ? '' : '(Cabang Terbuka)'}<br>
          I<sub>2</sub> = Vs / R2 = ${vs} / ${r2} = <strong>${i2.toFixed(3)} A</strong> ${spState.branch2Active ? '' : '(Cabang Terbuka)'}<br>
          I<sub>tot</sub> = I<sub>1</sub> + I<sub>2</sub> = ${i1.toFixed(3)} + ${i2.toFixed(3)} = <strong>${iTot.toFixed(3)} A</strong><br>
          R<sub>eq</sub> = <strong>${rTotText}</strong>
        `;
      }

      // Update Pedagogical Why Panel
      const whyPanel = document.getElementById("sp-parallel-why-panel");
      if (whyPanel) {
        if (!spState.branch1Active && !spState.branch2Active) {
          whyPanel.innerHTML = `
            <strong>💡 Mengapa Tidak Ada Arus?</strong><br>
            Kedua cabang memiliki celah saklar terbuka (gap), sehingga tidak ada lintasan tertutup untuk kembalinya elektron ke kutub negatif. Hambatan ekivalen rangkaian bernilai <strong>tak hingga (∞ Ω)</strong>.
          `;
        } else if (!spState.branch1Active || !spState.branch2Active) {
          const activeBranch = spState.branch1Active ? "Cabang 1 (R1)" : "Cabang 2 (R2)";
          const openBranch = spState.branch1Active ? "Cabang 2" : "Cabang 1";
          whyPanel.innerHTML = `
            <strong>💡 Mengapa ${activeBranch} Tetap Menyala?</strong><br>
            Meskipun ${openBranch} terputus, ${activeBranch} memiliki koneksi loop langsung ke terminal baterai (+ dan -) tanpa melewati ${openBranch}. Inilah prinsip dasar instalasi rumah tangga: matinya satu lampu tidak memadamkan lampu lain!
          `;
        } else {
          whyPanel.innerHTML = `
            <strong>💡 Analisis Fisika Rangkaian Paralel:</strong><br>
            Setiap cabang paralel terhubung langsung ke Simpul A (+) dan Simpul B (-), sehingga <strong>tegangan kedua cabang identik (V1 = V2 = ${vs} V)</strong>.<br>
            Arus total dari sumber merupakan penjumlahan arus kedua cabang: <strong>I<sub>tot</sub> = ${iTot.toFixed(2)} A</strong>.<br>
            Hambatan pengganti (<strong>Req ≈ ${rTot.toFixed(3)} Ω</strong>) selalu lebih kecil dari ${Math.min(r1, r2)} Ω karena percabangan membuka jalur konduksi tambahan bagi elektron.
          `;
        }
      }
    }

    function answerSPWhatIf(scenarioNum, choice) {
      spState.parallelWhatIfAnswer = choice;
      spState.parallelWhatIfAttempted = true;

      for (let i = 1; i <= 3; i++) {
        const btn = document.getElementById(`whatif-sp-${i}`);
        if (btn) btn.classList.remove("selected");
      }

      const activeBtn = document.getElementById(choice === 'naik' ? 'whatif-sp-1' : (choice === 'turun' ? 'whatif-sp-2' : 'whatif-sp-3'));
      if (activeBtn) activeBtn.classList.add("selected");

      const fb = document.getElementById("whatif-sp-feedback");
      if (fb) {
        fb.style.display = "block";
        if (choice === 'turun') {
          fb.className = "prediction-feedback show-correct";
          fb.innerHTML = `
            🎉 <strong>Prediksi Tepat!</strong><br>
            I<sub>1,awal</sub> = 12 / 6 = <strong>2.00 A</strong>.<br>
            Setelah R1 diperbesar menjadi 12 Ω: I<sub>1,baru</sub> = 12 / 12 = <strong>1.00 A</strong>.<br>
            Kuat arus I1 berkurang menjadi setengahnya, membuktikan Hukum Ohm (I berbanding terbalik dengan R).
          `;
        } else {
          fb.className = "prediction-feedback show-wrong";
          fb.innerHTML = `
            💡 <strong>Kurang Tepat.</strong><br>
            Pada cabang R1: I1 = Vs / R1. Jika R1 diperbesar dari 6 Ω ke 12 Ω, maka I1 akan <strong>Turun</strong> dari 2.00 A menjadi 1.00 A.
          `;
        }
      }

      updateSPChecklist();
    }

    /* ==========================================================================
       Element Inspection Interaction
       ========================================================================== */
    function inspectSPElement(elemId) {
      if (currentSPStep === 2) {
        spState.selectedSeriesElement = elemId;
        const panelTitle = document.getElementById("sp-series-inspect-title");
        const valR = document.getElementById("sp-insp-r1-r");
        const valV = document.getElementById("sp-insp-r1-v");
        const valI = document.getElementById("sp-insp-r1-i");
        const valP = document.getElementById("sp-insp-r1-p");
        const desc = document.getElementById("sp-series-insp-desc");

        // Toggle selected class on SVG elements
        document.querySelectorAll("#sp-series-svg .sp-component-box").forEach(el => el.classList.remove("selected"));
        
        const vs = spState.seriesVs;
        const r1 = spState.seriesR1;
        const r2 = spState.seriesR2;
        const rTot = r1 + r2;
        const current = spState.seriesSwitchClosed ? (vs / rTot) : 0;

        if (elemId === 'R1_series') {
          document.getElementById("sp-resistor1-box")?.classList.add("selected");
          const vr1 = current * r1;
          const p1 = vr1 * current;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: RESISTOR R1";
          if (valR) valR.textContent = `${r1} Ω`;
          if (valV) valV.textContent = `${vr1.toFixed(2)} V`;
          if (valI) valI.textContent = `${current.toFixed(3)} A`;
          if (valP) valP.textContent = `${p1.toFixed(2)} W`;
          if (desc) desc.textContent = `Resistor R1 menyerap daya sebesar ${p1.toFixed(2)} W dan menghasilkan tegangan jatuh ${vr1.toFixed(2)} V sesuai I × R1.`;
        } else if (elemId === 'R2_series') {
          document.getElementById("sp-resistor2-box")?.classList.add("selected");
          const vr2 = current * r2;
          const p2 = vr2 * current;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: RESISTOR R2";
          if (valR) valR.textContent = `${r2} Ω`;
          if (valV) valV.textContent = `${vr2.toFixed(2)} V`;
          if (valI) valI.textContent = `${current.toFixed(3)} A`;
          if (valP) valP.textContent = `${p2.toFixed(2)} W`;
          if (desc) desc.textContent = `Resistor R2 menyerap daya sebesar ${p2.toFixed(2)} W dengan tegangan jatuh ${vr2.toFixed(2)} V. Arus yang melewatinya sama persis dengan R1.`;
        } else if (elemId === 'battery_series') {
          document.getElementById("sp-series-battery-elem")?.classList.add("selected");
          const pTot = vs * current;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: SUMBER BATERAI DC";
          if (valR) valR.textContent = "0 Ω (Ideal)";
          if (valV) valV.textContent = `${vs.toFixed(2)} V`;
          if (valI) valI.textContent = `${current.toFixed(3)} A`;
          if (valP) valP.textContent = `${pTot.toFixed(2)} W`;
          if (desc) desc.textContent = `Baterai menyediakan gaya gerak listrik (GGL) sebesar ${vs} V untuk mendorong aliran muatan ke seluruh sirkuit seri.`;
        } else if (elemId === 'switch_series') {
          document.getElementById("sp-series-switch-graphic")?.classList.add("selected");
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: SAKLAR SERI";
          if (valR) valR.textContent = spState.seriesSwitchClosed ? "0 Ω (Tertutup)" : "∞ Ω (Terbuka)";
          if (valV) valV.textContent = spState.seriesSwitchClosed ? "0.00 V" : `${vs.toFixed(2)} V`;
          if (valI) valI.textContent = `${current.toFixed(3)} A`;
          if (valP) valP.textContent = "0.00 W";
          if (desc) desc.textContent = spState.seriesSwitchClosed 
            ? "Saklar tertutup memberikan kontinuitas bagi aliran elektron bebas." 
            : "Saklar terbuka memutus lintasan loop seri sehingga seluruh arus menjadi nol.";
        }
      } else if (currentSPStep === 3) {
        spState.selectedParallelElement = elemId;
        const panelTitle = document.getElementById("sp-parallel-inspect-title");
        const valR = document.getElementById("sp-insp-par-r");
        const valV = document.getElementById("sp-insp-par-v");
        const valI = document.getElementById("sp-insp-par-i");
        const valP = document.getElementById("sp-insp-par-p");
        const desc = document.getElementById("sp-parallel-insp-desc");

        document.querySelectorAll("#sp-parallel-svg .sp-component-box").forEach(el => el.classList.remove("selected"));

        const vs = spState.parallelVs;
        const r1 = spState.parallelR1;
        const r2 = spState.parallelR2;
        const i1 = spState.branch1Active ? (vs / r1) : 0;
        const i2 = spState.branch2Active ? (vs / r2) : 0;

        if (elemId === 'R1_parallel' || elemId === 'branch1') {
          document.getElementById("sp-par-r1-box")?.classList.add("selected");
          const p1 = (spState.branch1Active ? vs : 0) * i1;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: CABANG 1 (RESISTOR R1)";
          if (valR) valR.textContent = spState.branch1Active ? `${r1} Ω` : `${r1} Ω (Terputus)`;
          if (valV) valV.textContent = spState.branch1Active ? `${vs.toFixed(2)} V` : "0.00 V";
          if (valI) valI.textContent = `${i1.toFixed(3)} A`;
          if (valP) valP.textContent = `${p1.toFixed(2)} W`;
          if (desc) desc.textContent = spState.branch1Active
            ? `Cabang 1 menerima tegangan penuh ${vs} V langsung dari baterai. Arus I1 = ${vs} / ${r1} = ${i1.toFixed(2)} A.`
            : `Cabang 1 dalam kondisi TERBUKA (saklar diputus). Arus I1 bernilai 0.00 A.`;
        } else if (elemId === 'R2_parallel' || elemId === 'branch2') {
          document.getElementById("sp-par-r2-box")?.classList.add("selected");
          const p2 = (spState.branch2Active ? vs : 0) * i2;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: CABANG 2 (RESISTOR R2)";
          if (valR) valR.textContent = spState.branch2Active ? `${r2} Ω` : `${r2} Ω (Terputus)`;
          if (valV) valV.textContent = spState.branch2Active ? `${vs.toFixed(2)} V` : "0.00 V";
          if (valI) valI.textContent = `${i2.toFixed(3)} A`;
          if (valP) valP.textContent = `${p2.toFixed(2)} W`;
          if (desc) desc.textContent = spState.branch2Active
            ? `Cabang 2 menerima tegangan penuh ${vs} V langsung dari baterai. Arus I2 = ${vs} / ${r2} = ${i2.toFixed(2)} A.`
            : `Cabang 2 dalam kondisi TERBUKA (saklar diputus). Arus I2 bernilai 0.00 A.`;
        } else if (elemId === 'battery_parallel') {
          document.getElementById("sp-parallel-battery-elem")?.classList.add("selected");
          const iTot = i1 + i2;
          const pTot = vs * iTot;
          if (panelTitle) panelTitle.textContent = "INSPEKTOR: SUMBER BATERAI DC";
          if (valR) valR.textContent = "0 Ω (Ideal)";
          if (valV) valV.textContent = `${vs.toFixed(2)} V`;
          if (valI) valI.textContent = `${iTot.toFixed(3)} A`;
          if (valP) valP.textContent = `${pTot.toFixed(2)} W`;
          if (desc) desc.textContent = `Sumber baterai menyuplai arus total ${iTot.toFixed(2)} A yang terbagi ke cabang 1 (${i1.toFixed(2)} A) dan cabang 2 (${i2.toFixed(2)} A).`;
        }
      }
    }

    /* ==========================================================================
       Step 4: Comparison & Classification
       ========================================================================== */
    function toggleSPPathHighlight() {
      spState.pathHighlighted = !spState.pathHighlighted;
      const btn = document.getElementById("sp-btn-highlight-path");
      if (btn) {
        btn.classList.toggle("active", spState.pathHighlighted);
        btn.innerHTML = spState.pathHighlighted 
          ? `<span>✨ Sembunyikan Sorot Jalur</span>` 
          : `<span>✨ Tampilkan Jalur Arus</span>`;
      }
      document.querySelectorAll(".sp-classify-card svg path").forEach(p => {
        if (spState.pathHighlighted) {
          p.classList.add("highlight");
        } else {
          p.classList.remove("highlight");
        }
      });
    }

    function answerSPClassify(qNum, choice) {
      spState.classifyAnswers[qNum] = choice;
      spState.classifyAttempted = true;

      const fb = document.getElementById(`feedback-clf-${qNum}`);
      const btnSeri = document.getElementById(`btn-clf-${qNum}-seri`);
      const btnParalel = document.getElementById(`btn-clf-${qNum}-paralel`);
      const btnCampuran = document.getElementById(`btn-clf-${qNum}-campuran`);

      if (btnSeri) btnSeri.className = "sp-classify-btn";
      if (btnParalel) btnParalel.className = "sp-classify-btn";
      if (btnCampuran) btnCampuran.className = "sp-classify-btn";

      const activeBtn = document.getElementById(`btn-clf-${qNum}-${choice}`);

      if (qNum === 1) {
        if (choice === 'seri') {
          if (activeBtn) activeBtn.className = "sp-classify-btn correct";
          if (fb) {
            fb.className = "sp-classify-feedback show-correct";
            fb.innerHTML = "✓ <strong>Tepat Sekali!</strong> Rangkaian Seri karena seluruh arus hanya mengalir melalui satu lintasan tunggal berurutan tanpa simpul percabangan.";
          }
        } else {
          if (activeBtn) activeBtn.className = "sp-classify-btn wrong";
          if (fb) {
            fb.className = "sp-classify-feedback show-wrong";
            fb.innerHTML = "❌ <strong>Kurang Tepat.</strong> Pada rangkaian paralel harus ada titik percabangan kawat. Di sini hanya ada 1 jalur tertutup, sehingga merupakan Rangkaian Seri.";
          }
        }
      } else if (qNum === 2) {
        if (choice === 'paralel') {
          if (activeBtn) activeBtn.className = "sp-classify-btn correct";
          if (fb) {
            fb.className = "sp-classify-feedback show-correct";
            fb.innerHTML = "✓ <strong>Tepat Sekali!</strong> Rangkaian Paralel karena kedua lampu terhubung pada simpul percabangan bersama dan menerima tegangan baterai secara independen.";
          }
        } else {
          if (activeBtn) activeBtn.className = "sp-classify-btn wrong";
          if (fb) {
            fb.className = "sp-classify-feedback show-wrong";
            fb.innerHTML = "❌ <strong>Kurang Tepat.</strong> Perhatikan simpul kawat di atas dan bawah lampu yang membagi arus. Ini adalah Rangkaian Paralel.";
          }
        }
      } else if (qNum === 3) {
        if (choice === 'campuran') {
          if (activeBtn) activeBtn.className = "sp-classify-btn correct";
          if (fb) {
            fb.className = "sp-classify-feedback show-correct";
            fb.innerHTML = "✓ <strong>Tepat Sekali!</strong> Rangkaian Campuran (Kombinasi), karena terdapat cabang paralel di mana salah satu cabangnya tersusun dari dua resistor yang dipasang secara seri.";
          }
        } else {
          if (activeBtn) activeBtn.className = "sp-classify-btn wrong";
          if (fb) {
            fb.className = "sp-classify-feedback show-wrong";
            fb.innerHTML = "❌ <strong>Kurang Tepat.</strong> Sirkuit ini memiliki percabangan paralel sekaligus susunan seri di cabang bawah, sehingga disebut Rangkaian Campuran.";
          }
        }
      }

      updateSPChecklist();
    }

    /* ==========================================================================
       Step 5: Practice Calculations
       ========================================================================== */
    function checkSPPractice() {
      spState.practiceAttempted = true;

      // Q1: Series Rtot (9) and Itot (1.33 or 1.333)
      const valRtotS = parseFloat(document.getElementById("sp-input-rtot-series")?.value);
      const valItotS = parseFloat(document.getElementById("sp-input-itot-series")?.value);
      const fbQ1 = document.getElementById("sp-feedback-q1");

      const q1RtotOk = Math.abs(valRtotS - 9) < 0.1;
      const q1ItotOk = Math.abs(valItotS - 1.333) < 0.05 || Math.abs(valItotS - 1.33) < 0.05;

      if (fbQ1) {
        fbQ1.style.display = "block";
        if (q1RtotOk && q1ItotOk) {
          fbQ1.className = "quiz-feedback-box show-correct";
          fbQ1.innerHTML = `✓ <strong>Benar!</strong> R<sub>tot</sub> = 6 + 3 = 9 Ω, dan I<sub>tot</sub> = 12 / 9 ≈ 1.33 A.`;
        } else {
          fbQ1.className = "quiz-feedback-box show-wrong";
          fbQ1.innerHTML = `💡 R<sub>tot</sub> seri = R1 + R2 = 6 + 3 = <strong>9 Ω</strong>. Kuat arus I = Vs / R<sub>tot</sub> = 12 / 9 ≈ <strong>1.33 A</strong>.`;
        }
      }

      // Q2: Series VR1 (8) and VR2 (4)
      const valVR1 = parseFloat(document.getElementById("sp-input-vr1-series")?.value);
      const valVR2 = parseFloat(document.getElementById("sp-input-vr2-series")?.value);
      const fbQ2 = document.getElementById("sp-feedback-q2");

      const q2VR1Ok = Math.abs(valVR1 - 8) < 0.2;
      const q2VR2Ok = Math.abs(valVR2 - 4) < 0.2;

      if (fbQ2) {
        fbQ2.style.display = "block";
        if (q2VR1Ok && q2VR2Ok) {
          fbQ2.className = "quiz-feedback-box show-correct";
          fbQ2.innerHTML = `✓ <strong>Benar!</strong> V<sub>R1</sub> = 1.333 × 6 = 8.00 V dan V<sub>R2</sub> = 1.333 × 3 = 4.00 V. Jumlahnya tepat 12 V.`;
        } else {
          fbQ2.className = "quiz-feedback-box show-wrong";
          fbQ2.innerHTML = `💡 Gunakan V = I × R: V<sub>R1</sub> = (12/9) × 6 = <strong>8 V</strong>, V<sub>R2</sub> = (12/9) × 3 = <strong>4 V</strong>.`;
        }
      }

      // Q3: Parallel I1 (2), I2 (1.5), Itot (3.5)
      const valI1 = parseFloat(document.getElementById("sp-input-i1-parallel")?.value);
      const valI2 = parseFloat(document.getElementById("sp-input-i2-parallel")?.value);
      const valItotP = parseFloat(document.getElementById("sp-input-itot-parallel")?.value);
      const fbQ3 = document.getElementById("sp-feedback-q3");

      const q3I1Ok = Math.abs(valI1 - 2) < 0.1;
      const q3I2Ok = Math.abs(valI2 - 1.5) < 0.1;
      const q3ItotOk = Math.abs(valItotP - 3.5) < 0.1;

      if (fbQ3) {
        fbQ3.style.display = "block";
        if (q3I1Ok && q3I2Ok && q3ItotOk) {
          fbQ3.className = "quiz-feedback-box show-correct";
          fbQ3.innerHTML = `✓ <strong>Benar!</strong> I1 = 12/6 = 2 A, I2 = 12/8 = 1.5 A, dan I<sub>tot</sub> = 2 + 1.5 = 3.5 A.`;
        } else {
          let extraHint = "";
          if (valItotP === valI1 || valItotP === valI2) {
            extraHint = `<br><span style="color: #facc15;">Catatan: Pada rangkaian paralel, arus total merupakan JUMLAH arus cabang (Itot = I1 + I2), bukan sama dengan arus cabang.</span>`;
          }
          fbQ3.className = "quiz-feedback-box show-wrong";
          fbQ3.innerHTML = `💡 I1 = 12 / 6 = <strong>2 A</strong>, I2 = 12 / 8 = <strong>1.5 A</strong>, I<sub>tot</sub> = 2 + 1.5 = <strong>3.5 A</strong>.${extraHint}`;
        }
      }

      // Q4: Parallel Rtot (3.43)
      const valRtotP = parseFloat(document.getElementById("sp-input-rtot-parallel")?.value);
      const fbQ4 = document.getElementById("sp-feedback-q4");

      const q4RtotOk = Math.abs(valRtotP - 3.4286) < 0.1 || Math.abs(valRtotP - 3.43) < 0.1;

      if (fbQ4) {
        fbQ4.style.display = "block";
        if (q4RtotOk) {
          fbQ4.className = "quiz-feedback-box show-correct";
          fbQ4.innerHTML = `✓ <strong>Benar!</strong> R<sub>tot</sub> = (6 × 8) / (6 + 8) = 48 / 14 ≈ 3.43 Ω.`;
        } else {
          let extraHint = "";
          if (valRtotP === 14) {
            extraHint = `<br><span style="color: #facc15;">Catatan: Penjumlahan langsung (6 + 8 = 14) digunakan untuk rangkaian seri, bukan paralel.</span>`;
          }
          fbQ4.className = "quiz-feedback-box show-wrong";
          fbQ4.innerHTML = `💡 Untuk 2 resistor paralel: R<sub>tot</sub> = (R1 × R2) / (R1 + R2) = (6 × 8) / 14 ≈ <strong>3.43 Ω</strong>.${extraHint}`;
        }
      }

      updateSPChecklist();
    }

    /* ==========================================================================
       Step 6: Quiz Logic
       ========================================================================== */
    function selectSPQuizOption(qIdx, optIdx) {
      if (spState.quizSubmitted) return;

      spState.quizAnswers[qIdx] = optIdx;
      spState.quizAttempted = true;

      const card = document.getElementById(`sp-quiz-card-${qIdx}`);
      if (!card) return;

      card.querySelectorAll(".quiz-option-label").forEach((lbl, i) => {
        lbl.classList.toggle("selected", i === optIdx);
      });

      const radio = card.querySelector(`input[value="${optIdx}"]`);
      if (radio) radio.checked = true;

      updateSPChecklist();
    }

    function submitSPQuiz() {
      const answeredCount = Object.keys(spState.quizAnswers).length;
      if (answeredCount < SP_QUIZ_QUESTIONS.length) {
        alert(`Silakan jawab seluruh ${SP_QUIZ_QUESTIONS.length} pertanyaan terlebih dahulu.`);
        return;
      }

      spState.quizSubmitted = true;
      spState.quizAttempted = true;

      let correctCount = 0;
      const total = SP_QUIZ_QUESTIONS.length;
      const wrongConcepts = [];

      SP_QUIZ_QUESTIONS.forEach((q, idx) => {
        const userAns = spState.quizAnswers[idx];
        const fb = document.getElementById(`sp-quiz-feedback-${idx}`);
        const card = document.getElementById(`sp-quiz-card-${idx}`);

        if (fb) {
          fb.style.display = "block";
          if (userAns === q.correct) {
            fb.className = "quiz-feedback-box show-correct";
            fb.innerHTML = `✓ <strong>Tepat!</strong> ${q.explanation}`;
          } else {
            fb.className = "quiz-feedback-box show-wrong";
            fb.innerHTML = `❌ <strong>Kurang tepat.</strong> ${q.explanation}`;
          }
        }

        if (card) {
          card.querySelectorAll(".quiz-option-label").forEach((lbl, optIdx) => {
            if (optIdx === q.correct) {
              lbl.style.borderColor = "#10b981";
              lbl.style.background = "rgba(16, 185, 129, 0.15)";
            } else if (userAns === optIdx && userAns !== q.correct) {
              lbl.style.borderColor = "#ef4444";
              lbl.style.background = "rgba(239, 68, 68, 0.15)";
            }
          });
        }

        if (userAns === q.correct) {
          correctCount++;
        } else {
          if (idx === 0 || idx === 2) wrongConcepts.push("Rangkaian Seri");
          if (idx === 1 || idx === 3 || idx === 4) wrongConcepts.push("Pembagian Arus & Tegangan Paralel");
          if (idx === 5) wrongConcepts.push("Kontinuitas Jalur Paralel");
        }
      });

      const percent = Math.round((correctCount / total) * 100);
      const scoreDisplay = document.getElementById("sp-quiz-score-display");
      const msgDisplay = document.getElementById("sp-quiz-feedback-msg");
      const resultCard = document.getElementById("sp-quiz-result-card");

      if (scoreDisplay) scoreDisplay.textContent = `${correctCount} / ${total} benar (${percent}%)`;
      if (msgDisplay) {
        if (percent === 100) {
          msgDisplay.textContent = "Luar biasa! Pemahamanmu terhadap Rangkaian Seri & Paralel sudah sempurna.";
        } else {
          const uniqueMistakes = [...new Set(wrongConcepts)].join(", ");
          msgDisplay.innerHTML = `Bagus! Kamu menjawab benar ${correctCount} dari ${total} soal.<br><span style="color: #facc15;">Saran tinjauan: Pelajari kembali ${uniqueMistakes}.</span>`;
        }
      }

      if (resultCard) {
        resultCard.style.display = "block";
        resultCard.scrollIntoView({ behavior: 'smooth' });
      }

      updateSPChecklist();
    }

    function resetSPQuiz() {
      spState.quizAnswers = {};
      spState.quizSubmitted = false;

      SP_QUIZ_QUESTIONS.forEach((q, idx) => {
        const card = document.getElementById(`sp-quiz-card-${idx}`);
        if (!card) return;
        card.querySelectorAll(".quiz-option-label").forEach(lbl => {
          lbl.style.borderColor = "";
          lbl.style.background = "";
          lbl.classList.remove("selected");
        });
        card.querySelectorAll(".quiz-option-radio").forEach(rad => {
          rad.checked = false;
        });
        const fb = document.getElementById(`sp-quiz-feedback-${idx}`);
        if (fb) fb.style.display = "none";
      });

      const resultCard = document.getElementById("sp-quiz-result-card");
      if (resultCard) resultCard.style.display = "none";
    }

    /* ==========================================================================
       Completion Lock Logic (Section 33)
       ========================================================================== */
    function updateSPChecklist() {
      const isIntroDone = spState.exploredTypes.seri && spState.exploredTypes.paralel;
      const isSeriesDone = spState.seriesExplored || spState.seriesPredictionAttempted;
      const isParallelDone = spState.parallelExplored || spState.parallelWhatIfAttempted;
      const isClassifyDone = spState.classifyAttempted;
      const isPracticeDone = spState.practiceAttempted;
      const isQuizDone = spState.quizAttempted;

      const setCheck = (id, done, text) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle("done", done);
        el.innerHTML = `<span>${done ? '✓' : '○'}</span> ${text}`;
      };

      setCheck("chk-sp-intro", isIntroDone, "1. Kenali Rangkaian Seri & Paralel");
      setCheck("chk-sp-series", isSeriesDone, "2. Eksplorasi Rangkaian Seri");
      setCheck("chk-sp-parallel", isParallelDone, "3. Eksplorasi Rangkaian Paralel");
      setCheck("chk-sp-classify", isClassifyDone, "4. Selesaikan Uji Klasifikasi Rangkaian");
      setCheck("chk-sp-practice", isPracticeDone, "5. Latihan Perhitungan Numerik");
      setCheck("chk-sp-quiz", isQuizDone, "6. Selesaikan Kuis Akhir");

      const canComplete = isIntroDone && isSeriesDone && isParallelDone && isClassifyDone && isPracticeDone && isQuizDone;
      const finishBtn = document.getElementById("btn-finish-sp-module");
      const helper = document.getElementById("sp-completion-lock-helper");

      if (finishBtn) {
        finishBtn.disabled = !canComplete;
      }
      if (helper) {
        helper.innerHTML = canComplete 
          ? "🎉 <strong>Selamat!</strong> Seluruh tahapan pembelajaran telah selesai. Klik tombol di atas untuk menyimpan kelulusan modul."
          : "🔒 Lengkapi seluruh interaksi di Langkah 1 s.d. 6 untuk membuka tombol selesai.";
        helper.style.color = canComplete ? "#10b981" : "#94a3b8";
      }
    }

    function finishAndSaveSPModule(dbId) {
      updateModuleProgress(dbId, 'selesai');
      closeModuleModal();
    }



    // Auto-open Modul 02 or 04 if URL parameter is specified
    document.addEventListener("DOMContentLoaded", () => {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('modul') === '2') {
        openInteractiveModule(2, 2);
      } else if (urlParams.get('modul') === '4') {
        openSeriesParallelModule(4, 4);
      }
    });
  </script>
</body>
</html>
