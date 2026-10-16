<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    public function getLateCutoff(): string
    {
        $startTime = SystemSetting::get('jam_masuk', '08:00');
        $tolerance = (int) SystemSetting::get('toleransi_terlambat_menit', 15);
        return Carbon::parse($startTime)->addMinutes($tolerance)->format('H:i:s');
    }

    /**
     * Generate daily timeline collection covering every single day in the filter range.
     */
    public function getDailyTimeline(array $filters, Collection $employees): Collection
    {
        $lateCutoff = $this->getLateCutoff();
        $startDate = Carbon::parse($filters['start']);
        $endDate = Carbon::parse($filters['end']);

        // Filter target employees
        $targetEmployees = $employees
            ->when($filters['user_id'] !== 'all', fn ($rows) => $rows->where('id', (int) $filters['user_id']))
            ->when($filters['department'] !== 'all', fn ($rows) => $rows->where('department', $filters['department']))
            ->values();

        if ($targetEmployees->isEmpty()) {
            return collect();
        }

        $employeeIds = $targetEmployees->pluck('id')->all();

        // 1. Fetch valid check-ins
        $checkIns = Attendance::query()
            ->valid()
            ->checkIn()
            ->whereBetween('attendance_time', [$filters['start'] . ' 00:00:00', $filters['end'] . ' 23:59:59'])
            ->whereIn('user_id', $employeeIds)
            ->get()
            ->keyBy(fn ($row) => $row->user_id . '|' . $row->attendance_time->toDateString());

        // 2. Fetch valid check-outs
        $checkOuts = Attendance::query()
            ->valid()
            ->checkOut()
            ->whereBetween('attendance_time', [$filters['start'] . ' 00:00:00', $filters['end'] . ' 23:59:59'])
            ->whereIn('user_id', $employeeIds)
            ->get()
            ->keyBy(fn ($row) => $row->user_id . '|' . $row->attendance_time->toDateString());

        // 3. Fetch approved leaves
        $leaves = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereIn('user_id', $employeeIds)
            ->where(function ($query) use ($filters) {
                $query->whereBetween('start_date', [$filters['start'], $filters['end']])
                    ->orWhereBetween('end_date', [$filters['start'], $filters['end']])
                    ->orWhere(function ($sub) use ($filters) {
                        $sub->where('start_date', '<=', $filters['start'])
                            ->where('end_date', '>=', $filters['end']);
                    });
            })
            ->get();

        $leaveMap = [];
        foreach ($leaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $dateObj) {
                $leaveMap[$leave->user_id . '|' . $dateObj->toDateString()] = $leave->type_label;
            }
        }

        // 4. Generate daily entries from newest to oldest
        $timeline = collect();
        for ($cursor = $endDate->copy(); $cursor->gte($startDate); $cursor->subDay()) {
            $dateString = $cursor->toDateString();
            $dayName = $cursor->locale('id')->translatedFormat('l');
            $dateLabel = $cursor->locale('id')->translatedFormat('l, d M Y');
            $isWeekend = $cursor->isWeekend();

            foreach ($targetEmployees as $emp) {
                // If employee account was created after this date, skip
                if ($emp->created_at && $cursor->lt($emp->created_at->startOfDay())) {
                    continue;
                }

                $key = $emp->id . '|' . $dateString;
                $checkin = $checkIns->get($key);
                $checkout = $checkOuts->get($key);
                $leaveType = $leaveMap[$key] ?? null;

                $durationMinutes = null;
                $durationLabel = '-';
                if ($checkin && $checkout) {
                    $durationMinutes = max(0, (int) floor(($checkout->attendance_time->timestamp - $checkin->attendance_time->timestamp) / 60));
                    $durationLabel = intdiv($durationMinutes, 60) . 'j ' . str_pad((string) ($durationMinutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
                }

                if ($checkin) {
                    $isLate = $checkin->attendance_time->format('H:i:s') > $lateCutoff;
                    $statusCode = $isLate ? 'late' : 'on_time';
                    $statusLabel = $isLate ? 'Terlambat' : 'Tepat waktu';
                    $statusBadge = $isLate ? 'warning' : 'success';
                } elseif ($leaveType) {
                    $statusCode = 'leave';
                    $statusLabel = $leaveType;
                    $statusBadge = 'info';
                } elseif ($isWeekend) {
                    $statusCode = 'weekend';
                    $statusLabel = 'Libur Akhir Pekan';
                    $statusBadge = 'gray';
                } else {
                    $statusCode = 'absent';
                    $statusLabel = 'Tidak Hadir';
                    $statusBadge = 'danger';
                }

                // Apply arrival filter
                if (!$this->matchesArrivalFilter($statusCode, $filters['arrival'] ?? 'all')) {
                    continue;
                }

                $timeline->push([
                    'date' => $dateString,
                    'date_label' => $dateLabel,
                    'day_name' => $dayName,
                    'is_weekend' => $isWeekend,
                    'user' => $emp,
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                    'check_in_time' => $checkin ? $checkin->attendance_time->format('H:i') : '-',
                    'check_out_time' => $checkout ? $checkout->attendance_time->format('H:i') : '-',
                    'duration_label' => $durationLabel,
                    'duration_minutes' => $durationMinutes,
                    'distance_meter' => $checkin ? round($checkin->distance_meter) : null,
                    'face_match_score' => $checkin ? round($checkin->face_match_score, 1) : null,
                    'status_code' => $statusCode,
                    'status_label' => $statusLabel,
                    'status_badge' => $statusBadge,
                ]);
            }
        }

        return $timeline;
    }

    /**
     * Paginate timeline collection.
     */
    public function paginateTimeline(Collection $timeline, int $perPage = 30): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $timeline->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            $timeline->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );
    }

    /**
     * Build employee summary statistics across the date range.
     */
    public function buildSummary(array $filters, Collection $employees, Collection $fullTimeline): array
    {
        $targetEmployees = $employees
            ->when($filters['user_id'] !== 'all', fn ($rows) => $rows->where('id', (int) $filters['user_id']))
            ->when($filters['department'] !== 'all', fn ($rows) => $rows->where('department', $filters['department']))
            ->values();

        $groupedByUser = $fullTimeline->groupBy(fn ($item) => $item['user']->id);

        return $targetEmployees->map(function ($user) use ($groupedByUser) {
            $userRows = $groupedByUser->get($user->id, collect());
            $onTime = $userRows->where('status_code', 'on_time')->count();
            $late = $userRows->where('status_code', 'late')->count();
            $absent = $userRows->where('status_code', 'absent')->count();
            $leave = $userRows->where('status_code', 'leave')->count();
            $totalPresent = $onTime + $late;

            return [
                'user' => $user,
                'total_hadir' => $totalPresent,
                'tepat' => $onTime,
                'terlambat' => $late,
                'tidak_hadir' => $absent,
                'izin_sakit' => $leave,
            ];
        })->all();
    }

    private function matchesArrivalFilter(string $statusCode, string $arrivalFilter): bool
    {
        return match ($arrivalFilter) {
            'on_time' => $statusCode === 'on_time',
            'late' => $statusCode === 'late',
            'absent' => $statusCode === 'absent',
            'leave' => $statusCode === 'leave',
            default => true,
        };
    }
}
