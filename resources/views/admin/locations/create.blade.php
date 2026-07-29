@extends('layouts.app')
@section('title', 'Tambah Lokasi Kantor')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.settings.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Kembali ke Pengaturan</a>
        <div class="page-title" style="margin-top:8px;"><i class="ph ph-map-pin"></i> Tambah Lokasi Kantor</div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info" style="margin-bottom:20px;">
                <i class="ph ph-lightbulb"></i> Klik kanan pada Google Maps untuk mendapatkan koordinat (latitude, longitude) lokasi kantor Anda.
            </div>
            <form method="POST" action="{{ route('admin.lokasi.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Lokasi <span>*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Kantor Pusat" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Latitude <span>*</span></label>
                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="-6.1234567" required>
                        @error('latitude')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude <span>*</span></label>
                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="106.1234567" required>
                        @error('longitude')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Radius Toleransi (meter) <span>*</span></label>
                    <input type="number" name="radius_meter" class="form-control" value="{{ old('radius_meter', 100) }}" min="10" max="5000" required>
                    <div style="font-size:0.75rem;color:var(--gray-400);margin-top:4px;">Disarankan 100–150 meter untuk gedung perkantoran.</div>
                    @error('radius_meter')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-full btn-lg">💾 Simpan Lokasi</button>
            </form>
        </div>
    </div>
</div>
@endsection
