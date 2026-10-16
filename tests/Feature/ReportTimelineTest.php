<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AttendanceReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::create(['name' => 'karyawan', 'guard_name' => 'web']);

        SystemSetting::set('jam_masuk', '08:00');
        SystemSetting::set('toleransi_terlambat_menit', 15);
    }

    public function test_timeline_generates_empty_days_for_selected_period(): void
    {
        $employee = User::factory()->create([
            'name' => 'Budi Santoso',
            'created_at' => Carbon::parse('2026-10-01'),
        ]);
        $employee->assignRole('karyawan');

        $service = app(AttendanceReportService::class);
        $filters = [
            'start' => '2026-10-12', // Senin
            'end' => '2026-10-16',   // Jumat (5 hari kerja)
            'user_id' => (string) $employee->id,
            'department' => 'all',
            'arrival' => 'all',
        ];

        $employees = collect([$employee]);
        $timeline = $service->getDailyTimeline($filters, $employees);

        // Harus menghasilkan 5 hari walaupun tidak ada data presensi sama sekali
        $this->assertCount(5, $timeline);

        // Pastikan semua 5 hari bertatus absent (tidak hadir) karena tidak ada presensi
        foreach ($timeline as $day) {
            $this->assertEquals('absent', $day['status_code']);
            $this->assertEquals('Tidak Hadir', $day['status_label']);
            $this->assertEquals('-', $day['check_in_time']);
            $this->assertEquals('-', $day['check_out_time']);
        }

        $summary = $service->buildSummary($filters, $employees, $timeline);
        $this->assertEquals(0, $summary[0]['total_hadir']);
        $this->assertEquals(5, $summary[0]['tidak_hadir']);
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $employee = User::factory()->create();
        $employee->assignRole('karyawan');

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'start' => '2026-10-12',
            'end' => '2026-10-14',
            'user_id' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Detail Presensi Harian');
        $response->assertSee('Tidak Hadir');
    }
}
