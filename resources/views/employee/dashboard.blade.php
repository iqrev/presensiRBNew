@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-container">
    <!-- Greeting Header -->
    <div style="padding:20px 0 8px;">
        <div style="font-size:1rem;color:var(--gray-500);">
            {{ now()->hour < 12 ? '🌅 Selamat pagi' : (now()->hour < 17 ? '☀️ Selamat siang' : '🌙 Selamat sore') }},
        </div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--gray-900);">{{ Auth::user()->name }}</div>
        <div style="font-size:0.85rem;color:var(--gray-500);margin-top:2px;">
            {{ Auth::user()->jabatan ?? 'Karyawan' }} · {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <!-- Today's Status Card -->
    <div style="margin:16px 0;">
        <div class="card" style="background:linear-gradient(135deg,#4F46E5,#6366F1);color:white;border:none;">
            <div class="card-body" style="padding:20px;">
                <div style="font-size:0.8rem;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">
                    Status Presensi Hari Ini
                </div>

                @if($todayStatus === 'sudah_pulang')
                    <div style="font-size:1.2rem;font-weight:800;margin-bottom:4px;"><i class="ph ph-check"></i> Selesai!</div>
                    <div style="opacity:.85;font-size:0.875rem;">Masuk: {{ $checkIn->attendance_time->format('H:i') }} · Pulang: {{ $checkOut->attendance_time->format('H:i') }}</div>
                    @if($isLate)<span style="background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;margin-top:8px;display:inline-block;"><i class="ph ph-warning"></i> Terlambat</span>@endif

                @elseif($todayStatus === 'sudah_masuk')
                    <div style="font-size:1.2rem;font-weight:800;margin-bottom:4px;">🟢 Sudah Masuk</div>
                    <div style="opacity:.85;font-size:0.875rem;">Check-in: {{ $checkIn->attendance_time->format('H:i') }} · Jangan lupa check-out ya!</div>
                    @if($isLate)<span style="background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;margin-top:8px;display:inline-block;"><i class="ph ph-warning"></i> Terlambat</span>@endif

                @else
                    <div style="font-size:1.2rem;font-weight:800;margin-bottom:4px;"><i class="ph ph-hourglass-high"></i> Belum Absen</div>
                    <div style="opacity:.85;font-size:0.875rem;">Jam masuk: {{ $jamMasuk }} · Ayo segera absen!</div>
                @endif

                <div style="margin-top:16px;">
                    <a href="{{ route('attendance.index') }}" style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);color:white;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:700;font-size:0.875rem;display:inline-flex;align-items:center;gap:6px;transition:all .2s;" onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                        <i class="ph ph-camera"></i> {{ $todayStatus === 'sudah_masuk' ? 'Check Out Sekarang' : ($todayStatus === 'belum_absen' ? 'Check In Sekarang' : 'Lihat Detail') }} →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-calendar"></i></div>
            <div class="stat-value">{{ $thisMonthStats['hadir'] }}</div>
            <div class="stat-label">Hari Hadir Bulan Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="ph ph-list-dashes"></i></div>
            <div class="stat-value">{{ $recentLeaves->count() }}</div>
            <div class="stat-label">Pengajuan Izin</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <a href="{{ route('attendance.history') }}" class="card" style="text-decoration:none;padding:16px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem;margin-bottom:6px;"><i class="ph ph-calendar"></i></div>
            <div style="font-size:0.85rem;font-weight:600;color:var(--gray-700);">Riwayat Absensi</div>
        </a>
        <a href="{{ route('leave.create') }}" class="card" style="text-decoration:none;padding:16px;text-align:center;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <div style="font-size:2rem;margin-bottom:6px;">📝</div>
            <div style="font-size:0.85rem;font-weight:600;color:var(--gray-700);">Ajukan Izin</div>
        </a>
    </div>

    <!-- Recent Leave Requests -->
    @if($recentLeaves->count() > 0)
    <div class="card">
        <div class="card-header">
            <i class="ph ph-list-dashes"></i> Pengajuan Izin Terbaru
            <a href="{{ route('leave.index') }}" style="font-size:0.78rem;color:var(--primary);font-weight:600;text-decoration:none;">Lihat Semua →</a>
        </div>
        @foreach($recentLeaves as $leave)
        <div style="padding:14px 20px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:600;font-size:0.875rem;color:var(--gray-800);">{{ $leave->type_label }}</div>
                <div style="font-size:0.78rem;color:var(--gray-500);">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}</div>
            </div>
            <span class="badge badge-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                {{ $leave->status_label }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
