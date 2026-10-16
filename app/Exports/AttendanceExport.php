<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $userId = 'all',
        private readonly string $department = 'all',
        private readonly string $arrival = 'all',
    ) {}

    public function collection()
    {
        $lateCutoff = Carbon::parse(SystemSetting::get('jam_masuk', '08:00'))
            ->addMinutes((int) SystemSetting::get('toleransi_terlambat_menit', 15))
            ->format('H:i:s');

        $query = Attendance::with('user')
            ->whereBetween('attendance_time', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->checkIn()
            ->valid()
            ->when($this->userId !== 'all', fn ($query) => $query->where('user_id', $this->userId))
            ->when($this->department !== 'all', fn ($query) => $query->whereHas(
                'user', fn ($userQuery) => $userQuery->where('department', $this->department)
            ))
            ->when($this->arrival === 'on_time', fn ($query) => $query->whereTime('attendance_time', '<=', $lateCutoff))
            ->when($this->arrival === 'late', fn ($query) => $query->whereTime('attendance_time', '>', $lateCutoff))
            ->orderBy('attendance_time');

        $checkIns = $query->get();
        $checkouts = Attendance::query()
            ->valid()
            ->checkOut()
            ->whereBetween('attendance_time', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->whereIn('user_id', $checkIns->pluck('user_id')->unique())
            ->orderBy('attendance_time')
            ->get()
            ->groupBy(fn ($row) => $row->user_id . '|' . $row->attendance_time->toDateString());

        return $checkIns->map(function ($attendance) use ($checkouts, $lateCutoff) {
            $checkout = $checkouts->get($attendance->user_id . '|' . $attendance->attendance_time->toDateString())?->first();
            $isLate = $attendance->attendance_time->format('H:i:s') > $lateCutoff;
            $duration = '-';

            if ($checkout) {
                $minutes = max(0, (int) floor(($checkout->attendance_time->timestamp - $attendance->attendance_time->timestamp) / 60));
                $duration = intdiv($minutes, 60) . ' jam ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . ' menit';
            }

            return [
                'Nama' => $attendance->user->name,
                'NIK' => $attendance->user->nik ?? '-',
                'Jabatan' => $attendance->user->jabatan ?? '-',
                'Departemen' => $attendance->user->department ?? '-',
                'Tanggal' => $attendance->attendance_time->format('d/m/Y'),
                'Jam Masuk' => $attendance->attendance_time->format('H:i'),
                'Jam Keluar' => $checkout?->attendance_time->format('H:i') ?? '-',
                'Durasi' => $duration,
                'Jarak (m)' => round($attendance->distance_meter),
                'Face Score' => round($attendance->face_match_score, 1) . '%',
                'Status Waktu' => $isLate ? 'Terlambat' : 'Tepat waktu',
                'Status Data' => $attendance->status_label,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'NIK', 'Jabatan', 'Departemen', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Durasi', 'Jarak (m)', 'Face Score', 'Status Waktu', 'Status Data'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
