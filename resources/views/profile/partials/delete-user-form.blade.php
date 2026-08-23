<div class="profile-card profile-card-danger">
  <div class="profile-card-header">
    <h2 class="profile-section-title" style="color: #f87171;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      <span>Hapus Akun Pengguna</span>
    </h2>
    <p class="profile-section-desc">
      Setelah akun Anda dihapus, semua data progress modul, riwayat praktikum, dan berkas laporan akan dihapus secara permanen.
    </p>
  </div>

  <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun ini secara permanen? Tindakan ini tidak dapat dibatalkan.');" class="profile-form">
    @csrf
    @method('delete')

    <div class="form-group">
      <label for="delete_password" class="profile-label">Masukkan Kata Sandi untuk Konfirmasi Penghapusan</label>
      <input id="delete_password" name="password" type="password" class="profile-input @if($errors->userDeletion->has('password')) is-invalid @endif" placeholder="Kata sandi akun Anda" style="max-width: 400px;" required>
      @if($errors->userDeletion->has('password'))
        <span class="profile-error">{{ $errors->userDeletion->first('password') }}</span>
      @endif
    </div>

    <div>
      <button type="submit" class="btn-profile-danger">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        <span>Hapus Akun Saya</span>
      </button>
    </div>
  </form>
</div>