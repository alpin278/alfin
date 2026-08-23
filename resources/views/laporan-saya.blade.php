<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Laporan Praktikum Saya — DTE VirtualLab</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
  <style>
    .laporan-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }
    .laporan-stat-card {
      background: var(--color-bg-card, #1e293b);
      border: 1px solid var(--color-border, #334155);
      border-radius: var(--radius-md, 10px);
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .laporan-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: var(--radius-sm, 6px);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .laporan-stat-info {
      min-width: 0;
    }
    .laporan-stat-label {
      font-size: 0.76rem;
      color: #94a3b8;
      font-weight: 500;
    }
    .laporan-stat-val {
      font-size: 1.35rem;
      font-weight: 800;
      color: #f8fafc;
      font-family: var(--font-mono);
      line-height: 1.2;
    }
    .laporan-section-heading {
      font-size: 1.15rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-left: 4px solid var(--color-primary-blue, #0284c7);
      padding-left: 12px;
    }
    .laporan-card {
      background: var(--color-bg-card, #1e293b);
      border: 1px solid var(--color-border, #334155);
      border-radius: var(--radius-md, 10px);
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 16px;
      transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .laporan-card:hover {
      border-color: rgba(56, 189, 248, 0.4);
      transform: translateY(-2px);
    }
    .laporan-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: var(--radius-full, 9999px);
      font-size: 0.74rem;
      font-weight: 700;
    }
    .status-submitted {
      background: rgba(245, 158, 11, 0.15);
      color: #fbbf24;
      border: 1px solid rgba(245, 158, 11, 0.35);
    }
    .status-graded {
      background: rgba(16, 185, 129, 0.15);
      color: #10b981;
      border: 1px solid rgba(16, 185, 129, 0.35);
    }
    .status-empty {
      background: rgba(148, 163, 184, 0.12);
      color: #94a3b8;
      border: 1px solid #475569;
    }
    .grade-highlight-box {
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.25);
      border-radius: var(--radius-sm, 6px);
      padding: 10px 14px;
      margin-top: 10px;
    }
    .grade-score-val {
      font-size: 1.25rem;
      font-weight: 800;
      color: #10b981;
      font-family: var(--font-mono);
    }
    .feedback-quote {
      font-size: 0.8rem;
      color: #cbd5e1;
      font-style: italic;
      margin-top: 4px;
      line-height: 1.4;
    }
    .file-attachment-info {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid #334155;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 0.78rem;
      color: #94a3b8;
      overflow: hidden;
    }
    .file-attachment-info span {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    /* Modal Styling */
    .laporan-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(3, 7, 18, 0.75);
      backdrop-filter: blur(6px);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .laporan-modal-backdrop.active {
      display: flex;
    }
    .laporan-modal-box {
      background: var(--color-bg-surface, #0f172a);
      border: 1px solid var(--color-border, #334155);
      border-radius: var(--radius-lg, 14px);
      width: 100%;
      max-width: 520px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
      padding: 24px;
      animation: modalScale 0.2s ease-out;
    }
    @keyframes modalScale {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Universal Shared Navigation -->
  @include('partials.navbar')

  <!-- Breadcrumb Navigation -->
  <nav class="breadcrumb-container" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
      <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
      <li class="breadcrumb-separator">/</li>
      <li class="breadcrumb-item active" aria-current="page">Laporan Saya</li>
    </ol>
  </nav>

  <!-- Main Container -->
  <main class="materi-page-container">
    @if(session('success'))
      <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-size: 0.9rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span>{{ session('success') }}</span>
      </div>
    @endif

    @if($errors->any())
      <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 12px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem;">
        <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
          <span>Gagal Mengunggah Laporan:</span>
        </div>
        <ul style="margin: 0; padding-left: 18px;">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="materi-header-section">
      <h1 class="materi-page-title" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>LAPORAN PRAKTIKUM SAYA</span>
      </h1>
      <p class="materi-page-desc">
        Kumpulkan berkas laporan hasil praktikum modul kelistrikan dan studi kasus Anda untuk dinilai oleh dosen/instruktur.
      </p>
    </div>

    <!-- Stats Row -->
    <div class="laporan-stats-grid">
      <div class="laporan-stat-card">
        <div class="laporan-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
        </div>
        <div class="laporan-stat-info">
          <div class="laporan-stat-label">Total Praktikum</div>
          <div class="laporan-stat-val">{{ $totalTargets }}</div>
        </div>
      </div>
      <div class="laporan-stat-card">
        <div class="laporan-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="laporan-stat-info">
          <div class="laporan-stat-label">Menunggu Dinilai</div>
          <div class="laporan-stat-val">{{ $submittedCount }}</div>
        </div>
      </div>
      <div class="laporan-stat-card">
        <div class="laporan-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="laporan-stat-info">
          <div class="laporan-stat-label">Sudah Dinilai</div>
          <div class="laporan-stat-val">{{ $gradedCount }}</div>
        </div>
      </div>
      <div class="laporan-stat-card">
        <div class="laporan-stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
        </div>
        <div class="laporan-stat-info">
          <div class="laporan-stat-label">Rata-Rata Nilai</div>
          <div class="laporan-stat-val">{{ $averageGrade ? $averageGrade : '-' }}</div>
        </div>
      </div>
    </div>

    <!-- 1. SEKSI MODUL PEMBELAJARAN -->
    <div class="laporan-section-heading">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
      <span>Laporan Modul Pembelajaran</span>
    </div>
    <div class="materi-list-grid" style="margin-bottom: 40px;">
      @forelse($modules as $module)
        @php
          $key = 'App\Models\Module_' . $module->id;
          $sub = $submissions->get($key);
        @endphp
        <div class="laporan-card">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
              <span class="materi-number">MODUL {{ sprintf('%02d', $module->module_number) }}</span>
              @if(!$sub)
                <span class="laporan-status-badge status-empty">○ Belum Upload</span>
              @elseif($sub->status === 'graded')
                <span class="laporan-status-badge status-graded">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  Sudah Dinilai
                </span>
              @else
                <span class="laporan-status-badge status-submitted">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  Menunggu Nilai
                </span>
              @endif
            </div>

            <h3 class="materi-title" style="font-size: 1.05rem; margin-bottom: 6px;">{{ $module->title }}</h3>
            <p class="materi-desc" style="font-size: 0.8rem; line-height: 1.4; margin-bottom: 12px;">{{ Str::limit($module->description, 100) }}</p>

            @if($sub)
              <div class="file-attachment-info">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span>{{ $sub->original_filename }} ({{ $sub->file_size }} KB)</span>
              </div>

              @if($sub->status === 'graded')
                <div class="grade-highlight-box">
                  <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">NILAI AKHIR:</span>
                    <span class="grade-score-val">{{ $sub->grade }} / 100</span>
                  </div>
                  @if($sub->teacher_feedback)
                    <div class="feedback-quote">"{{ $sub->teacher_feedback }}"</div>
                  @endif
                  <div style="font-size: 0.7rem; color: #64748b; margin-top: 6px;">Dinilai oleh: {{ $sub->gradedByTeacher->name ?? 'Pengajar' }} &bull; {{ $sub->graded_at ? $sub->graded_at->diffForHumans() : '' }}</div>
                </div>
              @endif
            @endif
          </div>

          <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #334155; padding-top: 12px;">
            @if(!$sub)
              <button onclick="openUploadModal('App\\Models\\Module', {{ $module->id }}, 'Modul {{ sprintf('%02d', $module->module_number) }}: {{ addslashes($module->title) }}')" class="btn-cta-sim" style="width: 100%; justify-content: center; font-size: 0.82rem; padding: 8px 12px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <span>Upload Laporan</span>
              </button>
            @elseif($sub->status === 'submitted')
              <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="btn-modul-outline" style="flex: 1; text-align: center; font-size: 0.78rem; padding: 7px 10px; text-decoration: none; color: #cbd5e1; display: inline-flex; align-items: center; justify-content: center; gap: 5px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path></svg>
                <span>Lihat File</span>
              </a>
              <button onclick="openUploadModal('App\\Models\\Module', {{ $module->id }}, 'Modul {{ sprintf('%02d', $module->module_number) }}: {{ addslashes($module->title) }}')" class="btn-cta-sim" style="flex: 1; justify-content: center; font-size: 0.78rem; padding: 7px 10px; background: #334155; border: 1px solid #475569; display: inline-flex; align-items: center; gap: 5px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span>Ganti File</span>
              </button>
            @else
              <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="btn-modul-outline" style="width: 100%; text-align: center; font-size: 0.8rem; padding: 8px 12px; text-decoration: none; color: #10b981; border-color: rgba(16, 185, 129, 0.4); display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Unduh Laporan Saya</span>
              </a>
            @endif
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 30px;">Belum ada modul pembelajaran yang tersedia.</div>
      @endforelse
    </div>

    <!-- 2. SEKSI STUDI KASUS PBL -->
    <div class="laporan-section-heading">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
      <span>Laporan Studi Kasus (Problem-Based Learning)</span>
    </div>
    <div class="materi-list-grid">
      @forelse($caseStudies as $case)
        @php
          $key = 'App\Models\CaseStudy_' . $case->id;
          $sub = $submissions->get($key);
        @endphp
        <div class="laporan-card">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
              <span class="materi-number" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">KASUS #{{ $case->id }}</span>
              @if(!$sub)
                <span class="laporan-status-badge status-empty">○ Belum Upload</span>
              @elseif($sub->status === 'graded')
                <span class="laporan-status-badge status-graded">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  Sudah Dinilai
                </span>
              @else
                <span class="laporan-status-badge status-submitted">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  Menunggu Nilai
                </span>
              @endif
            </div>

            <h3 class="materi-title" style="font-size: 1.05rem; margin-bottom: 6px;">{{ $case->title }}</h3>
            <p class="materi-desc" style="font-size: 0.8rem; line-height: 1.4; margin-bottom: 12px;">{{ Str::limit($case->description, 100) }}</p>

            @if($sub)
              <div class="file-attachment-info">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span>{{ $sub->original_filename }} ({{ $sub->file_size }} KB)</span>
              </div>

              @if($sub->status === 'graded')
                <div class="grade-highlight-box">
                  <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">NILAI AKHIR:</span>
                    <span class="grade-score-val">{{ $sub->grade }} / 100</span>
                  </div>
                  @if($sub->teacher_feedback)
                    <div class="feedback-quote">"{{ $sub->teacher_feedback }}"</div>
                  @endif
                  <div style="font-size: 0.7rem; color: #64748b; margin-top: 6px;">Dinilai oleh: {{ $sub->gradedByTeacher->name ?? 'Pengajar' }} &bull; {{ $sub->graded_at ? $sub->graded_at->diffForHumans() : '' }}</div>
                </div>
              @endif
            @endif
          </div>

          <div style="display: flex; gap: 8px; align-items: center; border-top: 1px solid #334155; padding-top: 12px;">
            @if(!$sub)
              <button onclick="openUploadModal('App\\Models\\CaseStudy', {{ $case->id }}, 'Studi Kasus: {{ addslashes($case->title) }}')" class="btn-cta-sim" style="width: 100%; justify-content: center; font-size: 0.82rem; padding: 8px 12px; background: linear-gradient(135deg, #9333ea, #7e22ce); display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <span>Upload Laporan</span>
              </button>
            @elseif($sub->status === 'submitted')
              <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="btn-modul-outline" style="flex: 1; text-align: center; font-size: 0.78rem; padding: 7px 10px; text-decoration: none; color: #cbd5e1; display: inline-flex; align-items: center; justify-content: center; gap: 5px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path></svg>
                <span>Lihat File</span>
              </a>
              <button onclick="openUploadModal('App\\Models\\CaseStudy', {{ $case->id }}, 'Studi Kasus: {{ addslashes($case->title) }}')" class="btn-cta-sim" style="flex: 1; justify-content: center; font-size: 0.78rem; padding: 7px 10px; background: #334155; border: 1px solid #475569; display: inline-flex; align-items: center; gap: 5px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span>Ganti File</span>
              </button>
            @else
              <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="btn-modul-outline" style="width: 100%; text-align: center; font-size: 0.8rem; padding: 8px 12px; text-decoration: none; color: #10b981; border-color: rgba(16, 185, 129, 0.4); display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Unduh Laporan Saya</span>
              </a>
            @endif
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 30px;">Belum ada studi kasus yang tersedia.</div>
      @endforelse
    </div>
  </main>

  <!-- UPLOAD MODAL DIALOG -->
  <div class="laporan-modal-backdrop" id="uploadModalBackdrop" onclick="closeUploadModalOnBackdrop(event)">
    <div class="laporan-modal-box">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 16px;">
        <h3 style="color: #f8fafc; font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
          <span>Upload Laporan Praktikum</span>
        </h3>
        <button onclick="closeUploadModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer; padding: 4px;" aria-label="Tutup">✕</button>
      </div>

      <form action="{{ route('laporan.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="reportable_type" id="modalTargetType">
        <input type="hidden" name="reportable_id" id="modalTargetId">

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 0.78rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Target Praktikum:</label>
          <div id="modalTargetTitle" style="background: rgba(15, 23, 42, 0.8); border: 1px solid #334155; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: #38bdf8;">-</div>
        </div>

        <div style="margin-bottom: 14px;">
          <label for="laporanFile" style="display: block; font-size: 0.78rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Pilih File Laporan (Maks. 10MB):</label>
          <input type="file" name="file" id="laporanFile" required style="display: block; width: 100%; background: #1e293b; border: 1px dashed #475569; padding: 12px; border-radius: 6px; color: #f8fafc; font-size: 0.82rem; cursor: pointer;">
          <small style="display: block; color: #64748b; font-size: 0.72rem; margin-top: 5px;">Format yang didukung: PDF, Word (DOC/DOCX), Excel, PPT, Gambar (JPG, PNG).</small>
        </div>

        <div style="margin-bottom: 20px;">
          <label for="laporanNote" style="display: block; font-size: 0.78rem; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Catatan Tambahan untuk Guru/Dosen (Opsional):</label>
          <textarea name="note" id="laporanNote" rows="3" placeholder="Contoh: Laporan praktikum simulasi telah selesai diuji pada rangkaian seri dan paralel..." style="width: 100%; background: #1e293b; border: 1px solid #334155; border-radius: 6px; padding: 10px; color: #f8fafc; font-size: 0.82rem; resize: vertical;"></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" onclick="closeUploadModal()" style="background: transparent; border: 1px solid #475569; color: #cbd5e1; padding: 8px 16px; border-radius: 6px; font-size: 0.82rem; cursor: pointer;">Batal</button>
          <button type="submit" class="btn-cta-sim" style="font-size: 0.82rem; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            <span>Kirim Laporan</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openUploadModal(type, id, title) {
      document.getElementById('modalTargetType').value = type;
      document.getElementById('modalTargetId').value = id;
      document.getElementById('modalTargetTitle').textContent = title;
      document.getElementById('uploadModalBackdrop').classList.add('active');
    }

    function closeUploadModal() {
      document.getElementById('uploadModalBackdrop').classList.remove('active');
    }

    function closeUploadModalOnBackdrop(e) {
      if (e.target.id === 'uploadModalBackdrop') {
        closeUploadModal();
      }
    }
  </script>
</body>
</html>