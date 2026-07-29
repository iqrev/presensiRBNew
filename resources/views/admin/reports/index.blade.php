@extends('layouts.app')
@section('title', 'Laporan Presensi')
@section('content')
<div class="page-container" style="max-width:900px;">
    <div style="padding-top:16px;margin-bottom:20px;">
        <div class="page-title">📑 Laporan Presensi</div>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div style="flex:1;min-width:140px;">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start" class="form-control" value="{{ $startDate }}">
                </div>
                <div style="flex:1;min-width:140px;">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end" class="form-control" value="{{ $endDate }}">
                </div>
                <div style="flex:1;min-width:160px;">
                    <label class="form-label">Karyawan</label>
                    <select name="user_id" class="form-control">
                        <option value="all">Semua Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $userId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
                    <a href="{{ route('admin.reports.excel', ['start' => $startDate, 'end' => $endDate, 'user_id' => $userId]) }}" class="btn btn-success">📊 Excel</a>
                    <a href="{{ route('admin.reports.pdf', ['start' => $startDate, 'end' => $endDate, 'user_id' => $userId]) }}" class="btn btn-danger">📄 PDF</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    @if(!empty($summary))
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">📊 Ringkasan per Karyawan</div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Karyawan</th><th>Total Hadir</th><th>Tepat Waktu</th><th>Terlambat</th></tr></thead>
                <tbody>
                    @foreach($summary as $row)
                    <tr>
                        <td style="font-weight:600;">{{ $row['user']->name }}</td>
                        <td>{{ $row['total'] }} hari</td>
                        <td><span class="badge badge-success">{{ $row['tepat'] }}</span></td>
                        <td><span class="badge badge-warning">{{ $row['terlambat'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Detail Table -->
    <div class="card">
        <div class="card-header"><i class="ph ph-list-dashes"></i> Detail Presensi</div>
        @if($attendances->isEmpty())
            <div class="card-body" style="text-align:center;color:var(--gray-400);padding:32px;">Tidak ada data untuk filter ini.</div>
        @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Nama</th><th>Tanggal</th><th>Masuk</th><th>Jarak</th><th>Face %</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($attendances as $att)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $att->user->name }}</div>
                            <div style="font-size:0.72rem;color:var(--gray-400);">{{ $att->user->jabatan ?? '-' }}</div>
                        </td>
                        <td>{{ $att->attendance_time->format('d M Y') }}</td>
                        <td style="font-weight:700;">{{ $att->attendance_time->format('H:i') }}</td>
                        <td>{{ round($att->distance_meter) }} m</td>
                        <td>{{ round($att->face_match_score, 1) }}%</td>
                        <td><span class="badge badge-{{ $att->status_color }}">{{ $att->status_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;">{{ $attendances->links() }}</div>
        @endif
    </div>
</div>
@endsection
