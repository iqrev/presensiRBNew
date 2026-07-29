@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="page-container" style="max-width:1100px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div class="page-title" style="display:flex; align-items:center; gap:8px;">
                <i class="ph ph-squares-four" style="color:var(--primary);"></i> Dashboard Admin
            </div>
            <div class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
        <div>
            <div style="font-size:0.85rem; color:var(--gray-500); background:white; padding:8px 16px; border-radius:999px; border:1px solid var(--gray-200); box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                <i class="ph ph-users" style="color:var(--primary); margin-right:4px;"></i> 
                <span style="font-weight:600; color:var(--gray-800);">{{ $totalKaryawan }}</span> Total Karyawan
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:32px;">
        <div class="stat-card" style="border-radius:16px; border:1px solid var(--gray-100); background:white; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="width:52px; height:52px; border-radius:14px; background:#EFF6FF; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ph ph-check-circle"></i>
            </div>
            <div>
                <div style="font-size:0.85rem; color:var(--gray-500); font-weight:500; margin-bottom:4px;">Hadir Hari Ini</div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--gray-900);">{{ $todayStats['hadir'] }}</div>
            </div>
        </div>

        <div class="stat-card" style="border-radius:16px; border:1px solid var(--gray-100); background:white; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="width:52px; height:52px; border-radius:14px; background:#FFFBEB; color:#D97706; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ph ph-clock"></i>
            </div>
            <div>
                <div style="font-size:0.85rem; color:var(--gray-500); font-weight:500; margin-bottom:4px;">Terlambat</div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--gray-900);">{{ $todayStats['terlambat'] }}</div>
            </div>
        </div>

        <div class="stat-card" style="border-radius:16px; border:1px solid var(--gray-100); background:white; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="width:52px; height:52px; border-radius:14px; background:#FEF2F2; color:#DC2626; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ph ph-x-circle"></i>
            </div>
            <div>
                <div style="font-size:0.85rem; color:var(--gray-500); font-weight:500; margin-bottom:4px;">Belum Absen</div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--gray-900);">{{ $todayStats['belum'] }}</div>
            </div>
        </div>

        <div class="stat-card" style="border-radius:16px; border:1px solid var(--gray-100); background:white; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
            <div style="width:52px; height:52px; border-radius:14px; background:#F8FAFC; color:var(--gray-600); display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="ph ph-files"></i>
            </div>
            <div>
                <div style="font-size:0.85rem; color:var(--gray-500); font-weight:500; margin-bottom:4px;">Izin Pending</div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--gray-900);">{{ $pendingLeaves->count() }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
        <!-- Left Column -->
        <div>
            <!-- Today's Attendances -->
            <div class="card" style="margin-bottom:24px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03); border:1px solid var(--gray-100);">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding:20px; border-bottom:1px solid var(--gray-100);">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:600; color:var(--gray-800);">
                        <i class="ph ph-users" style="color:var(--primary); font-size:1.2rem;"></i> Presensi Masuk Hari Ini
                        <span style="font-size:0.75rem; background:var(--gray-100); padding:2px 8px; border-radius:999px; color:var(--gray-600);">{{ $todayAttendances->count() }} Karyawan</span>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" style="font-size:0.85rem; color:var(--primary); text-decoration:none; font-weight:600; display:flex; align-items:center; gap:4px;">
                        Laporan <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
                @if($todayAttendances->isEmpty())
                    <div class="card-body" style="text-align:center; color:var(--gray-400); padding:40px 24px;">
                        <i class="ph ph-calendar-blank" style="font-size:3rem; margin-bottom:12px; color:var(--gray-300);"></i>
                        <p>Belum ada presensi masuk hari ini.</p>
                    </div>
                @else
                <div class="table-wrap">
                    <table class="table" style="width:100%; text-align:left; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg-body); font-size:0.8rem; color:var(--gray-500); text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:12px 20px; font-weight:600;">Karyawan</th>
                                <th style="padding:12px 20px; font-weight:600;">Jam Masuk</th>
                                <th style="padding:12px 20px; font-weight:600;">Jarak</th>
                                <th style="padding:12px 20px; font-weight:600;">Akurasi Wajah</th>
                                <th style="padding:12px 20px; font-weight:600;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayAttendances as $att)
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px 20px;">
                                    <div style="font-weight:600; color:var(--gray-900);">{{ $att->user->name }}</div>
                                    <div style="font-size:0.75rem; color:var(--gray-500);">{{ $att->user->jabatan ?? 'Karyawan' }}</div>
                                </td>
                                <td style="padding:12px 20px; font-weight:600; color:var(--gray-800);">{{ $att->attendance_time->format('H:i') }}</td>
                                <td style="padding:12px 20px; color:var(--gray-600); font-size:0.9rem;">
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <i class="ph ph-map-pin" style="color:var(--gray-400);"></i> {{ round($att->distance_meter) }} m
                                    </div>
                                </td>
                                <td style="padding:12px 20px; color:var(--gray-600); font-size:0.9rem;">
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <i class="ph ph-scan" style="color:var(--primary-light);"></i> {{ round($att->face_match_score, 1) }}%
                                    </div>
                                </td>
                                <td style="padding:12px 20px;">
                                    <span style="font-size:0.75rem; font-weight:600; padding:4px 10px; border-radius:999px; 
                                        @if($att->status === 'hadir') background:#D1FAE5; color:#065F46;
                                        @elseif($att->status === 'terlambat') background:#FEF3C7; color:#92400E;
                                        @else background:#F1F5F9; color:#475569; @endif">
                                        {{ $att->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Failure Log -->
            @if($failureLogs->count() > 0)
            <div class="card" style="border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03); border:1px solid var(--gray-100);">
                <div class="card-header" style="padding:20px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; gap:8px; font-weight:600; color:var(--gray-800);">
                    <i class="ph ph-warning-circle" style="color:#DC2626; font-size:1.2rem;"></i> Log Presensi Gagal Terbaru
                </div>
                <div class="table-wrap">
                    <table class="table" style="width:100%; text-align:left; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg-body); font-size:0.8rem; color:var(--gray-500); text-transform:uppercase;">
                                <th style="padding:12px 20px; font-weight:600;">Karyawan</th>
                                <th style="padding:12px 20px; font-weight:600;">Waktu</th>
                                <th style="padding:12px 20px; font-weight:600;">Kendala</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failureLogs as $log)
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px 20px; font-weight:600; color:var(--gray-900);">{{ $log->user->name }}</td>
                                <td style="padding:12px 20px; font-size:0.85rem; color:var(--gray-600);">{{ $log->attempted_at->format('d M, H:i') }}</td>
                                <td style="padding:12px 20px;">
                                    <span style="font-size:0.75rem; font-weight:500; background:#FEE2E2; color:#991B1B; padding:4px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph ph-info"></i> {{ $log->failure_reason_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div>
            <!-- Pending Leave Requests -->
            <div class="card" style="margin-bottom:24px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03); border:1px solid var(--gray-100);">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; padding:20px; border-bottom:1px solid var(--gray-100);">
                    <div style="display:flex; align-items:center; gap:8px; font-weight:600; color:var(--gray-800);">
                        <i class="ph ph-envelope-open" style="color:var(--primary); font-size:1.2rem;"></i> Pengajuan Izin
                    </div>
                    @if($pendingLeaves->count() > 0)
                        <span style="font-size:0.75rem; background:#FEF2F2; color:#DC2626; padding:2px 8px; border-radius:999px; font-weight:600;">{{ $pendingLeaves->count() }} Baru</span>
                    @endif
                </div>
                
                @if($pendingLeaves->count() === 0)
                    <div style="padding:32px 20px; text-align:center; color:var(--gray-400);">
                        <i class="ph ph-check-circle" style="font-size:2.5rem; margin-bottom:8px; color:var(--gray-300);"></i>
                        <p style="font-size:0.9rem;">Tidak ada izin pending.</p>
                    </div>
                @else
                    @foreach($pendingLeaves->take(5) as $leave)
                    <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                            <div>
                                <div style="font-weight:600; font-size:0.9rem; color:var(--gray-900);">{{ $leave->user->name }}</div>
                                <div style="font-size:0.75rem; color:var(--gray-500); display:flex; align-items:center; gap:4px; margin-top:2px;">
                                    <i class="ph ph-calendar"></i> {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}
                                </div>
                            </div>
                            <span style="font-size:0.7rem; font-weight:600; background:var(--gray-100); color:var(--gray-600); padding:2px 6px; border-radius:4px;">{{ $leave->type_label }}</span>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                            <form method="POST" action="{{ route('admin.leave.approve', $leave) }}" style="width:100%;">
                                @csrf @method('PATCH')
                                <button class="btn btn-primary" style="width:100%; padding:8px; font-size:0.8rem; border-radius:8px; display:flex; justify-content:center; align-items:center; gap:4px;">
                                    <i class="ph ph-check"></i> Setujui
                                </button>
                            </form>
                            <a href="{{ route('admin.leave.index') }}" class="btn" style="width:100%; background:var(--gray-100); color:var(--gray-700); text-align:center; text-decoration:none; padding:8px; font-size:0.8rem; border-radius:8px; display:flex; justify-content:center; align-items:center; gap:4px;">
                                <i class="ph ph-eye"></i> Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                    <div style="padding:12px; text-align:center;">
                        <a href="{{ route('admin.leave.index') }}" style="font-size:0.85rem; color:var(--primary); text-decoration:none; font-weight:500;">Lihat Semua Pengajuan →</a>
                    </div>
                @endif
            </div>

            <!-- Manual Requests -->
            @if($manualRequests->count() > 0)
            <div class="card" style="border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03); border:1px solid var(--gray-100);">
                <div class="card-header" style="padding:20px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; gap:8px; font-weight:600; color:var(--gray-800);">
                    <i class="ph ph-hand-pointing" style="color:var(--warning); font-size:1.2rem;"></i> Presensi Manual
                </div>
                @foreach($manualRequests as $att)
                <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100);">
                    <div style="font-weight:600; font-size:0.9rem; color:var(--gray-900);">{{ $att->user->name }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                        <span style="font-size:0.8rem; color:var(--gray-500);">
                            {{ $att->type === 'check_in' ? 'Masuk' : 'Pulang' }} · {{ $att->attendance_time->format('d M H:i') }}
                        </span>
                    </div>
                    <div style="font-size:0.8rem; color:var(--gray-600); margin-top:8px; background:var(--gray-50); padding:8px; border-radius:6px; border-left:2px solid var(--gray-300);">
                        "{{ $att->rejection_reason }}"
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
