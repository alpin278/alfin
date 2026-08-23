@extends('layouts.admin')

@section('title', 'Evaluasi Laporan — ' . ($submission->user->name ?? 'Mahasiswa'))

@section('breadcrumb')
  <a href="{{ route('beranda') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.laporan.index') }}">Laporan Masuk</a>
  <span class="separator">/</span>
  <span class="current-page">Detail & Penilaian</span>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert-box-success">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="alert-box-error">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
      <div>
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    </div>
  @endif

  <!-- Top Action Row -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <a href="{{ route('admin.laporan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 0.88rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      <span>Kembali ke Daftar Laporan</span>
    </a>

    <a href="{{ asset('storage/' . $submission->file_path) }}" download="{{ $submission->original_filename }}" class="btn-admin-primary" style="background: #334155; border: 1px solid #475569; text-decoration: none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
      <span>Unduh File Asli</span>
    </a>
  </div>

  <!-- 2-Column Responsive Layout -->
  <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 24px; align-items: flex-start;" class="admin-eval-grid">
    <!-- LEFT: Student Info & File Preview -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Mahasiswa & Target Praktikum Card -->
      <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 20px;">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
          <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #38bdf8); color: #ffffff; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
            {{ strtoupper(substr($submission->user->name ?? 'M', 0, 1)) }}
          </div>
          <div>
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin: 0;">{{ $submission->user->name }}</h2>
            <div style="font-size: 0.8rem; color: #94a3b8; font-family: var(--font-mono); margin-top: 2px;">
              NIM: <span style="color: #38bdf8; font-weight: 700;">{{ $submission->user->nim ?? 'Belum diisi' }}</span> &bull; {{ $submission->user->email }}
            </div>
          </div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid #334155; border-radius: 8px; padding: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.82rem;">
          <div>
            <span style="color: #64748b; display: block; font-size: 0.72rem;">TARGET MATERI:</span>
            <strong style="color: #f8fafc;">{{ $submission->reportable->title ?? 'Praktikum' }}</strong>
          </div>
          <div>
            <span style="color: #64748b; display: block; font-size: 0.72rem;">WAKTU SUBMIT:</span>
            <span style="color: #cbd5e1;">{{ $submission->submitted_at ? $submission->submitted_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</span>
          </div>
          <div>
            <span style="color: #64748b; display: block; font-size: 0.72rem;">NAMA BERKAS:</span>
            <span style="color: #38bdf8; word-break: break-all;">{{ $submission->original_filename }}</span>
          </div>
          <div>
            <span style="color: #64748b; display: block; font-size: 0.72rem;">UKURAN & FORMAT:</span>
            <span style="color: #cbd5e1;">{{ $submission->file_size }} KB (.{{ strtoupper($submission->file_extension) }})</span>
          </div>
        </div>

        @if($submission->note)
          <div style="margin-top: 14px; background: rgba(56, 189, 248, 0.08); border-left: 3px solid #38bdf8; padding: 10px 14px; border-radius: 4px; font-size: 0.82rem; color: #cbd5e1;">
            <strong style="color: #38bdf8; display: block; font-size: 0.74rem; margin-bottom: 2px;">CATATAN DARI MAHASISWA:</strong>
            "{{ $submission->note }}"
          </div>
        @endif
      </div>

      <!-- File Preview Section -->
      <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 20px;">
        <h3 style="font-size: 0.95rem; font-weight: 700; color: #f8fafc; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span>Pratinjau Berkas Laporan</span>
        </h3>

        @php
          $ext = strtolower($submission->file_extension);
          $fileUrl = asset('storage/' . $submission->file_path);
        @endphp

        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
          <div style="text-align: center; background: #0b1329; padding: 12px; border-radius: 8px; border: 1px solid #334155;">
            <img src="{{ $fileUrl }}" alt="Preview Laporan" style="max-width: 100%; height: auto; max-height: 550px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
          </div>
        @elseif($ext === 'pdf')
          <div style="border-radius: 8px; overflow: hidden; border: 1px solid #334155; background: #0b1329;">
            <iframe src="{{ $fileUrl }}" width="100%" height="580px" style="border: none;"></iframe>
          </div>
        @else
          <div style="text-align: center; padding: 40px 20px; background: rgba(15, 23, 42, 0.6); border: 1px dashed #475569; border-radius: 8px;">
            <div style="display: inline-flex; color: #38bdf8; margin-bottom: 12px;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
            </div>
            <div style="font-weight: 700; color: #f8fafc; font-size: 1rem; margin-bottom: 6px;">Berkas Dokumen: .{{ strtoupper($ext) }}</div>
            <p style="color: #94a3b8; font-size: 0.82rem; max-width: 360px; margin: 0 auto 16px;">
              Pratinjau langsung di browser tidak didukung untuk tipe berkas ini. Silakan unduh berkas untuk memeriksa dokumen lengkap di komputer Anda.
            </p>
            <a href="{{ $fileUrl }}" download="{{ $submission->original_filename }}" class="btn-admin-primary" style="text-decoration: none; display: inline-flex;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span>Unduh {{ $submission->original_filename }} ({{ $submission->file_size }} KB)</span>
            </a>
          </div>
        @endif
      </div>
    </div>

    <!-- RIGHT: Grading Form -->
    <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 24px; position: sticky; top: 80px;">
      <h3 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        <span>Form Penilaian Guru / Dosen</span>
      </h3>
      <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 20px;">
        Masukkan nilai evaluasi (skala 0–100) serta berikan ulasan feedback untuk mahasiswa.
      </p>

      @if($submission->status === 'graded')
        <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.76rem; font-weight: 700; color: #34d399; text-transform: uppercase;">STATUS: SUDAH DINILAI</span>
            <span style="font-size: 1.25rem; font-weight: 800; color: #10b981; font-family: var(--font-mono);">{{ $submission->grade }} / 100</span>
          </div>
          <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">
            Dinilai oleh: <strong>{{ $submission->gradedByTeacher->name ?? 'Pengajar' }}</strong> &bull; {{ $submission->graded_at ? $submission->graded_at->translatedFormat('d M Y, H:i') : '' }}
          </div>
        </div>
      @endif

      <form action="{{ route('admin.laporan.grade', $submission->id) }}" method="POST">
        @csrf

        <!-- Input Nilai -->
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="inputGrade" style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Nilai Akhir (Skala 0 – 100) <span style="color: #ef4444;">*</span>
          </label>
          <div style="position: relative;">
            <input
              type="number"
              id="inputGrade"
              name="grade"
              min="0"
              max="100"
              step="0.1"
              value="{{ old('grade', $submission->grade) }}"
              required
              placeholder="Contoh: 88.5"
              style="width: 100%; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding: 12px 16px; font-size: 1.2rem; font-weight: 700; color: #10b981; font-family: var(--font-mono);"
            >
          </div>
          <small style="color: #64748b; font-size: 0.74rem; display: block; margin-top: 4px;">Gunakan titik (.) untuk angka desimal, contoh: 87.5 atau 90.</small>
        </div>

        <!-- Textarea Feedback -->
        <div class="form-group" style="margin-bottom: 24px;">
          <label for="inputFeedback" style="display: block; font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Ulasan Feedback & Evaluasi (Opsional)
          </label>
          <textarea
            id="inputFeedback"
            name="teacher_feedback"
            rows="6"
            placeholder="Tuliskan catatan apresiasi, analisis kesalahan skematik, atau saran perbaikan untuk mahasiswa..."
            style="width: 100%; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding: 12px; font-size: 0.84rem; color: #f8fafc; resize: vertical;"
          >{{ old('teacher_feedback', $submission->teacher_feedback) }}</textarea>
        </div>

        <button type="submit" class="btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 0.92rem; font-weight: 700;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <span>Simpan Penilaian</span>
        </button>
      </form>
    </div>
  </div>

  <style>
    @media (max-width: 900px) {
      .admin-eval-grid {
        grid-template-columns: 1fr !important;
      }
    }
  </style>
@endsection