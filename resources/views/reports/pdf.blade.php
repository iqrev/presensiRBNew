<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Presensi — {{ $namaKantor }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size:11px; color:#1F2937; background:white; }
    .header { text-align:center; padding:20px 0 16px; border-bottom:2px solid #4F46E5; margin-bottom:20px; }
    .header h1 { font-size:18px; color:#4F46E5; font-weight:700; }
    .header p { color:#6B7280; font-size:10px; margin-top:4px; }
    .period { background:#EEF2FF; border-radius:6px; padding:8px 16px; margin-bottom:16px; font-size:10px; color:#4F46E5; font-weight:600; }
    table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    thead th { background:#4F46E5; color:white; padding:8px 10px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:.04em; }
    tbody td { padding:7px 10px; border-bottom:1px solid #E5E7EB; }
    tbody tr:nth-child(even) td { background:#F9FAFB; }
    .badge { padding:2px 8px; border-radius:20px; font-size:9px; font-weight:700; }
    .badge-success { background:#D1FAE5; color:#065F46; }
    .badge-warning { background:#FEF3C7; color:#92400E; }
    .footer { text-align:center; margin-top:24px; font-size:9px; color:#9CA3AF; border-top:1px solid #E5E7EB; padding-top:12px; }
</style>
</head>
<body>
<div class="header">
    <h1><i class="ph ph-list-dashes"></i> Laporan Presensi Karyawan</h1>
    <p>{{ $namaKantor }}</p>
</div>
<div class="period">
    Periode: {{ \Carbon\Carbon::parse($filters['start'])->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($filters['end'])->translatedFormat('d F Y') }}
    · Filter: {{ $filterLabel }} · Batas terlambat: {{ substr($lateCutoff, 0, 5) }}
    · Dibuat: {{ now()->translatedFormat('d F Y H:i') }}
</div>

@php
    $totalHadir = collect($summary)->sum('total');
    $totalTepat = collect($summary)->sum('tepat');
    $totalTerlambat = collect($summary)->sum('terlambat');
@endphp
<table style="margin-bottom:14px;">
    <tbody>
        <tr>
            <td style="background:#EEF2FF;font-weight:700;">Total presensi masuk: {{ $totalHadir }}</td>
            <td style="background:#D1FAE5;font-weight:700;">Tepat waktu: {{ $totalTepat }}</td>
            <td style="background:#FEF3C7;font-weight:700;">Terlambat: {{ $totalTerlambat }}</td>
            <td style="background:#F3F4F6;font-weight:700;">Karyawan dalam filter: {{ count($summary) }}</td>
        </tr>
    </tbody>
</table>

@if(!empty($summary))
<table>
    <thead>
        <tr>
            <th>Nama Karyawan</th>
            <th>Departemen</th>
            <th>Total Hadir</th>
            <th>Tepat Waktu</th>
            <th>Terlambat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary as $row)
        <tr>
            <td style="font-weight:700;">{{ $row['user']->name }}</td>
            <td style="color:#6B7280;">{{ $row['user']->department ?? '-' }}</td>
            <td style="font-weight:700;text-align:center;">{{ $row['total'] }}</td>
            <td style="text-align:center;"><span class="badge badge-success">{{ $row['tepat'] }}</span></td>
            <td style="text-align:center;"><span class="badge badge-warning">{{ $row['terlambat'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    Laporan ini dibuat otomatis oleh Sistem AbsensiRB · {{ $namaKantor }} · {{ now()->format('Y') }}
</div>
</body>
</html>
