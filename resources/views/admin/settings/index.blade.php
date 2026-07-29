@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <div class="page-title">⚙️ Pengaturan Sistem</div>
        <div class="page-subtitle">Konfigurasi jam kerja dan lokasi kantor</div>
    </div>

    <!-- Work Hours -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><i class="ph ph-clock"></i> Jam Kerja</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Jam Masuk <span>*</span></label>
                        <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $settings['jam_masuk']?->value ?? '08:00') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Pulang <span>*</span></label>
                        <input type="time" name="jam_pulang" class="form-control" value="{{ old('jam_pulang', $settings['jam_pulang']?->value ?? '17:00') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Toleransi Keterlambatan (menit) <span>*</span></label>
                    <input type="number" name="toleransi_terlambat_menit" class="form-control" value="{{ old('toleransi_terlambat_menit', $settings['toleransi_terlambat_menit']?->value ?? '15') }}" min="0" max="120" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Kantor <span>*</span></label>
                    <input type="text" name="nama_kantor" class="form-control" value="{{ old('nama_kantor', $settings['nama_kantor']?->value ?? '') }}" required>
                </div>
                <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan</button>
            </form>
        </div>
    </div>

    <!-- Office Locations -->
    <div class="card">
        <div class="card-header">
            <i class="ph ph-map-pin"></i> Lokasi Kantor
            <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary" style="padding:6px 14px;font-size:0.78rem;">+ Tambah</a>
        </div>
        @php $locations = \App\Models\OfficeLocation::latest()->get(); @endphp
        @foreach($locations as $loc)
        <div style="padding:12px 20px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:600;font-size:0.875rem;">{{ $loc->name }}</div>
                <div style="font-size:0.75rem;color:var(--gray-500);">
                    {{ $loc->latitude }}, {{ $loc->longitude }} · Radius: {{ $loc->radius_meter }}m
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                @if($loc->is_active)<span class="badge badge-success">Aktif</span>@endif
                <a href="{{ route('admin.lokasi.edit', $loc) }}" class="btn btn-secondary" style="padding:5px 10px;font-size:0.75rem;">Edit</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
