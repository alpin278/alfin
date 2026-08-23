@extends('layouts.admin')

@section('title', 'Edit Modul ' . sprintf('%02d', $module->module_number))

@section('breadcrumb')
  <a href="{{ url('/') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Kelola Modul</a>
  <span class="separator">/</span>
  <span class="current-page">Edit Modul {{ sprintf('%02d', $module->module_number) }}</span>
@endsection

@section('content')
  <div class="admin-form-card">
    <div class="form-card-header">
      <h2>
        <span>✏️</span> Edit Modul {{ sprintf('%02d', $module->module_number) }}: {{ $module->title }}
      </h2>
      <p>Perbarui judul materi, nomor urut kurikulum, atau ringkasan penjelasan modul.</p>
    </div>

    <form action="{{ route('admin.modules.update', $module->id) }}" method="POST">
      @csrf
      @method('PUT')

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
            value="{{ old('module_number', $module->module_number) }}" 
            min="1" 
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
            value="{{ old('icon', $module->icon) }}" 
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
          value="{{ old('title', $module->title) }}" 
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
          required
        >{{ old('description', $module->description) }}</textarea>
        @error('description')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <a href="{{ route('admin.modules.index') }}" class="btn-form-cancel">Batal</a>
        <button type="submit" class="btn-form-submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <span>Simpan Perubahan</span>
        </button>
      </div>
    </form>
  </div>
@endsection
