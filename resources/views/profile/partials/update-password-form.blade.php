<div class="profile-card">
  <div class="profile-card-header">
    <h2 class="profile-section-title">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
      <span>Perbarui Kata Sandi</span>
    </h2>
    <p class="profile-section-desc">
      Pastikan akun Anda menggunakan kata sandi yang kuat dan aman untuk menjaga privasi.
    </p>
  </div>

  <form method="post" action="{{ route('password.update') }}" class="profile-form">
    @csrf
    @method('put')

    <div class="form-group">
      <label for="update_password_current_password" class="profile-label">Kata Sandi Saat Ini <span style="color: #ef4444;">*</span></label>
      <input id="update_password_current_password" name="current_password" type="password" class="profile-input @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password" required>
      @if($errors->updatePassword->has('current_password'))
        <span class="profile-error">{{ $errors->updatePassword->first('current_password') }}</span>
      @endif
    </div>

    <div class="form-group">
      <label for="update_password_password" class="profile-label">Kata Sandi Baru <span style="color: #ef4444;">*</span></label>
      <input id="update_password_password" name="password" type="password" class="profile-input @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password" required>
      @if($errors->updatePassword->has('password'))
        <span class="profile-error">{{ $errors->updatePassword->first('password') }}</span>
      @endif
    </div>

    <div class="form-group">
      <label for="update_password_password_confirmation" class="profile-label">Konfirmasi Kata Sandi Baru <span style="color: #ef4444;">*</span></label>
      <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="profile-input @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password" required>
      @if($errors->updatePassword->has('password_confirmation'))
        <span class="profile-error">{{ $errors->updatePassword->first('password_confirmation') }}</span>
      @endif
    </div>

    <div class="profile-action-row">
      <button type="submit" class="btn-profile-save">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        <span>Perbarui Kata Sandi</span>
      </button>

      @if (session('status') === 'password-updated')
        <span class="profile-toast-success">✓ Kata sandi berhasil diperbarui.</span>
      @endif
    </div>
  </form>
</div>