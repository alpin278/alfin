<div class="profile-card">
  <div class="profile-card-header">
    <h2 class="profile-section-title">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      <span>Informasi Akun</span>
    </h2>
    <p class="profile-section-desc">
      Perbarui informasi profil, identitas mahasiswa, dan alamat email akun Anda.
    </p>
  </div>

  <form method="post" action="{{ route('profile.update') }}" class="profile-form">
    @csrf
    @method('patch')

    <div class="form-group">
      <label for="name" class="profile-label">Nama Lengkap <span style="color: #ef4444;">*</span></label>
      <input id="name" name="name" type="text" class="profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
      @error('name')
        <span class="profile-error">{{ $message }}</span>
      @enderror
    </div>

    @if(Auth::user()->role !== 'admin')
      <div class="form-group">
        <label for="nim" class="profile-label">Nomor Induk Mahasiswa (NIM)</label>
        <input id="nim" name="nim" type="text" class="profile-input @error('nim') is-invalid @enderror" value="{{ old('nim', $user->nim) }}" placeholder="Contoh: 21076001" autocomplete="nim">
        @error('nim')
          <span class="profile-error">{{ $message }}</span>
        @enderror
      </div>
    @endif

    <div class="form-group">
      <label for="email" class="profile-label">Alamat Email <span style="color: #ef4444;">*</span></label>
      <input id="email" name="email" type="email" class="profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
      @error('email')
        <span class="profile-error">{{ $message }}</span>
      @enderror
    </div>

    <div class="profile-action-row">
      <button type="submit" class="btn-profile-save">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        <span>Simpan Perubahan</span>
      </button>

      @if (session('status') === 'profile-updated')
        <span class="profile-toast-success">✓ Data profil berhasil diperbarui.</span>
      @endif
    </div>
  </form>
</div>