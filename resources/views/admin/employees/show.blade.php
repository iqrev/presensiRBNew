@extends('layouts.app')
@section('title', 'Detail Karyawan')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('admin.karyawan.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Daftar Karyawan</a>
        <div class="flex items-center justify-between" style="margin-top:8px;">
            <div class="page-title">{{ $karyawan->name }}</div>
            <span class="badge badge-{{ $karyawan->status === 'aktif' ? 'success' : 'gray' }}">{{ ucfirst($karyawan->status) }}</span>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:0.875rem;">
                <div><div class="text-xs text-muted">Email</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->email }}</div></div>
                <div><div class="text-xs text-muted">NIK</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->nik ?? '-' }}</div></div>
                <div><div class="text-xs text-muted">Jabatan</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->jabatan ?? '-' }}</div></div>
                <div><div class="text-xs text-muted">Departemen</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->department ?? '-' }}</div></div>
                <div><div class="text-xs text-muted">No. HP</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->phone ?? '-' }}</div></div>
                <div><div class="text-xs text-muted">Consent Biometrik</div><div style="font-weight:600;margin-top:2px;">{{ $karyawan->biometric_consent_at ? $karyawan->biometric_consent_at->format('d M Y') : '<i class="ph ph-x"></i> Belum' }}</div></div>
            </div>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-primary" style="flex:1;justify-content:center;"><i class="ph ph-pencil-simple"></i> Edit</a>
                <a href="{{ route('admin.face.index', $karyawan) }}" class="btn btn-secondary" style="flex:1;justify-content:center;"><i class="ph ph-camera"></i> Foto Wajah ({{ $karyawan->faceReferences->count() }})</a>
            </div>
        </div>
    </div>

    <!-- Attendance This Month -->
    <div class="card">
        <div class="card-header"><i class="ph ph-calendar"></i> Presensi Bulan Ini</div>
        @if($karyawan->attendances->isEmpty())
            <div class="card-body" style="text-align:center;color:var(--gray-400);padding:24px;">Belum ada data presensi bulan ini.</div>
        @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Tanggal</th><th>Tipe</th><th>Jam</th><th>Jarak</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($karyawan->attendances as $att)
                    <tr>
                        <td>{{ $att->attendance_time->format('d M') }}</td>
                        <td>{{ $att->type === 'check_in' ? '<i class="ph ph-arrow-up-right"></i> Masuk' : '<i class="ph ph-arrow-down-left"></i> Pulang' }}</td>
                        <td style="font-weight:700;">{{ $att->attendance_time->format('H:i') }}</td>
                        <td>{{ round($att->distance_meter) }} m</td>
                        <td><span class="badge badge-{{ $att->status_color }}">{{ $att->status_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
