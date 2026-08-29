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

  <div class="admin-eval-page">
    <!-- Top Action Row -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
      <a href="{{ route('admin.laporan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 0.88rem; transition: color 0.2s;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        <span>Kembali ke Daftar Laporan</span>
      </a>

      <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ asset('storage/' . $submission->file_path) }}" download="{{ $submission->original_filename }}" class="btn-admin-primary" style="background: #334155; border: 1px solid #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span>Unduh File Asli</span>
        </a>

        <form action="{{ route('admin.laporan.destroy', $submission->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini? Data laporan akan dihapus.');" style="margin: 0;">
          @csrf
          @method('DELETE')
          <button type="submit" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; border-radius: 8px; padding: 8px 14px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.3)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.15)'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            <span>Hapus Laporan</span>
          </button>
        </form>
      </div>
    </div>

    <!-- 2-Column Responsive Layout -->
    <div class="admin-eval-grid">
      <!-- LEFT: Student Info & File Preview -->
      <div style="display: flex; flex-direction: column; gap: 20px; min-width: 0;">
        <!-- Mahasiswa & Target Praktikum Card -->
        <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
          <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #38bdf8); color: #ffffff; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              {{ strtoupper(substr($submission->user->name ?? 'M', 0, 1)) }}
            </div>
            <div style="min-width: 0;">
              <h2 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $submission->user->name }}</h2>
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
            <div style="min-width: 0;">
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
        <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
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
            <div style="text-align: center; padding: 36px 20px; background: rgba(15, 23, 42, 0.6); border: 1px dashed #475569; border-radius: 8px;">
              <div style="display: inline-flex; color: #38bdf8; margin-bottom: 12px;">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
              </div>
              <div style="font-weight: 700; color: #f8fafc; font-size: 1rem; margin-bottom: 6px;">Berkas Dokumen: .{{ strtoupper($ext) }}</div>
              <p style="color: #94a3b8; font-size: 0.82rem; max-width: 380px; margin: 0 auto 18px; line-height: 1.5;">
                Pratinjau langsung di browser tidak didukung untuk tipe berkas ini. Silakan unduh berkas untuk memeriksa dokumen lengkap di komputer Anda.
              </p>
              <div style="display: flex; justify-content: center;">
                <a href="{{ $fileUrl }}" download="{{ $submission->original_filename }}" class="btn-eval-download" title="Unduh {{ $submission->original_filename }} ({{ $submission->file_size }} KB)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex-shrink: 0;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="btn-eval-filename">Unduh {{ $submission->original_filename }}</span>
                  <span class="btn-eval-filesize">({{ $submission->file_size }} KB)</span>
                </a>
              </div>
            </div>
          @endif
        </div>
      </div>      <!-- RIGHT: Grading Form & Edit Request Actions -->
      <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 12px; padding: 24px; position: sticky; top: 80px; box-shadow: 0 4px 16px rgba(0,0,0,0.2); min-width: 0;">
        
        <!-- EDIT REQUEST STATUS & ACTIONS -->
        @if($submission->edit_request_status === 'requested')
          <div style="background: rgba(168, 85, 247, 0.15); border: 1px solid #c084fc; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; color: #e9d5ff; font-weight: 700; font-size: 0.92rem; margin-bottom: 6px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
              <span>Permintaan Izin Edit Laporan</span>
            </div>
            <p style="font-size: 0.8rem; color: #cbd5e1; margin: 0 0 12px; line-height: 1.5;">
              Mahasiswa <strong>{{ $submission->user->name }}</strong> mengajukan permohonan untuk mengunggah ulang laporan ini pada <strong>{{ $submission->edit_requested_at ? $submission->edit_requested_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</strong>.
            </p>
            <div style="display: flex; gap: 10px;">
              <button type="button" onclick="openApproveModal()" style="flex: 1; background: #0284c7; border: none; color: #ffffff; padding: 9px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Setujui (Approve)</span>
              </button>
              <form action="{{ route('admin.laporan.reject_edit', $submission->id) }}" method="POST" onsubmit="return confirm('Tolak permohonan izin edit dari mahasiswa ini?');" style="flex: 1; margin: 0;">
                @csrf
                <button type="submit" style="width: 100%; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 9px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                  <span>Tolak (Reject)</span>
                </button>
              </form>
            </div>
          </div>
        @elseif($submission->edit_request_status === 'approved' && !$submission->isEditDeadlinePassed())
          <div style="background: rgba(56, 189, 248, 0.12); border: 1px solid #38bdf8; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #38bdf8; font-size: 0.88rem; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
              <span>⏳ Izin Edit Aktif (Menunggu Upload Ulang)</span>
            </div>
            <div style="font-size: 0.8rem; color: #cbd5e1;">
              Batas waktu upload mahasiswa: <strong>{{ $submission->edit_deadline ? $submission->edit_deadline->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</strong> ({{ $submission->edit_deadline ? $submission->edit_deadline->diffForHumans() : '' }}).
            </div>
          </div>
        @elseif($submission->edit_request_status === 'expired')
          <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.8rem; color: #f87171;">
            ⚠️ Batas waktu upload ulang laporan untuk mahasiswa ini telah kadaluarsa (Expired).
          </div>
        @elseif($submission->edit_request_status === 'rejected')
          <div style="background: rgba(148, 163, 184, 0.1); border: 1px solid #475569; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; font-size: 0.8rem; color: #94a3b8;">
            Permintaan izin edit mahasiswa sebelumnya telah ditolak.
          </div>
        @endif

        <h3 style="font-size: 1.1rem; font-weight: 800; color: #f8fafc; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
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
                style="width: 100%; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding: 12px 16px; font-size: 1.2rem; font-weight: 700; color: #10b981; font-family: var(--font-mono); box-sizing: border-box;"
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
              style="width: 100%; background: #0f172a; border: 1px solid #475569; border-radius: 8px; padding: 12px; font-size: 0.84rem; color: #f8fafc; resize: vertical; box-sizing: border-box;"
            >{{ old('teacher_feedback', $submission->teacher_feedback) }}</textarea>
          </div>

          <button type="submit" class="btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 0.92rem; font-weight: 700; box-sizing: border-box;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <span>Simpan Penilaian</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Approve Edit & Set Deadline -->
  <div id="approveModalBackdrop" style="position: fixed; inset: 0; background: rgba(3, 7, 18, 0.75); backdrop-filter: blur(6px); z-index: 2000; display: none; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 16px;">
        <h3 style="color: #f8fafc; font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
          <span>Setujui Izin Edit Laporan</span>
        </h3>
        <button onclick="closeApproveModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer;" aria-label="Tutup">✕</button>
      </div>

      <form action="{{ route('admin.laporan.approve_edit', $submission->id) }}" method="POST">
        @csrf
        <p style="font-size: 0.82rem; color: #94a3b8; margin: 0 0 14px; line-height: 1.5;">
          Menyetujui izin edit akan <strong>mereset nilai saat ini</strong> menjadi kosong dan membuka akses upload ulang bagi mahasiswa hingga batas waktu yang ditentukan.
        </p>

        <div style="margin-bottom: 20px;">
          <label for="edit_deadline" style="display: block; font-size: 0.8rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;">
            Batas Waktu (Deadline) Upload Ulang: <span style="color: #ef4444;">*</span>
          </label>
          <input
            type="datetime-local"
            name="edit_deadline"
            id="edit_deadline"
            required
            min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
            value="{{ now()->addDays(2)->format('Y-m-d\T23:59') }}"
            style="width: 100%; background: #1e293b; border: 1px solid #475569; border-radius: 6px; padding: 10px 12px; color: #f8fafc; font-size: 0.88rem; box-sizing: border-box;"
          >
          <small style="color: #64748b; font-size: 0.72rem; display: block; margin-top: 4px;">Contoh: 2 hari ke depan pukul 23:59 WIB.</small>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" onclick="closeApproveModal()" style="background: transparent; border: 1px solid #475569; color: #cbd5e1; padding: 8px 16px; border-radius: 6px; font-size: 0.82rem; cursor: pointer;">Batal</button>
          <button type="submit" class="btn-admin-primary" style="font-size: 0.82rem; padding: 8px 20px;">
            <span>Simpan & Beri Izin</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openApproveModal() {
      document.getElementById('approveModalBackdrop').style.display = 'flex';
    }
    function closeApproveModal() {
      document.getElementById('approveModalBackdrop').style.display = 'none';
    }
  </script>

  <style>
    .admin-eval-page {
      width: 100%;
      max-width: 1140px;
      margin: 0 auto;
      box-sizing: border-box;
    }

    .admin-eval-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
      gap: 24px;
      align-items: flex-start;
      width: 100%;
    }

    .btn-eval-download {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, #0284c7, #0369a1);
      border: 1px solid rgba(56, 189, 248, 0.4);
      color: #ffffff;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 0.86rem;
      font-weight: 600;
      text-decoration: none;
      max-width: 380px;
      width: 100%;
      box-sizing: border-box;
      transition: all 0.2s ease;
      box-shadow: 0 2px 10px rgba(2, 132, 199, 0.3);
    }

    .btn-eval-download:hover {
      background: linear-gradient(135deg, #0369a1, #0284c7);
      box-shadow: 0 4px 14px rgba(56, 189, 248, 0.45);
      transform: translateY(-1px);
    }

    .btn-eval-filename {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 220px;
      display: inline-block;
      vertical-align: middle;
    }

    .btn-eval-filesize {
      font-size: 0.78rem;
      color: #bae6fd;
      flex-shrink: 0;
    }

    @media (max-width: 900px) {
      .admin-eval-grid {
        grid-template-columns: 1fr !important;
      }
      .btn-eval-filename {
        max-width: 180px;
      }
    }
  </style>
@endsection