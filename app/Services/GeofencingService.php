<?php

namespace App\Services;

use App\Models\OfficeLocation;

class GeofencingService
{
    /**
     * Haversine formula — pure PHP, no external library.
     * @return float distance in meters
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Check whether the user is within the active office radius.
     */
    public function checkLocation(float $userLat, float $userLng): array
    {
        // Ambil semua lokasi kantor yang aktif
        $locations = OfficeLocation::active()->get();

        if ($locations->isEmpty()) {
            return [
                'within'         => false,
                'distance_meter' => 0,
                'radius_meter'   => 0,
                'office_name'    => null,
                'error'          => 'Lokasi kantor belum dikonfigurasi.',
            ];
        }

        $minDistance = PHP_FLOAT_MAX;
        $closestLocation = null;

        // Cek satu per satu
        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $userLat, $userLng,
                $location->latitude, $location->longitude
            );

            // Jika user berada di dalam radius salah satu lokasi aktif, langsung rekam berhasil
            if ($distance <= $location->radius_meter) {
                return [
                    'within'         => true,
                    'distance_meter' => round($distance, 2),
                    'radius_meter'   => $location->radius_meter,
                    'office_name'    => $location->name,
                    'error'          => null,
                ];
            }

            // Simpan data lokasi yang paling dekat untuk pesan error
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestLocation = $location;
            }
        }

        // Jika tidak masuk ke radius satupun, kembalikan jarak dari kantor terdekat
        return [
            'within'         => false,
            'distance_meter' => round($minDistance, 2),
            'radius_meter'   => $closestLocation->radius_meter,
            'office_name'    => $closestLocation->name,
            'error'          => null,
        ];
    }
}
