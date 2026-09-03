<?php

declare(strict_types=1);

namespace App\Services;

class LocationTrackingConfigService
{
    /**
     * Retrieves the adjustable periodic update interval for active footprints.
     */
    public function getPingIntervalSeconds(): int
    {
        // In a production environment, this would retrieve from a CompanySettings table
        return config('attendance.location_ping_interval_seconds', 60); 
    }

    /**
     * Retrieves the storage lifecycle rules for automated cleanup.
     */
    public function getRetentionDays(): int
    {
        return config('attendance.location_retention_days', 30);
    }
}
