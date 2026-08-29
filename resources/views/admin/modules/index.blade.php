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
        <span style="display: inline-flex; vertical-align: middle;">📚</span> KELOLA MODUL PEMBELAJARAN
      </h1>
      <p>Kelola kurikulum praktikum, ubah data teori, dan atur modul kelistrikan yang tampil untuk mahasiswa.</p>
    </div>
    <a href="{{ route('admin.modules.create') }}" class="btn-admin-primary" style="background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%); border: none; font-size: 0.88rem; font-weight: 700; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      <span>+ Tambah Modul Baru</span>
    </a>
  </div>

  <!-- 1. DESKTOP VIEW: Clean Dark Mode Table (Visible only on md+ / Desktop) -->
  <div class="admin-table-wrapper admin-desktop-table hidden md:block" style="overflow: hidden; width: 100%;">
    <table class="admin-table" style="table-layout: fixed; width: 100%;">
      <thead>
        <tr>
          <th style="width: 13%; text-align: center;">NO. MODUL</th>
          <th style="width: 25%;">JUDUL MODUL</th>
          <th style="width: 18%;">SLUG URL</th>
          <th style="width: 26%;">DESKRIPSI MATERI</th>
          <th style="width: 18%; text-align: center;">AKSI</th>
        </tr>
      </thead>
      <tbody>
        @forelse($modules as $module)
          <tr>
            <td style="text-align: center;">
              <span class="materi-number" style="font-size: 0.70rem; padding: 3px 8px; border-radius: 9999px; white-space: nowrap;">
                MODUL {{ sprintf('%02d', $module->module_number) }}
              </span>
            </td>
            <td>
              <strong class="cell-truncate" title="{{ $module->title }}" style="color: #f8fafc; font-size: 0.90rem; font-weight: 700; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $module->title }}
              </strong>
            </td>
            <td>
              <span class="slug-code cell-truncate" title="{{ $module->slug }}" style="font-size: 0.76rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                {{ $module->slug }}
              </span>
            </td>
            <td>
              <p class="cell-truncate" title="{{ $module->description }}" style="color: #94a3b8; font-size: 0.82rem; line-height: 1.4; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $module->description }}
              </p>
            </td>
            <td style="text-align: center;">
              <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                <a href="{{ route('admin.modules.edit', $module->id) }}" style="display: inline-flex; align-items: center; gap: 5px; background: rgba(2, 132, 199, 0.15); border: 1px solid #0284c7; color: #38bdf8; padding: 6px 12px; border-radius: 6px; font-size: 0.76rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.15s;" onmouseover="this.style.background='#0284c7'; this.style.color='#ffffff'" onmouseout="this.style.background='rgba(2, 132, 199, 0.15)'; this.style.color='#38bdf8'" title="Edit Modul">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  <span>Edit</span>
                </a>
                <form action="{{ route('admin.modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul \"{{ $module->title }}\"? Semua riwayat progress mahasiswa pada modul ini juga akan ikut terhapus.');" style="display: inline; margin: 0;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" style="display: inline-flex; align-items: center; gap: 5px; background: rgba(239, 68, 68, 0.12); border: 1px solid #ef4444; color: #f87171; padding: 6px 12px; border-radius: 6px; font-size: 0.76rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.15s;" onmouseover="this.style.background='#dc2626'; this.style.color='#ffffff'" onmouseout="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.color='#f87171'" title="Hapus Modul">
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

  <!-- 2. MOBILE VIEW: Vertical Stacked Cards (Visible only on < md / Mobile) -->
  <div class="admin-cards-mobile block md:hidden">
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
