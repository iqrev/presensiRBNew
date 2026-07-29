@extends('layouts.app')

@section('title', 'Profil & Ganti Password')

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1 class="page-title"><i class="ph ph-user-circle"></i> Profil Pengguna</h1>
        <div class="page-subtitle">Kelola informasi akun dan keamanan Anda</div>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            Informasi Akun
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="form-control" style="background: var(--gray-50); color: var(--gray-500);">
                    {{ Auth::user()->name }}
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email / Username</label>
                <div class="form-control" style="background: var(--gray-50); color: var(--gray-500);">
                    {{ Auth::user()->email ?? Auth::user()->username }}
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Peran (Role)</label>
                <div>
                    <span class="badge badge-primary">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? '-') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-lock-key" style="font-size: 1.2rem; color: var(--primary);"></i>
                Ganti Password
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="current_password">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="Masukkan password lama">
                    @error('current_password')
                        <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Masukkan password baru (min. 8 karakter)">
                    @error('new_password')
                        <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="ph ph-floppy-disk"></i> Simpan Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
