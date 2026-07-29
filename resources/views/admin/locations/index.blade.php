@extends('layouts.app')
@section('title', 'Manajemen Lokasi Kantor')

@section('content')
<div class="page-container" style="max-width:1100px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div class="page-title" style="display:flex; align-items:center; gap:8px;">
                <i class="ph ph-map-pin" style="color:var(--primary);"></i> Lokasi Kantor
            </div>
            <div class="page-subtitle">Kelola titik koordinat absensi karyawan</div>
        </div>
        <div>
            <a href="{{ route('admin.lokasi.create') }}" class="btn btn-primary" style="padding:10px 20px; font-weight:600; font-size:0.9rem;">
                <i class="ph ph-plus"></i> Tambah Lokasi
            </a>
        </div>
    </div>

    <div class="card" style="border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03); border:1px solid var(--gray-100);">
        @if($locations->isEmpty())
            <div class="card-body" style="text-align:center; padding:60px 24px;">
                <i class="ph ph-map-pin-line" style="font-size:4rem; color:var(--gray-300); margin-bottom:16px;"></i>
                <div style="font-weight:600; color:var(--gray-700); font-size:1.1rem; margin-bottom:8px;">Belum Ada Lokasi</div>
                <p style="color:var(--gray-500); font-size:0.95rem;">Tambahkan setidaknya satu lokasi kantor untuk mengaktifkan geofencing.</p>
            </div>
        @else
        <div class="table-wrap" style="border:none; border-radius:16px;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg-body); font-size:0.8rem; color:var(--gray-500); text-transform:uppercase;">
                        <th style="padding:16px 24px; font-weight:600;">Nama Lokasi</th>
                        <th style="padding:16px 24px; font-weight:600;">Koordinat (Lat, Lng)</th>
                        <th style="padding:16px 24px; font-weight:600;">Radius</th>
                        <th style="padding:16px 24px; font-weight:600;">Status</th>
                        <th style="padding:16px 24px; font-weight:600; text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $lokasi)
                    <tr style="border-bottom:1px solid var(--gray-100);">
                        <td style="padding:16px 24px;">
                            <div style="font-weight:600; color:var(--gray-900); font-size:0.95rem;">{{ $lokasi->name }}</div>
                        </td>
                        <td style="padding:16px 24px; color:var(--gray-600); font-size:0.9rem; font-family:monospace;">
                            {{ $lokasi->latitude }}, {{ $lokasi->longitude }}
                        </td>
                        <td style="padding:16px 24px; color:var(--gray-600); font-size:0.9rem;">
                            {{ $lokasi->radius_meter }} Meter
                        </td>
                        <td style="padding:16px 24px;">
                            @if($lokasi->is_active)
                                <span class="badge badge-success" style="font-size:0.75rem; padding:6px 12px; background:#D1FAE5; color:#065F46;">Aktif</span>
                            @else
                                <span class="badge badge-gray" style="font-size:0.75rem; padding:6px 12px; background:#F1F5F9; color:#475569;">Tidak Aktif</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px; text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="{{ route('admin.lokasi.edit', $lokasi) }}" class="btn btn-secondary" style="padding:8px 12px; font-size:0.8rem; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                                    <i class="ph ph-pencil-simple"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.lokasi.destroy', $lokasi) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger" style="padding:8px 12px; font-size:0.8rem; border-radius:8px; background:#FEE2E2; color:#991B1B; border:none; display:inline-flex; align-items:center; gap:6px;">
                                        <i class="ph ph-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
