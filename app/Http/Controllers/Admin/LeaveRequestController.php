<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $leaves = LeaveRequest::with('user', 'approvedBy')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()->paginate(20);

        return view('admin.leave.index', compact('leaves', 'status'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['leave' => 'Pengajuan ini sudah diproses.']);
        }

        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Pengajuan {$leaveRequest->user->name} disetujui.");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate(['rejection_note' => 'required|string|max:500']);

        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['leave' => 'Pengajuan ini sudah diproses.']);
        }

        $leaveRequest->update([
            'status'         => 'rejected',
            'approved_by'    => Auth::id(),
            'approved_at'    => now(),
            'rejection_note' => $request->rejection_note,
        ]);

        return back()->with('success', "Pengajuan {$leaveRequest->user->name} ditolak.");
    }
}
