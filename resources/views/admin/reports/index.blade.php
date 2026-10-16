@extends('layouts.app')
@section('title', 'Laporan Presensi')
@section('content')
<style>
    .report-page { max-width:1180px; }
    .report-header { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; padding-top:16px; margin-bottom:18px; }
    .report-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .report-actions .btn { border-radius:12px; }
    .report-presets { display:flex; gap:8px; flex-wrap:wrap; margin:0 0 12px; }
    .report-presets a { font-size:.78rem; padding:6px 11px; border-radius:999px; background:#f1f5f9; color:#475569; text-decoration:none; }
    .report-filters { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); align-items:end; gap:12px; }
    .report-filters .form-group { min-width:0; margin:0; }
    .report-filters .employee-field, .report-filters .department-field, .report-filters .arrival-field { grid-column:span 2; }
    .report-filters .filter-buttons { grid-column:1/-1; display:flex; justify-content:flex-end; gap:8px; }
    .report-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin:16px 0; }
    .report-kpi { padding:14px 16px; border:1px solid var(--gray-100); border-radius:16px; background:linear-gradient(145deg,#fff,#f8fafc); }
    .report-kpi strong { display:block; font-size:1.4rem; color:var(--gray-900); }
    .report-kpi span { color:var(--gray-500); font-size:.78rem; }
    .report-card { overflow:hidden; margin-bottom:16px; }
    .report-card-title { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .report-table { min-width:850px; }
    .report-table .subtle { color:var(--gray-400); font-size:.75rem; }
    @media(max-width:920px) { .report-filters { grid-template-columns:repeat(2,minmax(0,1fr)); } .report-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:620px) { .report-filters { grid-template-columns:repeat(2,minmax(0,1fr)); } .report-filters .filter-buttons { justify-content:stretch; } .report-filters .filter-buttons .btn { flex:1; justify-content:center; } .report-header { align-items:flex-start; } }
</style>
<div class="page-container report-page">
    <div class="report-header">
        <div>
            <div class="page-title"><i class="ph ph-chart-bar" style="color:var(--primary);"></i> Laporan Presensi</div>
            <div class="page-subtitle">Pantau kehadiran harian, ketepatan waktu, dan hari kosong per periode.</div>
        </div>
        <div class="report-actions">
            <a href="{{ route('admin.reports.excel', $filters) }}" class="btn btn-success"><i class="ph ph-file-xls"></i> Ekspor Excel</a>
            <a href="{{ route('admin.reports.pdf', $filters) }}" class="btn btn-danger"><i class="ph ph-file-pdf"></i> Ringkasan PDF</a>
        </div>
    </div>

    <div class="report-presets">
        <a href="{{ route('admin.reports.index', array_merge($filters, ['start' => today()->toDateString(), 'end' => today()->toDateString()])) }}">Hari ini</a>
        <a href="{{ route('admin.reports.index', array_merge($filters, ['start' => today()->subDays(6)->toDateString(), 'end' => today()->toDateString()])) }}">7 hari terakhir</a>
        <a href="{{ route('admin.reports.index', array_merge($filters, ['start' => now()->startOfMonth()->toDateString(), 'end' => today()->toDateString()])) }}">Bulan ini</a>
    </div>

    <div class="card" style="padding:16px;margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filters">
            <div class="form-group date-field">
                <label class="form-label" for="report-start">Dari tanggal</label>
                <input id="report-start" type="date" name="start" class="form-control" value="{{ $filters['start'] }}" required>
            </div>
            <div class="form-group date-field">
                <label class="form-label" for="report-end">Sampai tanggal</label>
                <input id="report-end" type="date" name="end" class="form-control" value="{{ $filters['end'] }}" required>
            </div>
            <div class="form-group employee-field">
                <label class="form-label" for="report-employee">Karyawan</label>
                <select id="report-employee" name="user_id" class="form-control">
                    <option value="all">Semua karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected($filters['user_id'] == $employee->id)>{{ $employee->name }}{{ $employee->status === 'nonaktif' ? ' (Nonaktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group department-field">
                <label class="form-label" for="report-department">Departemen</label>
                <select id="report-department" name="department" class="form-control">
                    <option value="all">Semua departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected($filters['department'] === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group arrival-field">
                <label class="form-label" for="report-arrival">Status Kehadiran</label>
                <select id="report-arrival" name="arrival" class="form-control">
                    <option value="all" @selected(($filters['arrival'] ?? 'all') === 'all')>Semua hari & status</option>
                    <option value="on_time" @selected(($filters['arrival'] ?? '') === 'on_time')>Tepat waktu</option>
                    <option value="late" @selected(($filters['arrival'] ?? '') === 'late')>Terlambat</option>
                    <option value="absent" @selected(($filters['arrival'] ?? '') === 'absent')>Tidak hadir</option>
                    <option value="leave" @selected(($filters['arrival'] ?? '') === 'leave')>Izin / Sakit / Cuti</option>
                </select>
            </div>
            <div class="filter-buttons">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary"><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
                <button type="submit" class="btn btn-primary"><i class="ph ph-magnifying-glass"></i> Terapkan Filter</button>
            </div>
        </form>
        @if($errors->any())
            <div class="alert alert-danger" style="margin:12px 0 0;">{{ $errors->first() }}</div>
        @endif
    </div>

    <div class="report-kpis">
        <div class="report-kpi"><strong>{{ number_format($totalAttendance) }}</strong><span>Total hadir</span></div>
        <div class="report-kpi"><strong>{{ number_format($uniqueEmployees) }}</strong><span>Karyawan masuk</span></div>
        <div class="report-kpi"><strong>{{ number_format($onTimeAttendance) }}</strong><span>Tepat waktu (batas {{ substr($lateCutoff, 0, 5) }})</span></div>
        <div class="report-kpi"><strong>{{ number_format($lateAttendance) }}</strong><span>Terlambat</span></div>
        <div class="report-kpi"><strong style="color:var(--danger, #dc2626);">{{ number_format($absentAttendance) }}</strong><span>Hari tidak hadir</span></div>
    </div>

    <div class="card report-card">
        <div class="card-header report-card-title"><span><i class="ph ph-users-three"></i> Ringkasan per Karyawan</span><span style="font-size:.78rem;color:var(--gray-500);">Periode: {{ $filters['start'] }} sampai {{ $filters['end'] }}</span></div>
        @if(empty($summary))
            <div class="card-body" style="text-align:center;color:var(--gray-500);">Belum ada data karyawan untuk filter ini.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Karyawan</th><th>Departemen</th><th>Hari Hadir</th><th>Tepat Waktu</th><th>Terlambat</th><th>Tidak Hadir</th><th>Izin/Sakit</th></tr></thead>
                    <tbody>
                        @foreach($summary as $row)
                        <tr>
                            <td><div style="font-weight:600;">{{ $row['user']->name }}</div><div class="subtle">{{ $row['user']->nik ?? '-' }} · {{ $row['user']->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</div></td>
                            <td>{{ $row['user']->department ?? '-' }}</td>
                            <td><strong>{{ $row['total_hadir'] }}</strong></td>
                            <td><span class="badge badge-success">{{ $row['tepat'] }}</span></td>
                            <td><span class="badge badge-warning">{{ $row['terlambat'] }}</span></td>
                            <td><span class="badge badge-danger">{{ $row['tidak_hadir'] }}</span></td>
                            <td><span class="badge badge-info">{{ $row['izin_sakit'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card report-card">
        <div class="card-header report-card-title"><span><i class="ph ph-calendar-check"></i> Detail Presensi Harian (Termasuk Hari Kosong)</span><span style="font-size:.78rem;color:var(--gray-500);">{{ $attendances->total() }} catatan</span></div>
        @if($attendances->isEmpty())
            <div class="card-body" style="text-align:center;color:var(--gray-500);padding:36px;">Tidak ada data untuk kombinasi filter ini.</div>
        @else
            <div class="table-wrap">
                <table class="table report-table">
                    <thead><tr><th>Karyawan</th><th>Hari & Tanggal</th><th>Masuk</th><th>Pulang</th><th>Durasi</th><th>Jarak</th><th>Face</th><th>Status Kehadiran</th></tr></thead>
                    <tbody>
                        @foreach($attendances as $att)
                            <tr style="{{ $att['status_code'] === 'absent' ? 'background: #fffafa;' : '' }}">
                                <td>
                                    <div style="font-weight:600;">{{ $att['user']->name }}</div>
                                    <div class="subtle">{{ $att['user']->jabatan ?? '-' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $att['day_name'] }}</div>
                                    <div class="subtle">{{ \Carbon\Carbon::parse($att['date'])->translatedFormat('d M Y') }}</div>
                                </td>
                                <td style="font-weight:{{ $att['check_in_time'] !== '-' ? '700' : 'normal' }};">{{ $att['check_in_time'] }}</td>
                                <td>{{ $att['check_out_time'] }}</td>
                                <td>{{ $att['duration_label'] }}</td>
                                <td>{{ $att['distance_meter'] !== null ? $att['distance_meter'] . ' m' : '-' }}</td>
                                <td>{{ $att['face_match_score'] !== null ? $att['face_match_score'] . '%' : '-' }}</td>
                                <td><span class="badge badge-{{ $att['status_badge'] }}">{{ $att['status_label'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:14px 18px;">
                <span style="font-size:.82rem;color:var(--gray-500);">Menampilkan {{ $attendances->firstItem() }}-{{ $attendances->lastItem() }} dari {{ $attendances->total() }} catatan</span>
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
