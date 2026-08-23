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
    <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">TOTAL LAPORAN</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #f8fafc; font-family: var(--font-mono);">{{ $totalCount }}</div>
      </div>
    </div>
    <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">BELUM DINILAI (PENDING)</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #fbbf24; font-family: var(--font-mono);">{{ $submittedCount }}</div>
      </div>
    </div>
    <div style="background: var(--color-bg-card, #1e293b); border: 1px solid var(--color-border, #334155); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">SUDAH DINILAI</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: #34d399; font-family: var(--font-mono);">{{ $gradedCount }}</div>
      </div>
    </div>
  </div>

  <!-- Filters & Search Toolbar -->
  <div style="background: var(--color-bg-surface, #0f172a); border: 1px solid var(--color-border, #334155); border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
    <!-- Status Tabs -->
    <div style="display: flex; gap: 8px; align-items: center; overflow-x: auto;">
      <a href="{{ route('admin.laporan.index', ['status' => 'all', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'all' ? 'background: var(--color-primary-blue, #0284c7); color: #ffffff;' : 'background: #1e293b; color: #cbd5e1;' }}">
        Semua ({{ $totalCount }})
      </a>
      <a href="{{ route('admin.laporan.index', ['status' => 'submitted', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'submitted' ? 'background: #f59e0b; color: #0f172a;' : 'background: #1e293b; color: #cbd5e1;' }}">
        Belum Dinilai ({{ $submittedCount }})
      </a>
      <a href="{{ route('admin.laporan.index', ['status' => 'graded', 'search' => $search]) }}" style="padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; {{ $statusFilter === 'graded' ? 'background: #10b981; color: #ffffff;' : 'background: #1e293b; color: #cbd5e1;' }}">
        Sudah Dinilai ({{ $gradedCount }})
      </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.laporan.index') }}" style="display: flex; gap: 8px; align-items: center;">
      <input type="hidden" name="status" value="{{ $statusFilter }}">
      <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIM, berkas..." style="background: #1e293b; border: 1px solid #475569; border-radius: 6px; padding: 7px 12px; font-size: 0.82rem; color: #f8fafc; width: 220px;">
      <button type="submit" style="background: var(--color-primary-blue, #0284c7); border: none; color: #ffffff; padding: 7px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
        Cari
      </button>
      @if($search)
        <a href="{{ route('admin.laporan.index', ['status' => $statusFilter]) }}" style="color: #94a3b8; font-size: 0.8rem; text-decoration: underline;">Reset</a>
      @endif
    </form>
  </div>

  <!-- DESKTOP TABLE VIEW -->
  <div class="admin-table-wrapper admin-desktop-table">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 220px;">Mahasiswa</th>
          <th style="width: 200px;">Target Praktikum</th>
          <th>Berkas Laporan</th>
          <th style="width: 150px;">Tanggal Submit</th>
          <th style="width: 140px; text-align: center;">Status / Nilai</th>
          <th style="width: 140px; text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($submissions as $sub)
          <tr>
            <td>
              <div style="font-weight: 700; color: #f8fafc;">{{ $sub->user->name ?? 'Mahasiswa' }}</div>
              <div style="font-size: 0.76rem; color: #94a3b8; font-family: var(--font-mono);">
                NIM: {{ $sub->user->nim ?? '-' }}
              </div>
              <div style="font-size: 0.72rem; color: #64748b;">{{ $sub->user->email ?? '' }}</div>
            </td>
            <td>
              @if($sub->reportable)
                @if($sub->reportable_type === 'App\Models\Module')
                  <span class="materi-number" style="font-size: 0.68rem; margin-bottom: 4px; display: inline-block;">MODUL {{ sprintf('%02d', $sub->reportable->module_number ?? 1) }}</span>
                  <div style="font-weight: 600; font-size: 0.85rem; color: #cbd5e1;">{{ $sub->reportable->title }}</div>
                @else
                  <span class="materi-number" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); font-size: 0.68rem; margin-bottom: 4px; display: inline-block;">STUDI KASUS</span>
                  <div style="font-weight: 600; font-size: 0.85rem; color: #cbd5e1;">{{ $sub->reportable->title }}</div>
                @endif
              @else
                <span style="color: #ef4444; font-size: 0.8rem;">(Data terhapus)</span>
              @endif
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: #38bdf8; display: inline-flex; align-items: center;">
                  @if(in_array($sub->file_extension, ['jpg', 'jpeg', 'png', 'webp']))
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                  @elseif($sub->file_extension === 'pdf')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                  @else
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                  @endif
                </span>
                <div style="min-width: 0;">
                  <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" style="color: #38bdf8; font-weight: 600; font-size: 0.84rem; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $sub->original_filename }}
                  </a>
                  <span style="font-size: 0.72rem; color: #64748b;">.{{ strtoupper($sub->file_extension) }} &bull; {{ $sub->file_size }} KB</span>
                </div>
              </div>
              @if($sub->note)
                <div style="font-size: 0.74rem; color: #cbd5e1; font-style: italic; margin-top: 4px; background: rgba(15, 23, 42, 0.5); padding: 4px 8px; border-radius: 4px;">
                  "{{ Str::limit($sub->note, 60) }}"
                </div>
              @endif
            </td>
            <td>
              <div style="font-size: 0.8rem; color: #cbd5e1;">{{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d M Y') : '-' }}</div>
              <div style="font-size: 0.72rem; color: #64748b;">{{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('H:i') . ' WIB' : '' }}</div>
            </td>
            <td style="text-align: center;">
              @if($sub->status === 'graded')
                <span style="display: inline-block; background: rgba(16, 185, 129, 0.18); border: 1px solid #10b981; color: #34d399; padding: 4px 10px; border-radius: 9999px; font-weight: 800; font-size: 0.82rem; font-family: var(--font-mono);">
                  {{ $sub->grade }} / 100
                </span>
              @else
                <span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(245, 158, 11, 0.18); border: 1px solid #f59e0b; color: #fbbf24; padding: 4px 10px; border-radius: 9999px; font-weight: 700; font-size: 0.74rem;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  Belum Dinilai
                </span>
              @endif
            </td>
            <td style="text-align: center;">
              <a href="{{ route('admin.laporan.show', $sub->id) }}" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-decoration: none; {{ $sub->status === 'graded' ? 'background: #334155; color: #f8fafc;' : 'background: #0284c7; color: #ffffff;' }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span>{{ $sub->status === 'graded' ? 'Ubah Nilai' : 'Beri Nilai' }}</span>
              </a>
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

  <!-- Pagination -->
  @if($submissions->hasPages())
    <div style="margin-top: 24px;">
      {{ $submissions->links() }}
    </div>
  @endif
@endsection