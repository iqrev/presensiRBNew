@extends('layouts.app')
@section('title', 'Pengajuan Izin')
@section('content')
<div class="page-container">
    <div class="flex items-center justify-between" style="margin-bottom:16px;padding-top:16px;">
        <div>
            <div class="page-title">📝 Pengajuan Izin</div>
        </div>
        <a href="{{ route('leave.create') }}" class="btn btn-primary">+ Buat</a>
    </div>

    @if($leaves->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px 20px;">
                <div style="font-size:3rem;margin-bottom:12px;">📭</div>
                <div style="color:var(--gray-500);margin-bottom:16px;">Belum ada pengajuan izin.</div>
                <a href="{{ route('leave.create') }}" class="btn btn-primary">📝 Buat Pengajuan</a>
            </div>
        </div>
    @else
        @foreach($leaves as $leave)
        <div class="card" style="margin-bottom:10px;">
            <div class="card-body" style="padding:14px 16px;">
                <div class="flex items-center justify-between" style="margin-bottom:8px;">
                    <div style="font-weight:700;color:var(--gray-800);">{{ $leave->type_label }}</div>
                    <span class="badge badge-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                        {{ $leave->status_label }}
                    </span>
                </div>
                <div style="font-size:0.85rem;color:var(--gray-600);">
                    <i class="ph ph-calendar"></i> {{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }}
                    ({{ $leave->duration_days }} hari)
                </div>
                <div style="font-size:0.8rem;color:var(--gray-500);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $leave->reason }}
                </div>
                @if($leave->status === 'rejected' && $leave->rejection_note)
                <div style="margin-top:8px;background:var(--danger-light);padding:8px 12px;border-radius:8px;font-size:0.78rem;color:var(--danger);">
                    <i class="ph ph-x"></i> Alasan penolakan: {{ $leave->rejection_note }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
        <div style="margin-top:16px;">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
