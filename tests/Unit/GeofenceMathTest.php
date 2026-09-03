<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GeofenceMathTest extends TestCase
{
    /**
     * Haversine formula calculates the great-circle distance between two points on a sphere.
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function test_haversine_calculates_distance_zero_for_same_coordinates(): void
    {
        $distance = $this->calculateHaversineDistance(40.7128, -74.0060, 40.7128, -74.0060);
        
        $this->assertEquals(0.0, $distance);
    }

    public function test_haversine_calculates_correct_distance_between_two_points(): void
    {
        // Eiffel Tower to Louvre Museum (Approx 3.5 km)
        $distance = $this->calculateHaversineDistance(48.8584, 2.2945, 48.8606, 2.3376);
        
        // Assert distance is around 3150 meters (give or take a few meters for floating point)
        $this->assertGreaterThan(3100, $distance);
        $this->assertLessThan(3200, $distance);
    }
}
