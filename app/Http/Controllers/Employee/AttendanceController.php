<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index()
    {
        return view('employee.checkin');
    }

    public function checkIn(AttendanceRequest $request)
    {
        $result = $this->attendanceService->process(
            'check_in',
            $request->file('photo'),
            $request->input('descriptor'),
            $request->float('latitude'),
            $request->float('longitude'),
        );

        if ($result['success']) {
            return response()->json([
                'success'         => true,
                'message'         => "Check-in berhasil! Selamat bekerja, {$result['user_name']}.",
                'attendance_time' => $result['attendance']['attendance_time']->format('H:i:s'),
                'face_score'      => round($result['face']->score, 1),
                'distance'        => round($result['geo']['distance_meter']),
                'user_name'       => $result['user_name'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['reason'],
        ], 422);
    }

    public function checkOut(AttendanceRequest $request)
    {
        $result = $this->attendanceService->process(
            'check_out',
            $request->file('photo'),
            $request->input('descriptor'),
            $request->float('latitude'),
            $request->float('longitude'),
        );

        if ($result['success']) {
            return response()->json([
                'success'         => true,
                'message'         => "Check-out berhasil! Sampai jumpa, {$result['user_name']}.",
                'attendance_time' => $result['attendance']['attendance_time']->format('H:i:s'),
                'face_score'      => round($result['face']->score, 1),
                'distance'        => round($result['geo']['distance_meter']),
                'user_name'       => $result['user_name'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['reason'],
        ], 422);
    }
}
