<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly ImageCompressionService $imageService) {}

    public function index()
    {
        $leaves = LeaveRequest::where('user_id', Auth::id())
            ->latest()->paginate(10);
        return view('employee.leave.index', compact('leaves'));
    }

    public function create()
    {
        return view('employee.leave.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'       => 'required|in:izin,sakit,cuti',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.after_or_equal'   => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $compressed = $this->imageService->compressAndStore($file, "leave-attachments/" . Auth::id());
                $attachmentPath = $compressed['path'];
            } else {
                // PDF: store as-is
                $attachmentPath = $file->store("private/leave-attachments/" . Auth::id(), 'local');
            }
        }

        LeaveRequest::create([
            'user_id'         => Auth::id(),
            'type'            => $request->type,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'reason'          => $request->reason,
            'attachment_path' => $attachmentPath,
            'status'          => 'pending',
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan HR.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403);
        }
        return view('employee.leave.show', compact('leaveRequest'));
    }
}
