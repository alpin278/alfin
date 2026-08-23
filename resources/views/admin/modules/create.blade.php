@extends('layouts.admin')

@section('title', 'Tambah Modul Baru')

@section('breadcrumb')
  <a href="{{ url('/') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Kelola Modul</a>
  <span class="separator">/</span>
  <span class="current-page">Tambah Modul</span>
@endsection

@section('content')
  <div class="admin-form-card">
    <div class="form-card-header">
      <h2>
        <span>➕</span> Tambah Modul Pembelajaran Baru
      </h2>
      <p>Isi formulir di bawah ini untuk menambahkan materi praktikum kelistrikan baru ke sistem.</p>
    </div>

    <form action="{{ route('admin.modules.store') }}" method="POST">
      @csrf

      <div class="form-grid-2">
        <!-- Nomor Urut Modul -->
        <div class="form-group">
          <label class="form-label" for="module_number">
            Nomor Urut Modul <span class="required">*</span>
          </label>
          <input 
            type="number" 
            name="module_number" 
            id="module_number" 
            class="form-control @error('module_number') is-invalid @enderror" 
            value="{{ old('module_number', $nextNumber) }}" 
            min="1" 
            placeholder="Contoh: 1, 2, 3..."
            required
          >
          @error('module_number')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <!-- Ikon SVG / Key (Opsional) -->
        <div class="form-group">
          <label class="form-label" for="icon">
            Nama Ikon / Jenis (Opsional)
          </label>
          <input 
            type="text" 
            name="icon" 
            id="icon" 
            class="form-control @error('icon') is-invalid @enderror" 
            value="{{ old('icon', 'zap') }}" 
            placeholder="zap, activity, gauge, book"
          >
          @error('icon')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <!-- Judul Modul -->
      <div class="form-group">
        <label class="form-label" for="title">
          Judul Modul Pembelajaran <span class="required">*</span>
        </label>
        <input 
          type="text" 
          name="title" 
          id="title" 
          class="form-control @error('title') is-invalid @enderror" 
          value="{{ old('title') }}" 
          placeholder="Contoh: Hukum Kirchhoff & Analisis Node" 
          required 
          autofocus
        >
        @error('title')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <!-- Deskripsi Modul -->
      <div class="form-group">
        <label class="form-label" for="description">
          Deskripsi & Rangkuman Materi <span class="required">*</span>
        </label>
        <textarea 
          name="description" 
          id="description" 
          rows="5" 
          class="form-control @error('description') is-invalid @enderror" 
          placeholder="Tuliskan tujuan pembelajaran, konsep kelistrikan dasar, dan ringkasan materi praktikum..." 
          required
        >{{ old('description') }}</textarea>
        @error('description')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <a href="{{ route('admin.modules.index') }}" class="btn-form-cancel">Batal</a>
        <button type="submit" class="btn-form-submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <span>Simpan Modul</span>
        </button>
      </div>
    </form>
  </div>
@endsection
