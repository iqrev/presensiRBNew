@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.karyawan.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem; display:flex; align-items:center; gap:4px;">
            <i class="ph ph-arrow-left"></i> Kembali
        </a>
        <div class="page-title" style="margin-top:8px;">
            <i class="ph ph-user-plus" style="color:var(--primary);"></i> Tambah Karyawan
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.karyawan.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                    @error('name')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Username <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Contoh: budi123" required>
                        @error('username')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="budi@kantor.com">
                        @error('email')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK (Opsional)</label>
                        <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" placeholder="Nomor Induk Karyawan">
                        @error('nik')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="Contoh: Staff IT">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departemen</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="Contoh: Teknologi">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">No. HP (Opsional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 08123456789">
                </div>
                <div class="alert alert-info" style="margin-bottom:16px;">
                    <i class="ph ph-info"></i>
                    <div>Setelah karyawan ditambahkan, jangan lupa upload foto referensi wajah agar sistem/mesin presensi dapat mendeteksi wajah karyawan tersebut.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-full btn-lg">
                    <i class="ph ph-floppy-disk"></i> Simpan Data Karyawan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
