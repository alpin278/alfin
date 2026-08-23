@extends('layouts.admin')

@section('title', 'Kelola Modul Pembelajaran')

@section('breadcrumb')
  <a href="{{ route('beranda') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <span class="current-page">Kelola Modul</span>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert-box-success">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="alert-box-error">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <!-- Header Section -->
  <div class="admin-header-row">
    <div class="admin-title-group">
      <h1>
        <span>📚</span> KELOLA MODUL PEMBELAJARAN
      </h1>
      <p>Kelola kurikulum praktikum, ubah data teori, dan atur modul kelistrikan yang tampil untuk mahasiswa.</p>
    </div>
    <a href="{{ route('admin.modules.create') }}" class="btn-admin-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>+ Tambah Modul Baru</span>
    </a>
  </div>

  <!-- 1. DESKTOP VIEW: Clean Dark Mode Table (Hidden on <= 768px) -->
  <div class="admin-table-wrapper admin-desktop-table">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 120px; text-align: center;">No. Modul</th>
          <th style="width: 260px;">Judul Modul</th>
          <th style="width: 180px;">Slug URL</th>
          <th>Deskripsi Materi</th>
          <th style="width: 160px; text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($modules as $module)
          <tr>
            <td style="text-align: center;">
              <span class="materi-number">MODUL {{ sprintf('%02d', $module->module_number) }}</span>
            </td>
            <td>
              <strong style="color: #f8fafc; font-size: 0.95rem; font-weight: 700;">{{ $module->title }}</strong>
            </td>
            <td>
              <span class="slug-code">{{ $module->slug }}</span>
            </td>
            <td>
              <p style="color: #94a3b8; font-size: 0.84rem; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $module->description }}
              </p>
            </td>
            <td>
              <div class="action-buttons-cell">
                <a href="{{ route('admin.modules.edit', $module->id) }}" class="btn-table-edit" title="Edit Modul">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  <span>Edit</span>
                </a>
                <form action="{{ route('admin.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul \"{{ $module->title }}\"? Semua riwayat progress mahasiswa pada modul ini juga akan ikut terhapus.');" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-table-delete" title="Hapus Modul">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <span>Hapus</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align: center; padding: 48px 20px; color: #94a3b8;">
              <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
              <p style="margin: 0; font-size: 0.95rem;">Belum ada modul yang terdaftar di database.</p>
              <p style="margin: 4px 0 16px; font-size: 0.8rem; color: #64748b;">Klik tombol di bawah untuk menambahkan modul pertama.</p>
              <a href="{{ route('admin.modules.create') }}" class="btn-admin-primary" style="font-size: 0.82rem;">+ Tambah Modul Sekarang</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- 2. MOBILE VIEW: Vertical Stacked Cards (Visible only on <= 768px) -->
  <div class="admin-cards-mobile">
    @forelse($modules as $module)
      <div class="admin-module-card">
        <!-- Top Row: Badge & Slug -->
        <div class="card-meta-row">
          <span class="materi-number">MODUL {{ sprintf('%02d', $module->module_number) }}</span>
          <span class="slug-code">{{ $module->slug }}</span>
        </div>

        <!-- Title -->
        <h3 class="card-module-title">{{ $module->title }}</h3>

        <!-- Description -->
        <p class="card-module-desc">{{ $module->description }}</p>

        <!-- Bottom Actions (Touch-Friendly min-height 44px) -->
        <div class="card-actions-row">
          <a href="{{ route('admin.modules.edit', $module->id) }}" class="btn-card-edit" title="Edit Modul">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            <span>Edit Modul</span>
          </a>
          <form action="{{ route('admin.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul \"{{ $module->title }}\"? Semua riwayat progress mahasiswa pada modul ini juga akan ikut terhapus.');" class="form-card-delete">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-card-delete" title="Hapus Modul">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              <span>Hapus</span>
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="admin-empty-card">
        <div style="font-size: 2.2rem; margin-bottom: 8px;">📭</div>
        <p style="margin: 0; font-size: 0.95rem; font-weight: 600; color: #f8fafc;">Belum ada modul terdaftar.</p>
        <p style="margin: 4px 0 16px; font-size: 0.82rem; color: #94a3b8;">Klik tombol di bawah untuk menambahkan modul pertama.</p>
        <a href="{{ route('admin.modules.create') }}" class="btn-admin-primary" style="width: 100%; min-height: 44px;">+ Tambah Modul Sekarang</a>
      </div>
    @endforelse
  </div>
@endsection
