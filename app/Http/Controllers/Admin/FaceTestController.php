<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class FaceTestController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {}

    public function index()
    {
        return view('admin.face-test');
    }

    public function match(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string',
        ], [
            'descriptor.required' => 'Fitur wajah tidak terdeteksi oleh sistem.',
        ]);

        $descriptorStr = $request->input('descriptor');
        $incomingDescriptor = json_decode($descriptorStr, true);

        if (!is_array($incomingDescriptor) || count($incomingDescriptor) !== 128) {
            return response()->json(['success' => false, 'message' => 'Data fitur wajah tidak valid.'], 422);
        }

        $matchResult = $this->attendanceService->findMatchingFace($incomingDescriptor);
        
        $bestMatch = $matchResult['match'];
        $bestDistance = $matchResult['distance'];
        $threshold = 0.50; // Jarak maksimal untuk dianggap cocok

        if (!$bestMatch || $bestDistance > $threshold) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak dikenali di sistem.',
            ], 404);
        }

        // Convert distance to a simulated confidence score (0-100)
        $score = max(0, 100 - ($bestDistance * 40));

        return response()->json([
            'success' => true,
            'message' => 'Wajah Cocok',
            'data' => [
                'employee_name' => $bestMatch->user->name,
                'score'         => round($score, 2),
                'distance'      => round($bestDistance, 4),
            ]
        ]);
    }
}
