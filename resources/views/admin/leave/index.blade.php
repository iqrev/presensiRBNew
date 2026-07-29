@extends('layouts.app')
@section('title', 'Pengajuan Izin — Admin')
@section('content')
<div class="page-container" style="max-width:900px;">
    <div style="padding-top:16px;margin-bottom:20px;">
        <div class="page-title">📝 Pengajuan Izin</div>
    </div>

    <!-- Status Filter -->
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        @foreach(['pending'=>'<i class="ph ph-hourglass-high"></i> Pending','approved'=>'<i class="ph ph-check"></i> Disetujui','rejected'=>'<i class="ph ph-x"></i> Ditolak','all'=>'<i class="ph ph-list-dashes"></i> Semua'] as $val => $label)
        <a href="{{ route('admin.leave.index', ['status' => $val]) }}"
           class="btn {{ $status === $val ? 'btn-primary' : 'btn-secondary' }}" style="font-size:0.8rem;padding:7px 14px;">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($leaves->isEmpty())
        <div class="card"><div class="card-body" style="text-align:center;color:var(--gray-400);padding:32px;">Tidak ada data pengajuan.</div></div>
    @else
    <div class="card">
        @foreach($leaves as $leave)
        <div style="padding:16px 20px;border-bottom:1px solid var(--gray-100);" x-data="{ showReject: false }">
            <div class="flex items-center justify-between" style="margin-bottom:8px;">
                <div>
                    <div style="font-weight:700;font-size:0.9rem;">{{ $leave->user->name }} — {{ $leave->type_label }}</div>
                    <div style="font-size:0.78rem;color:var(--gray-500);">
                        <i class="ph ph-calendar"></i> {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }} ({{ $leave->duration_days }} hari)
                        · Diajukan: {{ $leave->created_at->diffForHumans() }}
                    </div>
                </div>
                <span class="badge badge-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'warning') }}">
                    {{ $leave->status_label }}
                </span>
            </div>
            <div style="font-size:0.85rem;color:var(--gray-600);margin-bottom:8px;">{{ $leave->reason }}</div>

            @if($leave->status === 'pending')
            <div style="display:flex;gap:8px;margin-top:10px;">
                <form method="POST" action="{{ route('admin.leave.approve', $leave) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-success" style="padding:7px 16px;font-size:0.82rem;"><i class="ph ph-check"></i> Setujui</button>
                </form>
                <button @click="showReject=!showReject" class="btn btn-danger" style="padding:7px 16px;font-size:0.82rem;"><i class="ph ph-x"></i> Tolak</button>
            </div>

            <!-- Reject form inline -->
            <div x-show="showReject" x-transition style="margin-top:12px;" x-cloak>
                <form method="POST" action="{{ route('admin.leave.reject', $leave) }}">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Alasan Penolakan <span>*</span></label>
                        <textarea name="rejection_note" class="form-control" rows="2" required maxlength="500" placeholder="Tuliskan alasan penolakan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Konfirmasi Tolak</button>
                    <button type="button" @click="showReject=false" class="btn btn-secondary" style="margin-left:8px;">Batal</button>
                </form>
            </div>
            @endif

            @if($leave->rejection_note)
            <div style="margin-top:8px;background:var(--danger-light);padding:8px 12px;border-radius:8px;font-size:0.78rem;color:var(--danger);">
                Alasan penolakan: {{ $leave->rejection_note }}
            </div>
            @endif
        </div>
        @endforeach
        <div style="padding:12px 20px;">{{ $leaves->links() }}</div>
    </div>
    @endif
</div>
@endsection
