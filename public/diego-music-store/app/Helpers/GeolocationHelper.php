<?php

namespace App\Helpers;

class GeolocationHelper
{
    /**
     * Calculate the distance between two geographic coordinates using the Haversine formula.
     *
     * @param  float|null  $lat1
     * @param  float|null  $lon1
     * @param  float|null  $lat2
     * @param  float|null  $lon2
     * @return int distance in meters
     */
    public static function calculateDistanceInMeters(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): int
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return 0;
        }

        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
