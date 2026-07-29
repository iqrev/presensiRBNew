<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceFailureLog;
use App\Models\LeaveRequest;
use App\Models\SystemSetting;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalKaryawan = User::role('karyawan')->active()->count();

        // Rekap hari ini
        $todayStats = [
            'hadir'   => Attendance::checkIn()->today()->valid()->count(),
            'terlambat' => 0, // calculated below
            'belum'   => 0,   // calculated below
        ];

        // Hitung terlambat
        $jamMasuk  = SystemSetting::get('jam_masuk', '08:00');
        $toleransi = (int) SystemSetting::get('toleransi_terlambat_menit', 15);
        $batasLambat = \Carbon\Carbon::parse(today()->format('Y-m-d') . ' ' . $jamMasuk)->addMinutes($toleransi);

        $todayStats['terlambat'] = Attendance::checkIn()->today()->valid()
            ->where('attendance_time', '>', $batasLambat)->count();

        // Karyawan yang belum absen hari ini
        $sudahAbsenIds = Attendance::checkIn()->today()->valid()->pluck('user_id');
        $todayStats['belum'] = User::role('karyawan')->active()
            ->whereNotIn('id', $sudahAbsenIds)->count();

        // Izin pending
        $pendingLeaves = LeaveRequest::with('user')->where('status', 'pending')->latest()->get();

        // Log presensi gagal terbaru (10 terakhir)
        $failureLogs = AttendanceFailureLog::with('user')->latest()->limit(10)->get();

        // Daftar karyawan presensi hari ini
        $todayAttendances = Attendance::with('user')
            ->today()->valid()->checkIn()
            ->orderBy('attendance_time')
            ->get();

        // Presensi manual pending
        $manualRequests = Attendance::with('user')
            ->where('status', 'manual_request')
            ->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalKaryawan', 'todayStats', 'pendingLeaves',
            'failureLogs', 'todayAttendances', 'manualRequests',
            'jamMasuk'
        ));
    }
}
