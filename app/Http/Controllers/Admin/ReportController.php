<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::role('karyawan')->orderBy('name')->get();
        $departments = $employees->pluck('department')->filter()->unique()->sort()->values();
        $filters = $this->validatedFilters($request, $employees, $departments);
        $lateCutoff = $this->lateCutoff();

        $baseQuery = $this->attendanceQuery($filters);
        $totalAttendance = (clone $baseQuery)->count();
        $lateAttendance = (clone $baseQuery)->whereTime('attendance_time', '>', $lateCutoff)->count();
        $uniqueEmployees = (clone $baseQuery)->distinct()->count('user_id');
        $summary = $this->buildSummary($filters, $employees, $lateCutoff);

        $detailQuery = $this->applyArrivalFilter(clone $baseQuery, $filters, $lateCutoff)
            ->with('user')
            ->orderByDesc('attendance_time');
        $attendances = $detailQuery->paginate(30)->withQueryString();

        $pageRecords = $attendances->getCollection();
        $checkoutMap = collect();
        if ($pageRecords->isNotEmpty()) {
            $checkoutMap = Attendance::query()
                ->valid()
                ->checkOut()
                ->whereBetween('attendance_time', [$filters['start'] . ' 00:00:00', $filters['end'] . ' 23:59:59'])
                ->whereIn('user_id', $pageRecords->pluck('user_id')->unique())
                ->get()
                ->keyBy(fn ($row) => $row->user_id . '|' . $row->attendance_time->toDateString());
        }

        return view('admin.reports.index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $filters,
            'lateCutoff' => $lateCutoff,
            'summary' => $summary,
            'totalAttendance' => $totalAttendance,
            'lateAttendance' => $lateAttendance,
            'onTimeAttendance' => $totalAttendance - $lateAttendance,
            'uniqueEmployees' => $uniqueEmployees,
            'checkoutMap' => $checkoutMap,
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
        $lateCutoff = $this->lateCutoff();
        $summary = $this->buildSummary($filters, $employees, $lateCutoff);
        $namaKantor = SystemSetting::get('nama_kantor', 'Kantor');
        $filterLabel = $this->filterLabel($filters, $employees);

        $pdf = Pdf::loadView('reports.pdf', compact('summary', 'filters', 'namaKantor', 'filterLabel', 'lateCutoff'))
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
            'arrival' => ['required', Rule::in(['all', 'on_time', 'late'])],
        ]);
    }

    private function attendanceQuery(array $filters)
    {
        return Attendance::query()
            ->whereBetween('attendance_time', [$filters['start'] . ' 00:00:00', $filters['end'] . ' 23:59:59'])
            ->checkIn()
            ->valid()
            ->when($filters['user_id'] !== 'all', fn ($query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['department'] !== 'all', fn ($query) => $query->whereHas(
                'user', fn ($userQuery) => $userQuery->where('department', $filters['department'])
            ));
    }

    private function applyArrivalFilter($query, array $filters, string $lateCutoff)
    {
        return match ($filters['arrival']) {
            'on_time' => $query->whereTime('attendance_time', '<=', $lateCutoff),
            'late' => $query->whereTime('attendance_time', '>', $lateCutoff),
            default => $query,
        };
    }

    private function buildSummary(array $filters, $employees, string $lateCutoff): array
    {
        $employees = $employees
            ->when($filters['user_id'] !== 'all', fn ($rows) => $rows->where('id', (int) $filters['user_id']))
            ->when($filters['department'] !== 'all', fn ($rows) => $rows->where('department', $filters['department']))
            ->values();

        $recordsByUser = $this->applyArrivalFilter(
            $this->attendanceQuery($filters), $filters, $lateCutoff
        )
            ->selectRaw('user_id, COUNT(*) as total, SUM(CASE WHEN TIME(attendance_time) <= ? THEN 1 ELSE 0 END) as tepat', [$lateCutoff])
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return $employees->map(function ($user) use ($recordsByUser) {
            $records = $recordsByUser->get($user->id);
            $total = (int) ($records->total ?? 0);
            $onTime = (int) ($records->tepat ?? 0);
            return [
                'user' => $user,
                'total' => $total,
                'tepat' => $onTime,
                'terlambat' => $total - $onTime,
            ];
        })->all();
    }

    private function lateCutoff(): string
    {
        $startTime = SystemSetting::get('jam_masuk', '08:00');
        $tolerance = (int) SystemSetting::get('toleransi_terlambat_menit', 15);
        return Carbon::parse($startTime)->addMinutes($tolerance)->format('H:i:s');
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
            default => 'Semua status waktu',
        };
        return implode(' · ', [$employeeName, $department, $arrival]);
    }
}
