@extends('layouts.admin')

@section('title', 'Pengaturan Profil Admin')

@section('breadcrumb')
  <a href="{{ route('beranda') }}">Beranda</a>
  <span class="separator">/</span>
  <a href="{{ route('admin.modules.index') }}">Panel Admin</a>
  <span class="separator">/</span>
  <span class="current-page">Pengaturan Profil</span>
@endsection

@section('content')
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

  <div class="profile-top-header">
    <a href="{{ route('admin.modules.index') }}" class="btn-profile-back">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      <span>Kembali ke Kelola Modul</span>
    </a>

    <h1 class="profile-page-title">
      <span style="color: var(--color-primary-light); display: inline-flex;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      </span>
      <span>PENGATURAN PROFIL ADMIN</span>
    </h1>
    <p class="profile-page-desc">Kelola nama, email administrator, dan keamanan kata sandi akun Anda.</p>
  </div>

  <div class="profile-cards-stack">
    @include('profile.partials.update-profile-information-form')
    @include('profile.partials.update-password-form')
    @include('profile.partials.delete-user-form')
  </div>
@endsection