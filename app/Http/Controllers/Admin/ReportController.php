<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::role('karyawan')->active()->orderBy('name')->get();
        $startDate = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end', now()->format('Y-m-d'));
        $userId    = $request->get('user_id', 'all');

        $query = Attendance::with('user')
            ->whereBetween('attendance_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->checkIn()
            ->valid()
            ->orderBy('attendance_time', 'desc');

        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        $attendances = $query->paginate(30)->withQueryString();

        // Summary stats per user
        $summary = $this->buildSummary($userId, $startDate, $endDate, $employees);

        return view('admin.reports.index', compact(
            'attendances', 'employees', 'startDate', 'endDate', 'userId', 'summary'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end', now()->format('Y-m-d'));
        $userId    = $request->get('user_id', 'all');

        $filename = "laporan-presensi-{$startDate}-{$endDate}.xlsx";

        return Excel::download(
            new \App\Exports\AttendanceExport($startDate, $endDate, $userId),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->get('end', now()->format('Y-m-d'));
        $userId    = $request->get('user_id', 'all');

        $employees = User::role('karyawan')->active()->get();
        $summary   = $this->buildSummary($userId, $startDate, $endDate, $employees);
        $namaKantor = SystemSetting::get('nama_kantor', 'Kantor');

        $pdf = Pdf::loadView('reports.pdf', compact('summary', 'startDate', 'endDate', 'namaKantor'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("laporan-presensi-{$startDate}-{$endDate}.pdf");
    }

    private function buildSummary(string $userId, string $startDate, string $endDate, $employees): array
    {
        $jamMasuk  = SystemSetting::get('jam_masuk', '08:00');
        $toleransi = (int) SystemSetting::get('toleransi_terlambat_menit', 15);
        $batasLambat = Carbon::parse($startDate . ' ' . $jamMasuk)->addMinutes($toleransi);

        $query = Attendance::with('user')
            ->whereBetween('attendance_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->checkIn()->valid();

        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        return $query->get()->groupBy('user_id')->map(function ($records, $uid) use ($batasLambat) {
            $user = $records->first()->user;
            return [
                'user'      => $user,
                'total'     => $records->count(),
                'tepat'     => $records->filter(fn($r) => $r->attendance_time->lte($batasLambat))->count(),
                'terlambat' => $records->filter(fn($r) => $r->attendance_time->gt($batasLambat))->count(),
            ];
        })->values()->toArray();
    }
}
