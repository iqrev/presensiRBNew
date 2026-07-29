@extends('layouts.app')
@section('title', 'Edit Lokasi Kantor')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.settings.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Kembali</a>
        <div class="page-title" style="margin-top:8px;"><i class="ph ph-pencil-simple"></i> Edit Lokasi: {{ $lokasi->name }}</div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.lokasi.update', $lokasi) }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Lokasi <span>*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $lokasi->name) }}" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Latitude <span>*</span></label>
                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $lokasi->latitude) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude <span>*</span></label>
                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $lokasi->longitude) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Radius (meter) <span>*</span></label>
                    <input type="number" name="radius_meter" class="form-control" value="{{ old('radius_meter', $lokasi->radius_meter) }}" min="10" max="5000" required>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;cursor:pointer;font-size:0.875rem;color:var(--gray-700);">
                    <input type="checkbox" name="is_active" value="1" {{ $lokasi->is_active ? 'checked' : '' }} style="accent-color:var(--primary);width:16px;height:16px;">
                    <span>Jadikan lokasi aktif</span>
                </label>
                <button type="submit" class="btn btn-primary btn-full btn-lg">💾 Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
