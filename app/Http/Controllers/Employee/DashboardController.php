<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $checkIn  = $user->todayCheckIn();
        $checkOut = $user->todayCheckOut();

        $jamMasuk  = SystemSetting::get('jam_masuk', '08:00');
        $jamPulang = SystemSetting::get('jam_pulang', '17:00');
        $toleransi = (int) SystemSetting::get('toleransi_terlambat_menit', 15);

        // Status presensi hari ini
        $todayStatus = 'belum_absen';
        $isLate      = false;

        if ($checkIn) {
            $todayStatus = $checkOut ? 'sudah_pulang' : 'sudah_masuk';
            $batasLambat = \Carbon\Carbon::parse(today()->format('Y-m-d') . ' ' . $jamMasuk)->addMinutes($toleransi);
            $isLate      = $checkIn->attendance_time->gt($batasLambat);
        }

        // Rekap bulan ini
        $thisMonthStats = [
            'hadir'   => Attendance::where('user_id', $user->id)->checkIn()->thisMonth()->valid()->count(),
            'izin'    => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')
                            ->whereMonth('start_date', now()->month)->count(),
        ];

        // Pengajuan izin terbaru
        $recentLeaves = LeaveRequest::where('user_id', $user->id)
            ->latest()->limit(3)->get();

        return view('employee.dashboard', compact(
            'user', 'checkIn', 'checkOut', 'todayStatus', 'isLate',
            'jamMasuk', 'jamPulang', 'thisMonthStats', 'recentLeaves'
        ));
    }
}
