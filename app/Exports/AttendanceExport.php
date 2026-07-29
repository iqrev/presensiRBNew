<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $userId = 'all',
    ) {}

    public function collection()
    {
        $query = Attendance::with('user')
            ->whereBetween('attendance_time', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59',
            ])
            ->checkIn()
            ->valid()
            ->orderBy('attendance_time');

        if ($this->userId !== 'all') {
            $query->where('user_id', $this->userId);
        }

        return $query->get()->map(function ($attendance) {
            $checkOut = Attendance::where('user_id', $attendance->user_id)
                ->where('type', 'check_out')
                ->whereDate('attendance_time', $attendance->attendance_time->toDateString())
                ->valid()
                ->first();

            return [
                'Nama'          => $attendance->user->name,
                'NIK'           => $attendance->user->nik ?? '-',
                'Jabatan'       => $attendance->user->jabatan ?? '-',
                'Tanggal'       => $attendance->attendance_time->format('d/m/Y'),
                'Jam Masuk'     => $attendance->attendance_time->format('H:i'),
                'Jam Keluar'    => $checkOut ? $checkOut->attendance_time->format('H:i') : '-',
                'Jarak (m)'     => round($attendance->distance_meter),
                'Face Score'    => round($attendance->face_match_score, 1) . '%',
                'Status'        => $attendance->status_label,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'NIK', 'Jabatan', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Jarak (m)', 'Face Score', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
