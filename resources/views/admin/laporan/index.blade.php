@extends('layouts.admin')

@section('title', 'Laporan Praktikum Masuk')

@section('breadcrumb')
  <a href="{{ route('beranda') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <span class="current-page">Laporan Masuk</span>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert-box-success">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <!-- Header Section -->
  <div class="admin-header-row">
    <div class="admin-title-group">
      <h1>
        <span style="color: var(--color-primary-light); display: inline-flex; vertical-align: middle;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </span>
        LAPORAN PRAKTIKUM MASUK
      </h1>
      <p>Periksa berkas laporan praktikum mahasiswa, berikan evaluasi penilaian, dan feedback pembelajaran.</p>
    </div>
  </div>

  <!-- Metric Stat Cards -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--shadow-card);">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--color-primary-subtle, #eaf2ff); color: var(--color-primary, #2563eb); display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--color-text-secondary, #64748b); font-weight: 600;">TOTAL LAPORAN</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: var(--color-text-primary, #0f172a); font-family: var(--font-mono);">{{ $totalCount }}</div>
      </div>
    </div>
    <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--shadow-card);">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--color-text-secondary, #64748b); font-weight: 600;">BELUM DINILAI (PENDING)</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #d97706; font-family: var(--font-mono);">{{ $submittedCount }}</div>
      </div>
    </div>
    <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: var(--shadow-card);">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #059669; display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--color-text-secondary, #64748b); font-weight: 600;">SUDAH DINILAI</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #059669; font-family: var(--font-mono);">{{ $gradedCount }}</div>
      </div>
    </div>
  </div>

  <!-- Filters & Search Toolbar -->
  <div style="background: var(--color-bg-card, #ffffff); border: 1px solid var(--color-border, #dce5f0); border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px; box-shadow: var(--shadow-card);">
    <!-- Status Tabs -->
    <div style="display: flex; gap: 8px; align-items: center; overflow-x: auto;">
      <a href="{{ route('admin.laporan.index', ['status' => 'all', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'all' ? 'background: var(--color-primary, #2563eb); color: #ffffff;' : 'background: var(--color-bg-surface-secondary, #f1f5fb); color: var(--color-text-secondary, #475569); border: 1px solid var(--color-border, #dce5f0);' }}">
        Semua ({{ $totalCount }})
      </a>
      <a href="{{ route('admin.laporan.index', ['status' => 'submitted', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'submitted' ? 'background: #f59e0b; color: #ffffff;' : 'background: var(--color-bg-surface-secondary, #f1f5fb); color: var(--color-text-secondary, #475569); border: 1px solid var(--color-border, #dce5f0);' }}">
        Belum Dinilai ({{ $submittedCount }})
      </a>
      <a href="{{ route('admin.laporan.index', ['status' => 'graded', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'graded' ? 'background: #10b981; color: #ffffff;' : 'background: var(--color-bg-surface-secondary, #f1f5fb); color: var(--color-text-secondary, #475569); border: 1px solid var(--color-border, #dce5f0);' }}">
        Sudah Dinilai ({{ $gradedCount }})
      </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.laporan.index') }}" style="display: flex; gap: 8px; align-items: center; margin: 0;">
      <input type="hidden" name="status" value="{{ $statusFilter }}">
      <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIM, berkas..." style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #dce5f0); border-radius: 6px; padding: 7px 12px; font-size: 0.82rem; color: var(--color-text-primary, #0f172a); width: 220px;">
      <button type="submit" class="btn-admin-primary" style="padding: 7px 14px; font-size: 0.82rem; min-height: unset;">Cari</button>
      @if($search)
        <a href="{{ route('admin.laporan.index', ['status' => $statusFilter]) }}" style="color: var(--color-text-secondary, #64748b); font-size: 0.8rem; text-decoration: underline;">Reset</a>
      @endif
    </form>
  </div>

  <!-- DESKTOP TABLE VIEW (Visible only on md+ / Desktop) -->
  <div class="admin-table-wrapper admin-desktop-table hidden md:block" style="overflow: hidden; width: 100%;">
    <table class="admin-table" style="table-layout: fixed; width: 100%;">
      <thead>
        <tr>
          <th style="width: 18%;">Mahasiswa</th>
          <th style="width: 18%;">Target Praktikum</th>
          <th style="width: 22%;">Berkas Laporan</th>
          <th style="width: 11%;">Tanggal Submit</th>
          <th style="width: 14%; text-align: center;">Status / Nilai</th>
          <th style="width: 17%; text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($submissions as $sub)
          <tr>
            <td>
              <div style="font-weight: 700; color: var(--color-text-primary, #0f172a); font-size: 0.88rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sub->user->name ?? 'Mahasiswa' }}">{{ $sub->user->name ?? 'Mahasiswa' }}</div>
              <div style="font-size: 0.74rem; color: var(--color-text-muted, #94a3b8); font-family: var(--font-mono); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                NIM: <span style="color: var(--color-primary, #2563eb); font-weight: 700;">{{ $sub->user->nim ?? '-' }}</span>
              </div>
              <div style="font-size: 0.70rem; color: var(--color-text-secondary, #64748b); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sub->user->email ?? '' }}">{{ $sub->user->email ?? '' }}</div>
            </td>
            <td>
              @if($sub->reportable)
                @if($sub->reportable_type === 'App\Models\Module')
                  <span style="background: var(--color-primary-subtle, #eaf2ff); border: 1px solid #bfdbfe; color: var(--color-primary-hover, #1d4ed8); font-size: 0.68rem; font-weight: 700; font-family: var(--font-mono); padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 3px;">MODUL {{ sprintf('%02d', $sub->reportable->module_number ?? 1) }}</span>
                  <div style="font-weight: 600; font-size: 0.82rem; color: var(--color-text-primary, #0f172a); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sub->reportable->title }}">{{ $sub->reportable->title }}</div>
                @else
                  <span style="background: var(--color-primary-subtle, #eaf2ff); border: 1px solid #bfdbfe; color: var(--color-primary, #2563eb); font-size: 0.68rem; font-weight: 700; font-family: var(--font-mono); padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 3px;">STUDI KASUS</span>
                  <div style="font-weight: 600; font-size: 0.82rem; color: var(--color-text-primary, #0f172a); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $sub->reportable->title }}">{{ $sub->reportable->title }}</div>
                @endif
              @else
                <span style="color: #ef4444; font-size: 0.8rem;">(Data terhapus)</span>
              @endif
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 7px; width: 100%; min-width: 0;">
                <span style="color: var(--color-primary, #2563eb); display: inline-flex; align-items: center; flex-shrink: 0;">
                  @if(in_array($sub->file_extension, ['jpg', 'jpeg', 'png', 'webp']))
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                  @elseif($sub->file_extension === 'pdf')
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                  @else
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                  @endif
                </span>
                <div style="min-width: 0; flex: 1; overflow: hidden;">
                  <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="file-name-truncate" title="{{ $sub->original_filename }}" style="color: var(--color-primary, #2563eb); font-weight: 600; font-size: 0.82rem; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;">
                    {{ $sub->original_filename }}
                  </a>
                  <span style="font-size: 0.70rem; color: var(--color-text-secondary, #64748b); display: block;">.{{ strtoupper($sub->file_extension) }} &bull; {{ $sub->file_size }} KB</span>
                </div>
              </div>
              @if($sub->note)
                <div class="report-note-truncate" title="{{ $sub->note }}" style="font-size: 0.72rem; color: var(--color-text-secondary, #475569); font-style: italic; margin-top: 3px; background: var(--color-bg-surface-secondary, #f1f5fb); border: 1px solid var(--color-border, #dce5f0); padding: 3px 6px; border-radius: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  "{{ Str::limit($sub->note, 45) }}"
                </div>
              @endif
            </td>
            <td>
              <div style="font-size: 0.78rem; color: var(--color-text-primary, #0f172a); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d M Y') : '-' }}</div>
              <div style="font-size: 0.70rem; color: var(--color-text-secondary, #64748b);">{{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('H:i') . ' WIB' : '' }}</div>
            </td>
            <td style="text-align: center;">
              @if($sub->edit_request_status === 'requested')
                <span style="display: inline-flex; align-items: center; gap: 3px; background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #b45309; padding: 3px 7px; border-radius: 9999px; font-weight: 700; font-size: 0.70rem; white-space: nowrap;" title="Mahasiswa mengajukan izin upload ulang">
                  ⚠️ Minta Edit
                </span>
              @elseif($sub->edit_request_status === 'approved' && !$sub->isEditDeadlinePassed())
                <span style="display: inline-flex; align-items: center; gap: 3px; background: var(--color-primary-subtle, #eaf2ff); border: 1px solid #bfdbfe; color: var(--color-primary-hover, #1d4ed8); padding: 3px 7px; border-radius: 9999px; font-weight: 700; font-size: 0.70rem; white-space: nowrap;" title="Izin edit disetujui, menunggu upload ulang mahasiswa">
                  ⏳ Tunggu Upload
                </span>
              @elseif($sub->status === 'graded')
                <span class="status-pill-graded">
                  {{ $sub->grade }} / 100
                </span>
              @else
                <span class="status-pill-pending">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  Belum Dinilai
                </span>
              @endif
            </td>
            <td style="text-align: center;">
              <div style="display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                @if($sub->edit_request_status === 'requested')
                  <a href="{{ route('admin.laporan.show', $sub->id) }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 12px; border-radius: 6px; font-size: 0.76rem; font-weight: 700; text-decoration: none; white-space: nowrap; background: #d97706; color: #ffffff; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.3); transition: all 0.15s;" onmouseover="this.style.background='#b45309'" onmouseout="this.style.background='#d97706'" title="Tinjau permohonan izin edit">
                    <span>Proses Izin</span>
                  </a>
                @elseif($sub->status === 'graded')
                  <a href="{{ route('admin.laporan.show', $sub->id) }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 12px; border-radius: 6px; font-size: 0.76rem; font-weight: 700; text-decoration: none; white-space: nowrap; background: var(--color-primary-subtle, #eaf2ff); border: 1px solid #bfdbfe; color: var(--color-primary, #2563eb); transition: all 0.15s;" onmouseover="this.style.background='#2563eb'; this.style.color='#ffffff'" onmouseout="this.style.background='var(--color-primary-subtle, #eaf2ff)'; this.style.color='var(--color-primary, #2563eb)'" title="Ubah nilai laporan">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Ubah Nilai</span>
                  </a>
                @else
                  <a href="{{ route('admin.laporan.show', $sub->id) }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 12px; border-radius: 6px; font-size: 0.76rem; font-weight: 700; text-decoration: none; white-space: nowrap; background: var(--color-primary, #2563eb); color: #ffffff; box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3); transition: all 0.15s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='var(--color-primary, #2563eb)'" title="Beri nilai laporan">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Beri Nilai</span>
                  </a>
                @endif

                <!-- Tombol Hapus (Admin Only) -->
                <form action="{{ route('admin.laporan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini? Data laporan akan dihapus.');" style="margin: 0; display: inline-flex;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.25)'; this.style.color='#fca5a5'" onmouseout="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.color='#f87171'" title="Hapus laporan ini">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 40px;">
              Tidak ada laporan praktikum yang sesuai dengan filter pencarian.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- MOBILE CARDS VIEW (Visible only on < md / Mobile) -->
  <div class="admin-cards-mobile block md:hidden">
    @forelse($submissions as $sub)
      <div class="admin-report-card">
        <!-- Student Info & Status Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
          <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #38bdf8); color: #ffffff; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              {{ strtoupper(substr($sub->user->name ?? 'M', 0, 1)) }}
            </div>
            <div style="min-width: 0;">
              <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--color-text-primary, #0f172a); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $sub->user->name ?? 'Mahasiswa' }}
              </h3>
              <div style="font-size: 0.75rem; color: var(--color-text-secondary, #64748b); font-family: var(--font-mono); margin-top: 1px;">
                NIM: <span style="color: var(--color-primary, #2563eb); font-weight: 700;">{{ $sub->user->nim ?? '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Status Badge -->
          <div>
            @if($sub->edit_request_status === 'requested')
              <span style="display: inline-block; background: #faf5ff; border: 1px solid #d8b4fe; color: #7e22ce; padding: 3px 8px; border-radius: 9999px; font-weight: 700; font-size: 0.72rem; white-space: nowrap;">
                ⚠️ Minta Edit
              </span>
            @elseif($sub->edit_request_status === 'approved' && !$sub->isEditDeadlinePassed())
              <span style="display: inline-block; background: var(--color-bg-surface-soft, #eaf2ff); border: 1px solid #bfdbfe; color: var(--color-primary, #2563eb); padding: 3px 8px; border-radius: 9999px; font-weight: 700; font-size: 0.72rem; white-space: nowrap;">
                ⏳ Tunggu Upload
              </span>
            @elseif($sub->status === 'graded')
              <span style="display: inline-block; background: #ecfdf5; border: 1px solid #10b981; color: #059669; padding: 3px 8px; border-radius: 9999px; font-weight: 800; font-size: 0.78rem; font-family: var(--font-mono); white-space: nowrap;">
                {{ $sub->grade }} / 100
              </span>
            @else
              <span style="display: inline-flex; align-items: center; gap: 3px; background: #fffbeb; border: 1px solid #fde68a; color: #d97706; padding: 3px 8px; border-radius: 9999px; font-weight: 700; font-size: 0.72rem; white-space: nowrap;">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Pending
              </span>
            @endif
          </div>
        </div>

        <!-- Target Materi & Berkas -->
        <div style="background: var(--color-bg-surface-secondary, #f8fafc); border: 1px solid var(--color-border, #e2e8f0); border-radius: 8px; padding: 12px; margin-bottom: 12px; font-size: 0.8rem; display: flex; flex-direction: column; gap: 8px;">
          <div>
            <span style="color: #64748b; font-size: 0.7rem; font-weight: 700; display: block; margin-bottom: 2px;">TARGET PRAKTIKUM:</span>
            @if($sub->reportable)
              @if($sub->reportable_type === 'App\Models\Module')
                <span class="materi-number" style="font-size: 0.65rem; margin-bottom: 2px; display: inline-block;">MODUL {{ sprintf('%02d', $sub->reportable->module_number ?? 1) }}</span>
                <div style="font-weight: 600; color: var(--color-text-primary, #0f172a);">{{ $sub->reportable->title }}</div>
              @else
                <span class="materi-number" style="background: var(--color-bg-surface-soft, #eaf2ff); color: var(--color-primary, #2563eb); border: 1px solid #bfdbfe; font-size: 0.65rem; margin-bottom: 2px; display: inline-block;">STUDI KASUS</span>
                <div style="font-weight: 600; color: var(--color-text-primary, #0f172a);">{{ $sub->reportable->title }}</div>
              @endif
            @else
              <span style="color: #ef4444;">(Data terhapus)</span>
            @endif
          </div>

          <div style="border-top: 1px solid var(--color-border-subtle, #e2e8f0); padding-top: 6px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
              <span style="color: var(--color-primary, #2563eb); display: inline-flex;">
                @if(in_array($sub->file_extension, ['jpg', 'jpeg', 'png', 'webp']))
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                @elseif($sub->file_extension === 'pdf')
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                @else
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                @endif
              </span>
              <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" style="color: var(--color-primary, #2563eb); text-decoration: none; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px; display: inline-block;">
                {{ $sub->original_filename }}
              </a>
              <span style="color: #64748b; font-size: 0.72rem;">({{ $sub->file_size }} KB)</span>
            </div>
            <span style="color: #64748b; font-size: 0.72rem; white-space: nowrap;">
              {{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d/m/Y H:i') : '-' }}
            </span>
          </div>

          @if($sub->note)
            <div style="background: var(--color-bg-surface-soft, #eaf2ff); border-left: 2px solid var(--color-primary, #2563eb); padding: 4px 8px; font-size: 0.74rem; color: #1e3a8a; font-style: italic;">
              "{{ Str::limit($sub->note, 60) }}"
            </div>
          @endif
        </div>

        <!-- Action Buttons (Beri Nilai & Hapus) -->
        <div style="display: flex; gap: 8px; align-items: center;">
          <a href="{{ route('admin.laporan.show', $sub->id) }}" class="btn-admin-primary" style="flex: 1; justify-content: center; min-height: 40px; font-size: 0.84rem; font-weight: 700; text-decoration: none; {{ $sub->status === 'graded' ? 'background: #ffffff; border: 1px solid var(--color-border, #dce5f0); color: var(--color-text-primary, #0f172a);' : '' }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            <span>{{ $sub->edit_request_status === 'requested' ? 'Proses Izin Edit' : ($sub->status === 'graded' ? 'Ubah Nilai' : 'Beri Nilai') }}</span>
          </a>
          <form action="{{ route('admin.laporan.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini? Data laporan akan dihapus.');" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; cursor: pointer;" title="Hapus laporan ini">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="admin-empty-card">
        <p style="color: #94a3b8; margin: 0;">Tidak ada laporan praktikum yang sesuai dengan filter pencarian.</p>
      </div>
    @endforelse
  </div>

  <!-- Pagination -->
  @if($submissions->hasPages())
    <div style="margin-top: 24px;">
      {{ $submissions->links() }}
    </div>
  @endif
@endsection