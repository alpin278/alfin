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
              @if($module->module_number == 2 || $module->module_number == 4)
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
      if (moduleNum === 2) {
        openInteractiveModule(dbId, moduleNum);
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
        <div class="interactive-modal-backdrop" onclick="closeModuleModal()">
          <div class="interactive-modal-container" onclick="event.stopPropagation()">
            
            <!-- Modal Header -->
            <div class="interactive-modal-header">
              <div class="interactive-header-top">
                <div>
                  <span class="interactive-module-badge">⚡ Modul 02 • Dasar Teknik Elektro</span>
                  <h2 class="interactive-module-title">Hambatan Listrik & Hukum Ohm</h2>
                </div>
                <div class="interactive-header-actions">
                  <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-header-sim" title="Buka Rangkaian di Simulator">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <span>Coba di Simulator</span>
                  </a>
                  <button class="btn-close-modal" onclick="closeModuleModal()" aria-label="Tutup Modul">✕</button>
                </div>
              </div>

              <!-- Compact Progress Bar Tracker -->
              <div class="interactive-progress-wrapper">
                <div class="interactive-progress-bar">
                  <div class="interactive-progress-fill" id="interactive-progress-fill" style="width: 20%;"></div>
                </div>
                <span class="interactive-progress-text" id="interactive-progress-text">Langkah 1 dari 5 (20%)</span>
              </div>
            </div>

            <!-- Step Tabs Bar -->
            <div class="interactive-tabs-bar" role="tablist">
              <button class="interactive-tab-item active" id="tab-btn-1" onclick="goToStep(1)">
                <span class="tab-badge">1</span>
                <span class="interactive-tab-title">Kenali V, I, R</span>
              </button>
              <button class="interactive-tab-item" id="tab-btn-2" onclick="goToStep(2)">
                <span class="tab-badge">2</span>
                <span class="interactive-tab-title">Eksplorasi Formula</span>
              </button>
              <button class="interactive-tab-item" id="tab-btn-3" onclick="goToStep(3)">
                <span class="tab-badge">3</span>
                <span class="interactive-tab-title">Prediksi Perubahan</span>
              </button>
              <button class="interactive-tab-item" id="tab-btn-4" onclick="goToStep(4)">
                <span class="tab-badge">4</span>
                <span class="interactive-tab-title">Latihan Perhitungan</span>
              </button>
              <button class="interactive-tab-item" id="tab-btn-5" onclick="goToStep(5)">
                <span class="tab-badge">5</span>
                <span class="interactive-tab-title">Quiz & Simulator</span>
              </button>
            </div>

            <!-- Modal Body (Steps) -->
            <div class="interactive-modal-body" id="interactive-modal-body">
              
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

                      <div style="font-family: var(--font-mono); font-size: 0.82rem; color: #94a3b8;">
                        Konversi Satuan: <strong id="calc-ampere-val" style="color: #38bdf8;">0.024 A</strong> (1 A = 1000 mA)
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
                    <pre style="background: #070d18; padding: 12px; border-radius: 8px; font-family: monospace; color: #38bdf8; text-align: center; margin: 8px 0;">
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
                  <p style="font-size: 0.9rem; color: #cbd5e1; line-height: 1.6; margin: 0 0 10px;">
                    Buat rangkaian dengan sumber <strong>12V</strong> dan resistor <strong>600Ω</strong> pada simulator, kemudian hubungkan Multimeter (mode Amperemeter DC secara seri) untuk mengukur arusnya.<br>
                    <strong>Arus Teoretis yang diharapkan:</strong> <span style="color: #38bdf8; font-weight: 700;">20 mA</span>.
                  </p>

                  <h4 class="challenge-title" style="margin-top: 16px;">Tantangan 2: Pengujian Resistor 1200Ω</h4>
                  <p style="font-size: 0.9rem; color: #cbd5e1; line-height: 1.6; margin: 0 0 10px;">
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

            </div>

            <!-- Modal Footer Navigation -->
            <div class="interactive-modal-footer">
              <button class="btn-step-nav btn-step-prev" id="btn-step-prev" onclick="goToStep(currentModuleStep - 1)" disabled>
                <span>← Sebelumnya</span>
              </button>

              <span class="footer-step-counter" id="footer-step-counter">Langkah 1 dari 5</span>

              <button class="btn-step-nav btn-step-next" id="btn-step-next" onclick="goToStep(currentModuleStep + 1)">
                <span>Langkah Selanjutnya →</span>
              </button>
            </div>

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
                    <div style="background: rgba(10,16,28,0.85); border-radius: 8px; padding: 12px; margin-bottom: 14px; border: 1px solid rgba(56,189,248,0.25);">
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
                    <div style="background: rgba(10,16,28,0.85); border-radius: 8px; padding: 12px; margin-bottom: 14px; border: 1px solid rgba(16,185,129,0.25);">
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
                        <span style="font-size: 0.82rem; color: #94a3b8; align-self: center;">💡 Klik komponen pada diagram untuk inspeksi detail.</span>
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
                      <p id="sp-series-insp-desc" style="font-size: 0.84rem; color: #cbd5e1; margin: 4px 0 0; line-height: 1.5;">
                        Resistor R1 menerima kuat arus penuh (1.333 A) yang sama persis dengan R2. Karena nilai hambatannya (6 Ω) adalah 2 kali lipat dari R2 (3 Ω), R1 menyerap dua per tiga (8 V) dari total tegangan sumber 12 V.
                      </p>
                    </div>

                    <!-- Prediction Exercise -->
                    <div class="sp-whatif-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase;">❓ Uji Prediksi Konseptual Seri</span>
                      <p style="font-size: 0.92rem; margin: 0; color: #f8fafc; line-height: 1.5;">
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
                      <span style="font-size: 0.78rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Substitusi Rumus Seri Matematis</span>
                      <div id="sp-series-math-display" style="font-family: var(--font-mono, monospace); font-size: 0.95rem; line-height: 1.65; color: #cbd5e1;">
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
                      <p id="sp-parallel-insp-desc" style="font-size: 0.84rem; color: #cbd5e1; margin: 4px 0 0; line-height: 1.5;">
                        Cabang 1 terhubung langsung ke terminal baterai melalui Simpul A dan Simpul B, sehingga mendapatkan tegangan penuh 12 V. Sesuai I1 = Vs / R1 = 12 / 6 = 2.00 A.
                      </p>
                    </div>

                    <!-- "Bagaimana Jika...?" What-If Prediction Section -->
                    <div class="sp-whatif-card">
                      <span style="font-size: 0.78rem; font-weight: 800; color: #fbbf24; text-transform: uppercase;">🔮 Mode Eksplorasi "Bagaimana Jika...?"</span>
                      <p style="font-size: 0.92rem; margin: 0; color: #f8fafc; line-height: 1.5;">
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
                      <span style="font-size: 0.78rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Substitusi Rumus Paralel Matematis</span>
                      <div id="sp-parallel-math-display" style="font-family: var(--font-mono, monospace); font-size: 0.95rem; line-height: 1.65; color: #cbd5e1;">
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
                <h4 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin: 24px 0 12px;">Uji Klasifikasi Rangkaian:</h4>
                <div class="sp-classify-grid">
                  
                  <!-- Challenge 1 -->
                  <div class="sp-classify-card" id="sp-classify-1">
                    <div class="sp-classify-header">
                      <span style="font-size: 0.88rem; font-weight: 800; color: #38bdf8;">Kasus 1: Dua Beban dalam Satu Lintasan</span>
                    </div>
                    <div style="background: rgba(10,16,28,0.8); padding: 10px; border-radius: 8px; text-align: center;">
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
                    <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
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
                    <div style="background: rgba(10,16,28,0.8); padding: 10px; border-radius: 8px; text-align: center;">
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
                    <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
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
                    <div style="background: rgba(10,16,28,0.8); padding: 10px; border-radius: 8px; text-align: center;">
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
                    <p style="font-size: 0.9rem; color: #cbd5e1; margin: 0;">Rangkaian di atas tergolong ke dalam konfigurasi:</p>
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
                      <p style="font-size: 0.95rem; font-weight: 600; color: #f8fafc; margin-bottom: 12px; line-height: 1.5;">${q.q}</p>
                      <div style="display: flex; flex-direction: column; gap: 8px;">
                        ${q.options.map((opt, optIdx) => `
                          <label class="quiz-option-label" id="sp-opt-lbl-${idx}-${optIdx}" onclick="selectSPQuizOption(${idx}, ${optIdx})">
                            <input type="radio" name="sp_quiz_${idx}" value="${optIdx}" class="quiz-option-radio">
                            <span style="font-size: 0.9rem; color: #cbd5e1;">${opt}</span>
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
                  <h4 style="font-size: 1.15rem; color: #f8fafc; margin-bottom: 4px;">Hasil Pemahaman:</h4>
                  <div id="sp-quiz-score-display" style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; font-family: var(--font-mono, monospace);"></div>
                  <p id="sp-quiz-feedback-msg" style="font-size: 0.92rem; color: #cbd5e1; margin-top: 6px;"></p>
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
                  <h4 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin-bottom: 14px;">🚀 Tantangan Laboratorium Simulasi:</h4>
                  
                  <div class="sp-type-grid" style="margin-top: 0;">
                    <!-- Challenge Series -->
                    <div style="background: rgba(15,23,42,0.85); border: 1px solid #334155; border-radius: 12px; padding: 18px;">
                      <span class="sp-type-card-badge">TANTANGAN 1 • SERI</span>
                      <h5 style="color: #f8fafc; font-size: 1.05rem; margin: 6px 0;">Rangkaikan Rangkaian Seri</h5>
                      <p style="font-size: 0.88rem; color: #94a3b8; line-height: 1.55;">
                        Susun <strong>Baterai 12V</strong> dengan dua resistor <strong>6 Ω</strong> dan <strong>3 Ω</strong> secara seri. Verifikasi bahwa arus sirkuit bernilai <strong>1.33 A</strong> dan tegangan jatuh pada resistor 6 Ω adalah <strong>8 V</strong>.
                      </p>
                      <a href="{{ route('simulasi', ['from' => 'materi']) }}" class="btn-learn" style="text-decoration: none; margin-top: 12px; display: inline-flex; width: 100%; justify-content: center;">
                        <span>Coba Rangkaian Seri di Simulator</span>
                      </a>
                    </div>

                    <!-- Challenge Parallel -->
                    <div style="background: rgba(15,23,42,0.85); border: 1px solid #334155; border-radius: 12px; padding: 18px;">
                      <span class="sp-type-card-badge" style="color: #34d399; background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3);">TANTANGAN 2 • PARALEL</span>
                      <h5 style="color: #f8fafc; font-size: 1.05rem; margin: 6px 0;">Rangkaikan Rangkaian Paralel</h5>
                      <p style="font-size: 0.88rem; color: #94a3b8; line-height: 1.55;">
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
                  <h4 style="font-size: 1.05rem; color: #f8fafc; margin-bottom: 8px;">Kriteria Kelulusan Modul:</h4>
                  
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
              <button class="btn-step-prev" id="btn-sp-step-prev" disabled onclick="goToSPStep(currentSPStep - 1)">
                <span>← Langkah Sebelumnya</span>
              </button>
              <div style="font-size: 0.86rem; font-weight: 600; color: #94a3b8;" id="sp-step-counter-footer">
                Langkah 1 dari 6
              </div>
              <button class="btn-step-next" id="btn-sp-step-next" onclick="goToSPStep(currentSPStep + 1)">
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
