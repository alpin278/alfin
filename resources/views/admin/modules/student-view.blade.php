@extends('layouts.admin')

@section('title', 'Daftar Modul Siswa')

@section('breadcrumb')
  <a href="{{ route('beranda') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <span class="current-page">Daftar Modul Siswa</span>
@endsection

@section('content')
  <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
  <style>
    /* Fluxus Light Minimal Adaptations for Admin Student Modules Grid */
    .admin-student-modules-wrapper .materi-card {
      background: var(--color-bg-surface, #ffffff);
      border: 1px solid var(--color-border, #dce5f0);
      box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
      border-radius: var(--radius-lg, 14px);
      display: flex;
      flex-direction: column;
      width: 100%;
      min-width: 0;
      overflow: hidden;
      box-sizing: border-box;
      transition: all var(--transition-normal, 0.2s ease);
    }
    .admin-student-modules-wrapper .materi-card:hover {
      border-color: #93c5fd;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
    }
    .admin-student-modules-wrapper .materi-number {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--color-primary, #2563eb);
      background: var(--color-bg-surface-soft, #eaf2ff);
      border: 1px solid rgba(37, 99, 235, 0.2);
      padding: 3px 10px;
      border-radius: var(--radius-full, 9999px);
    }
    .admin-student-modules-wrapper .status-badge {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: var(--radius-full, 9999px);
      background: rgba(16, 185, 129, 0.12) !important;
      color: #059669 !important;
      border: 1px solid rgba(16, 185, 129, 0.3) !important;
    }
    .admin-student-modules-wrapper .materi-icon-wrapper {
      background: var(--color-bg-surface-soft, #eaf2ff);
      border: 1px solid rgba(37, 99, 235, 0.2);
      color: var(--color-primary, #2563eb);
    }
    .admin-student-modules-wrapper .materi-title {
      color: var(--color-text-primary, #0f172a);
      font-size: 1.05rem;
      font-weight: 700;
      margin: 10px 0 6px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow-wrap: break-word;
      word-break: break-word;
      line-height: 1.4;
    }
    .admin-student-modules-wrapper .materi-desc {
      color: var(--color-text-secondary, #64748b);
      font-size: 0.85rem;
      line-height: 1.5;
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow-wrap: break-word;
      word-break: break-word;
    }
    .admin-student-modules-wrapper .btn-learn {
      background: var(--color-primary, #2563eb);
      color: #ffffff;
      font-weight: 600;
      font-size: 0.82rem;
      padding: 9px 14px;
      border-radius: 8px;
      border: 1px solid var(--color-primary, #2563eb);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.15s;
    }
    .admin-student-modules-wrapper .btn-learn:hover {
      background: var(--color-primary-hover, #1d4ed8);
      border-color: var(--color-primary-hover, #1d4ed8);
      box-shadow: 0 2px 10px rgba(37, 99, 235, 0.3);
    }
    .admin-student-modules-wrapper .btn-practice {
      background: var(--color-bg-surface, #ffffff);
      border: 1px solid var(--color-border, #cbd5e1);
      color: var(--color-text-primary, #0f172a);
      font-weight: 600;
      font-size: 0.82rem;
      padding: 9px 14px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.15s;
      text-decoration: none;
    }
    .admin-student-modules-wrapper .btn-practice:hover {
      border-color: #93c5fd;
      color: var(--color-primary, #2563eb);
      background: var(--color-bg-surface-soft, #eaf2ff);
    }
  </style>

  <div class="admin-student-modules-wrapper">
    <!-- Header Row -->
    <div class="admin-header-row" style="margin-bottom: 20px;">
      <div class="admin-title-group">
        <h1>
          <span style="display: inline-flex; vertical-align: middle;">📖</span> DAFTAR MODUL SISWA
        </h1>
        <p>Pratinjau kurikulum dan teori pembelajaran fisika kelistrikan sesuai tampilan yang dilihat mahasiswa.</p>
      </div>
      <a href="{{ route('admin.modules.index') }}" class="btn-admin-secondary" style="font-size: 0.82rem; padding: 8px 16px; background: #ffffff; border: 1px solid var(--color-border, #cbd5e1); color: var(--color-text-primary, #0f172a); border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        <span>Kelola / Edit Modul</span>
      </a>
    </div>

    <!-- Modul List Grid -->
    <div class="materi-list-grid">
      @forelse($modules as $module)
        <div class="materi-card" id="card-modul-{{ $module->id }}">
          <div class="materi-card-top">
            <div class="materi-card-meta">
              <span class="materi-number">MODUL {{ sprintf('%02d', $module->module_number) }}</span>
              <span class="status-badge">
                ✓ Aktif di Mahasiswa
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

          <div class="materi-card-actions" style="margin-top: auto; padding-top: 16px; display: flex; gap: 8px;">
            <button class="btn-learn" onclick="openAdminModuleModal({{ $module->module_number }}, '{{ addslashes($module->title) }}')" style="flex: 1;" title="Baca materi teori lengkap">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
              <span>Pelajari Teori</span>
            </button>
            <a href="{{ route('admin.modules.edit', $module->id) }}" class="btn-practice" style="flex: 1; text-decoration: none;" title="Edit data modul ini">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              <span>Edit Modul</span>
            </a>
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px; background: var(--color-bg-card, #ffffff); border-radius: 12px; border: 1px dashed var(--color-border, #dce5f0); color: var(--color-text-muted, #94a3b8);">
          <div style="font-size: 2.2rem; margin-bottom: 8px;">📭</div>
          <p style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary, #0f172a);">Belum ada modul yang terdaftar di database.</p>
          <a href="{{ route('admin.modules.create') }}" class="btn-admin-primary" style="margin-top: 14px; display: inline-flex;">+ Tambah Modul Pertama</a>
        </div>
      @endforelse
    </div>
  </div>

  <!-- Modal Detail Materi -->
  <div id="admin-materi-modal-container"></div>

  <script>
    const MATERI_DATA = {
      1: {
        title: "Tegangan Listrik (Voltage)",
        content: `
          <p><strong>Tegangan listrik</strong> (beda potensial) adalah perbedaan energi potensial listrik antara dua titik dalam rangkaian listrik per satuan muatan. Satuannya dinyatakan dalam <strong>Volt (V)</strong>.</p>
          <h4 style="color: var(--color-primary, #2563eb); margin: 14px 0 6px;">Karakteristik Tegangan DC:</h4>
          <ul style="padding-left: 20px; line-height: 1.6; color: var(--color-text-secondary, #475569);">
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
          <h4 style="color: var(--color-primary, #2563eb); margin: 14px 0 6px;">Hukum Ohm:</h4>
          <p style="background: var(--color-bg-surface-secondary, #f1f5fb); padding: 12px; border-radius: 8px; font-family: var(--font-mono, monospace); font-size: 1.05rem; text-align: center; color: var(--color-primary, #2563eb); border: 1px solid var(--color-border, #dce5f0);">
            V = I × R &nbsp;|&nbsp; I = V / R &nbsp;|&nbsp; R = V / I
          </p>
          <ul style="padding-left: 20px; line-height: 1.6; color: var(--color-text-secondary, #475569);">
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
          <h4 style="color: var(--color-primary, #2563eb); margin: 14px 0 6px;">Aturan Pemasangan:</h4>
          <ul style="padding-left: 20px; line-height: 1.6; color: var(--color-text-secondary, #475569);">
            <li><strong>Mode Voltmeter (V DC):</strong> Hubungkan probe merah (+) dan hitam (-) <em>secara paralel</em> pada komponen yang ingin diukur tegangannya.</li>
            <li><strong>Mode Amperemeter (A DC):</strong> Putus salah satu jalur kawat dan sambungkan probe <em>secara seri</em> agar arus mengalir melewati instrumen.</li>
            <li><strong>Mode Ohmmeter (Ω):</strong> Ukur komponen dalam kondisi <em>sumber daya MATI (OFF)</em> agar tidak merusak alat ukur.</li>
          </ul>
        `
      }
    };

    function openAdminModuleModal(moduleNum, fallbackTitle) {
      const data = MATERI_DATA[moduleNum] || {
        title: fallbackTitle || "Detail Modul",
        content: "<p>Modul ini tersedia dan dapat dipelajari oleh mahasiswa sebelum memulai simulasi di laboratorium virtual.</p>"
      };

      const container = document.getElementById("admin-materi-modal-container");
      container.innerHTML = `
        <div class="materi-modal-backdrop" onclick="closeAdminModuleModal()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 10000; padding: 20px;">
          <div class="materi-modal-content" onclick="event.stopPropagation()" style="background: var(--color-bg-surface, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.12); overflow: hidden; animation: fadeIn 0.15s ease;">
            <div class="materi-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid var(--color-border, #dce5f0); background: var(--color-bg-surface-secondary, #f1f5fb);">
              <h3 style="color: var(--color-text-primary, #0f172a); font-size: 1.1rem; font-weight: 700; margin: 0;">Modul 0${moduleNum}: ${data.title}</h3>
              <button onclick="closeAdminModuleModal()" style="background: none; border: none; color: var(--color-text-muted, #64748b); font-size: 1.3rem; cursor: pointer; padding: 4px;" aria-label="Tutup">✕</button>
            </div>
            <div class="materi-modal-body" style="padding: 24px; color: var(--color-text-secondary, #475569); font-size: 0.9rem; line-height: 1.6; max-height: 65vh; overflow-y: auto;">
              ${data.content}
            </div>
            <div class="materi-modal-footer" style="padding: 16px 24px; border-top: 1px solid var(--color-border, #dce5f0); display: flex; justify-content: flex-end; gap: 10px;">
              <button onclick="closeAdminModuleModal()" style="background: var(--color-bg-surface-secondary, #f1f5fb); border: 1px solid var(--color-border, #dce5f0); color: var(--color-text-primary, #0f172a); padding: 8px 18px; border-radius: 8px; font-size: 0.84rem; font-weight: 600; cursor: pointer;">Tutup</button>
            </div>
          </div>
        </div>
      `;
    }

    function closeAdminModuleModal() {
      document.getElementById("admin-materi-modal-container").innerHTML = "";
    }
  </script>
@endsection
