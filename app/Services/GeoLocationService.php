<?php

declare(strict_types=1);

namespace App\Services;

class GeoLocationService
{
    /**
     * Calculate the distance in meters between two sets of coordinates using the Haversine formula.
     */
    public function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadiusMeters = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadiusMeters * $c);
    }
    
    /**
     * Check if the given coordinates are within the office's allowed radius.
     */
    public function isWithinRadius(int $distanceMeters, int $allowedRadiusMeters): bool
    {
        return $distanceMeters <= $allowedRadiusMeters;
    }
}
