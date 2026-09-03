<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsRegistryService
{
    /**
     * Resolves a global setting, falling back to a hardcoded default to prevent magic numbers.
     * Implements aggressive caching to protect DB performance during frequent reads.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('settings.registry.' . $key, function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Overwrites a global setting and clears its cache.
     */
    public function set(string $key, mixed $value): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget('settings.registry.' . $key);
    }

    /**
     * Returns all settings initialized with their defaults for the administrative dashboard.
     */
    public function getAllDefaults(): array
    {
        return [
            'timezone' => $this->get('timezone', 'UTC'),
            'gps_accuracy_threshold_meters' => $this->get('gps_accuracy_threshold_meters', 200),
            'default_geofence_radius_meters' => $this->get('default_geofence_radius_meters', 50),
            'late_arrival_buffer_minutes' => $this->get('late_arrival_buffer_minutes', 15),
            'auto_checkout_limit_hours' => $this->get('auto_checkout_limit_hours', 14),
        ];
    }
}
