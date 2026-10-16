<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AttendanceReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $employees = User::role('karyawan')->orderBy('name')->get();
        $departments = $employees->pluck('department')->filter()->unique()->sort()->values();
        $filters = $this->validatedFilters($request, $employees, $departments);
        $lateCutoff = $this->reportService->getLateCutoff();

        // 1. Dapatkan seluruh timeline harian untuk karyawan yang dipilih (termasuk hari kosong/tidak hadir)
        $timeline = $this->reportService->getDailyTimeline($filters, $employees);

        // 2. Buat ringkasan per karyawan
        $summary = $this->reportService->buildSummary($filters, $employees, $timeline);

        // 3. Hitung metrik KPI
        $presentRows = $timeline->filter(fn ($item) => in_array($item['status_code'], ['on_time', 'late']));
        $totalAttendance = $presentRows->count();
        $lateAttendance = $timeline->where('status_code', 'late')->count();
        $onTimeAttendance = $timeline->where('status_code', 'on_time')->count();
        $absentAttendance = $timeline->where('status_code', 'absent')->count();
        $uniqueEmployees = $presentRows->pluck('user.id')->unique()->count();

        // 4. Paginasi timeline untuk tampilan tabel
        $attendances = $this->reportService->paginateTimeline($timeline, 30);

        return view('admin.reports.index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $filters,
            'lateCutoff' => $lateCutoff,
            'summary' => $summary,
            'totalAttendance' => $totalAttendance,
            'lateAttendance' => $lateAttendance,
            'onTimeAttendance' => $onTimeAttendance,
            'absentAttendance' => $absentAttendance,
            'uniqueEmployees' => $uniqueEmployees,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $employees = User::role('karyawan')->get(['id', 'department']);
        $departments = $employees->pluck('department')->filter()->unique()->values();
        $filters = $this->validatedFilters($request, $employees, $departments);

        return Excel::download(
            new \App\Exports\AttendanceExport(
                $filters['start'], $filters['end'], $filters['user_id'], $filters['department'], $filters['arrival']
            ),
            "laporan-presensi-{$filters['start']}-{$filters['end']}.xlsx"
        );
    }

    public function exportPdf(Request $request)
    {
        $employees = User::role('karyawan')->orderBy('name')->get();
        $departments = $employees->pluck('department')->filter()->unique()->sort()->values();
        $filters = $this->validatedFilters($request, $employees, $departments);
        $lateCutoff = $this->reportService->getLateCutoff();

        $timeline = $this->reportService->getDailyTimeline($filters, $employees);
        $summary = $this->reportService->buildSummary($filters, $employees, $timeline);

        $namaKantor = SystemSetting::get('nama_kantor', 'Kantor');
        $filterLabel = $this->filterLabel($filters, $employees);

        $pdf = Pdf::loadView('reports.pdf', compact('summary', 'timeline', 'filters', 'namaKantor', 'filterLabel', 'lateCutoff'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("laporan-presensi-{$filters['start']}-{$filters['end']}.pdf");
    }

    private function validatedFilters(Request $request, $employees, $departments): array
    {
        $defaults = [
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->toDateString(),
            'user_id' => 'all',
            'department' => 'all',
            'arrival' => 'all',
        ];
        $input = array_merge($defaults, array_filter(
            $request->only(array_keys($defaults)),
            fn ($value) => $value !== null && $value !== ''
        ));
        $request->merge($input);

        return $request->validate([
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'user_id' => ['required', Rule::in(array_merge(['all'], $employees->pluck('id')->map(fn ($id) => (string) $id)->all()))],
            'department' => ['required', Rule::in(array_merge(['all'], $departments->map(fn ($value) => (string) $value)->all()))],
            'arrival' => ['required', Rule::in(['all', 'on_time', 'late', 'absent', 'leave'])],
        ]);
    }

    private function filterLabel(array $filters, $employees): string
    {
        $employeeName = $filters['user_id'] === 'all'
            ? 'Semua karyawan'
            : (string) optional($employees->firstWhere('id', (int) $filters['user_id']))->name;
        $department = $filters['department'] === 'all' ? 'Semua departemen' : $filters['department'];
        $arrival = match ($filters['arrival']) {
            'on_time' => 'Tepat waktu',
            'late' => 'Terlambat',
            'absent' => 'Tidak hadir',
            'leave' => 'Izin / Sakit / Cuti',
            default => 'Semua status',
        };
        return implode(' · ', [$employeeName, $department, $arrival]);
    }
}
