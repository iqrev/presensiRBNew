@extends('layouts.app')
@section('title', 'Detail Pengajuan Izin')
@section('content')
<div class="page-container">
    <div style="padding-top:16px;margin-bottom:20px;">
        <a href="{{ route('leave.index') }}" style="color:var(--primary);text-decoration:none;font-size:.875rem;">← Kembali</a>
        <div class="page-title" style="margin-top:8px;"><i class="ph ph-list-dashes"></i> Detail Pengajuan</div>
    </div>
    <div class="card">
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div style="font-size:1.1rem;font-weight:800;">{{ $leaveRequest->type_label }}</div>
                <span class="badge badge-{{ $leaveRequest->status === 'approved' ? 'success' : ($leaveRequest->status === 'rejected' ? 'danger' : 'warning') }}" style="font-size:0.85rem;padding:6px 14px;">
                    {{ $leaveRequest->status_label }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div><div class="text-xs text-muted">Tanggal Mulai</div><div style="font-weight:600;margin-top:2px;">{{ $leaveRequest->start_date->translatedFormat('d F Y') }}</div></div>
                <div><div class="text-xs text-muted">Tanggal Selesai</div><div style="font-weight:600;margin-top:2px;">{{ $leaveRequest->end_date->translatedFormat('d F Y') }}</div></div>
                <div><div class="text-xs text-muted">Durasi</div><div style="font-weight:600;margin-top:2px;">{{ $leaveRequest->duration_days }} hari</div></div>
                <div><div class="text-xs text-muted">Diajukan</div><div style="font-weight:600;margin-top:2px;">{{ $leaveRequest->created_at->diffForHumans() }}</div></div>
            </div>
            <div><div class="text-xs text-muted">Alasan</div><div style="margin-top:4px;font-size:0.875rem;">{{ $leaveRequest->reason }}</div></div>

            @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_note)
            <div style="margin-top:16px;" class="alert alert-danger">
                <div><strong>Alasan Penolakan:</strong> {{ $leaveRequest->rejection_note }}</div>
            </div>
            @endif

            @if($leaveRequest->status === 'approved')
            <div style="margin-top:16px;" class="alert alert-success">
                <i class="ph ph-check"></i> Pengajuan disetujui oleh {{ $leaveRequest->approvedBy?->name ?? 'HR' }} pada {{ $leaveRequest->approved_at?->translatedFormat('d F Y H:i') }}.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
