<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $month = $request->get('month', now()->format('Y-m'));

        [$year, $monthNum] = explode('-', $month);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('attendance_time', $year)
            ->whereMonth('attendance_time', $monthNum)
            ->orderBy('attendance_time')
            ->get()
            ->groupBy(fn($a) => $a->attendance_time->format('Y-m-d'));

        return view('employee.history', compact('user', 'attendances', 'month', 'year', 'monthNum'));
    }
}
