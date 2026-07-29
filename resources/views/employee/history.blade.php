@extends('layouts.app')
@section('title', 'Riwayat Absensi')

@section('content')
<div class="page-container">
    <div class="page-header">
        <div class="page-title"><i class="ph ph-calendar"></i> Riwayat Absensi</div>
    </div>

    <!-- Month Selector -->
    <form method="GET" style="margin-bottom:16px;">
        <div style="display:flex;gap:8px;align-items:center;">
            <input type="month" name="month" value="{{ $month }}" class="form-control" style="flex:1;" onchange="this.form.submit()">
            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Tampilkan</button>
        </div>
    </form>

    @if($attendances->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px 20px;">
                <div style="font-size:3rem;margin-bottom:12px;">📭</div>
                <div style="color:var(--gray-500);">Belum ada data presensi untuk bulan ini.</div>
            </div>
        </div>
    @else
        @foreach($attendances as $date => $records)
        @php
            $checkIn  = $records->firstWhere('type', 'check_in');
            $checkOut = $records->firstWhere('type', 'check_out');
            $carbonDate = \Carbon\Carbon::parse($date);
        @endphp
        <div class="card" style="margin-bottom:10px;">
            <div class="card-body" style="padding:14px 16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:42px;height:42px;background:var(--primary-50);border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <div style="font-size:0.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;">{{ $carbonDate->translatedFormat('D') }}</div>
                            <div style="font-size:1rem;font-weight:800;color:var(--primary-dark);">{{ $carbonDate->format('d') }}</div>
                        </div>
                        <div>
                            <div style="font-size:0.875rem;font-weight:600;color:var(--gray-800);">{{ $carbonDate->translatedFormat('l, d F Y') }}</div>
                            <div style="font-size:0.78rem;color:var(--gray-500);margin-top:2px;">
                                @if($checkIn)
                                    <i class="ph ph-arrow-up-right"></i> {{ $checkIn->attendance_time->format('H:i') }}
                                    @if($checkOut) · <i class="ph ph-arrow-down-left"></i> {{ $checkOut->attendance_time->format('H:i') }} @endif
                                @else
                                    Tidak ada data
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($checkIn)
                        <span class="badge badge-{{ $checkIn->status === 'valid' ? 'success' : ($checkIn->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ $checkIn->status_label }}
                        </span>
                    @endif
                </div>

                @if($checkIn)
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span style="background:var(--gray-50);padding:4px 10px;border-radius:20px;font-size:0.72rem;color:var(--gray-600);">
                        <i class="ph ph-map-pin"></i> {{ round($checkIn->distance_meter) }} m dari kantor
                    </span>
                    @if($checkIn->face_match_score)
                    <span style="background:var(--gray-50);padding:4px 10px;border-radius:20px;font-size:0.72rem;color:var(--gray-600);">
                        😊 Kecocokan: {{ round($checkIn->face_match_score, 1) }}%
                    </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection
