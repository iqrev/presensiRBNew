@extends('layouts.app')
@section('title', 'Edit Karyawan')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.karyawan.show', $karyawan) }}" style="color:var(--primary);text-decoration:none;font-size:.875rem; display:flex; align-items:center; gap:4px;">
            <i class="ph ph-arrow-left"></i> Kembali
        </a>
        <div class="page-title" style="margin-top:8px;">
            <i class="ph ph-pencil-simple" style="color:var(--primary);"></i> Edit: {{ $karyawan->name }}
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.karyawan.update', $karyawan) }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $karyawan->name) }}" required>
                    @error('name')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Username <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $karyawan->username) }}" required>
                        @error('username')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $karyawan->email) }}">
                        @error('email')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control" value="{{ old('nik', $karyawan->nik) }}">
                        @error('nik')<div class="form-error" style="font-size:0.8rem; color:var(--danger); margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $karyawan->jabatan) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departemen</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department', $karyawan->department) }}">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $karyawan->phone) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span style="color:var(--danger);">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="aktif"    {{ old('status', $karyawan->status) === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $karyawan->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:16px;">
                    <i class="ph ph-floppy-disk"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
