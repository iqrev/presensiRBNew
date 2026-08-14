<?php

namespace App\Services;

use App\DTOs\FaceMatchResult;
use App\Models\Attendance;
use App\Models\AttendanceFailureLog;
use App\Models\FaceReference;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    public function __construct(
        private readonly GeofencingService   $geofencingService,
        private readonly ImageCompressionService $imageService,
    ) {}

    /**
     * Process a kiosk check-in or check-out request without knowing the user beforehand.
     */
    public function process(string $type, UploadedFile $photo, string $descriptorStr, float $lat, float $lng): array
    {
        // 1. Compress & save the captured photo temporarily
        $tempDir = "attendance-photos/temp";
        $compressed = $this->imageService->compressAndStore($photo, $tempDir);
        $capturedPath = storage_path("app/{$compressed['path']}");

        // 2. Local Face Recognition (1:N Search via Euclidean Distance)
        $incomingDescriptor = json_decode($descriptorStr, true);
        if (!is_array($incomingDescriptor) || count($incomingDescriptor) !== 128) {
            $this->imageService->delete($compressed['path']);
            return ['success' => false, 'reason' => 'Data fitur wajah tidak valid. Silakan coba lagi.'];
        }

        $matchResult = $this->findMatchingFace($incomingDescriptor);
        
        $bestMatch = $matchResult['match'];
        $bestDistance = $matchResult['distance'];
        $threshold = 0.50; // Jarak maksimal untuk dianggap cocok (semakin kecil semakin mirip)

        if (!$bestMatch || $bestDistance > $threshold) {
            $this->imageService->delete($compressed['path']);
            return [
                'success' => false,
                'reason'  => 'Wajah tidak dikenali di sistem. Silakan coba lagi atau lapor HR.',
            ];
        }

        $user = $bestMatch->user;
        if (!$user) {
            $this->imageService->delete($compressed['path']);
            return ['success' => false, 'reason' => 'Data karyawan tidak ditemukan. Silakan lapor HR.'];
        }

        // Convert distance to a simulated confidence score (0-100) for logging purposes
        // 0.0 distance = 100% score, 0.5 distance = ~80% score
        $faceScore = max(0, 100 - ($bestDistance * 40));
        
        // 4. Validate double-checkin/checkout
        if ($type === 'check_in' && $this->hasCheckedInToday($user)) {
            $this->imageService->delete($compressed['path']);
            return ['success' => false, 'reason' => "Halo {$user->name}, Anda sudah melakukan check-in hari ini."];
        }
        if ($type === 'check_out' && !$this->hasCheckedInToday($user)) {
            $this->imageService->delete($compressed['path']);
            return ['success' => false, 'reason' => "Halo {$user->name}, Anda belum melakukan check-in hari ini."];
        }
        if ($type === 'check_out' && $this->hasCheckedOutToday($user)) {
            $this->imageService->delete($compressed['path']);
            return ['success' => false, 'reason' => "Halo {$user->name}, Anda sudah melakukan check-out hari ini."];
        }

        // Validate early checkout
        if ($type === 'check_out') {
            $jamPulang = SystemSetting::get('jam_pulang', '17:00');
            $nowTime = now()->format('H:i');

            if ($nowTime < $jamPulang) {
                $this->imageService->delete($compressed['path']);
                return ['success' => false, 'reason' => "Halo {$user->name}, Anda belum bisa check-out. Jadwal pulang adalah pukul {$jamPulang}."];
            }
        }

        // 5. Move temp photo to user folder
        $finalPath = "attendance-photos/{$user->id}/" . basename($compressed['path']);
        Storage::move($compressed['path'], $finalPath);

        // Add watermark
        $this->imageService->addWatermark(storage_path("app/{$finalPath}"), [
            'Nama'   => $user->name,
            'Waktu'  => now()->format('d M Y H:i:s'),
            'Lokasi' => "{$lat}, {$lng}",
            'Tipe'   => $type === 'check_in' ? 'Check-In' : 'Check-Out',
        ]);

        // 6. Geofencing check
        $geoResult = $this->geofencingService->checkLocation($lat, $lng);

        // 7. Determine outcome
        if ($geoResult['within']) {
            $attendance = Attendance::create([
                'user_id'          => $user->id,
                'type'             => $type,
                'attendance_time'  => now(),
                'latitude'         => $lat,
                'longitude'        => $lng,
                'distance_meter'   => $geoResult['distance_meter'],
                'is_within_radius' => true,
                'face_match_score' => $faceScore,
                'is_face_verified' => true,
                'photo_path'       => $finalPath,
                'photo_size_kb'    => $compressed['size_kb'],
                'status'           => 'valid',
            ]);

            return [
                'success'    => true,
                'user_name'  => $user->name,
                'attendance' => $attendance,
                'geo'        => $geoResult,
                'face'       => (object)['score' => $faceScore]
            ];
        }

        // 8. Log the failure if out of radius
        AttendanceFailureLog::create([
            'user_id'          => $user->id,
            'type'             => $type,
            'failure_reason'   => 'out_of_radius',
            'face_match_score' => $faceScore,
            'distance_meter'   => $geoResult['distance_meter'],
            'photo_path'       => $finalPath,
            'latitude'         => $lat,
            'longitude'        => $lng,
            'attempted_at'     => now(),
        ]);

        return [
            'success'   => false,
            'user_name' => $user->name,
            'reason'    => "Halo {$user->name}, Anda berada di luar area kantor ({$geoResult['distance_meter']} meter).",
            'geo'       => $geoResult,
            'face'      => (object)['score' => $faceScore],
        ];
    }

    /**
     * Finds the best matching face reference for a given descriptor.
     */
    public function findMatchingFace(array $incomingDescriptor): array
    {
        $allReferences = FaceReference::with('user')->where('is_active', true)->get();
        
        $bestMatch = null;
        $bestDistance = 999.0;

        foreach ($allReferences as $ref) {
            $storedDescriptor = json_decode($ref->face_token, true);
            if (!is_array($storedDescriptor) || count($storedDescriptor) !== 128) {
                continue; // Skip invalid legacy data
            }

            // Calculate Euclidean distance
            $sumSq = 0;
            for ($i = 0; $i < 128; $i++) {
                $diff = $incomingDescriptor[$i] - $storedDescriptor[$i];
                $sumSq += $diff * $diff;
            }
            $distance = sqrt($sumSq);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $ref;
            }
        }

        return [
            'match'    => $bestMatch,
            'distance' => $bestDistance,
        ];
    }

    /**
     * Check if employee has already checked in today.
     */
    public function hasCheckedInToday(User $user): bool
    {
        return Attendance::where('user_id', $user->id)
            ->where('type', 'check_in')
            ->where('status', 'valid')
            ->whereDate('attendance_time', today())
            ->exists();
    }

    public function hasCheckedOutToday(User $user): bool
    {
        return Attendance::where('user_id', $user->id)
            ->where('type', 'check_out')
            ->where('status', 'valid')
            ->whereDate('attendance_time', today())
            ->exists();
    }
}
