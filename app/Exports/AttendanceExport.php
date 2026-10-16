<?php

namespace App\Exports;

use App\Models\User;
use App\Services\AttendanceReportService;
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
        $service = app(AttendanceReportService::class);
        $employees = User::role('karyawan')->orderBy('name')->get();

        $filters = [
            'start' => $this->startDate,
            'end' => $this->endDate,
            'user_id' => $this->userId,
            'department' => $this->department,
            'arrival' => $this->arrival,
        ];

        $timeline = $service->getDailyTimeline($filters, $employees);

        return $timeline->map(function ($item) {
            return [
                'Nama' => $item['user']->name,
                'NIK' => $item['user']->nik ?? '-',
                'Jabatan' => $item['user']->jabatan ?? '-',
                'Departemen' => $item['user']->department ?? '-',
                'Hari' => $item['day_name'],
                'Tanggal' => Carbon::parse($item['date'])->format('d/m/Y'),
                'Jam Masuk' => $item['check_in_time'],
                'Jam Keluar' => $item['check_out_time'],
                'Durasi' => $item['duration_label'],
                'Jarak (m)' => $item['distance_meter'] !== null ? $item['distance_meter'] : '-',
                'Face Score' => $item['face_match_score'] !== null ? $item['face_match_score'] . '%' : '-',
                'Status Kehadiran' => $item['status_label'],
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'NIK', 'Jabatan', 'Departemen', 'Hari', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Durasi', 'Jarak (m)', 'Face Score', 'Status Kehadiran'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
