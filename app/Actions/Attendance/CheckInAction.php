<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInAction
{
    public function __construct(
        private readonly GeoLocationService $geoLocationService
    ) {}

    public function execute(User $user, float $latitude, float $longitude, int $accuracy): Attendance
    {
        $today = Carbon::today();

        if (!$user->office_id) {
            throw ValidationException::withMessages([
                'location' => 'You are not assigned to any office.',
            ]);
        }

        $office = $user->office;

        if (!$office->is_active) {
            throw ValidationException::withMessages([
                'location' => 'Your assigned office is currently inactive.',
            ]);
        }

        // Validate accuracy
        if ($accuracy > 150) { 
            throw ValidationException::withMessages([
                'location' => "GPS accuracy is currently {$accuracy}m. Please move to an area with a clearer signal.",
            ]);
        }

        // Haversine calculation
        $distance = $this->geoLocationService->calculateDistanceMeters(
            (float) $office->latitude,
            (float) $office->longitude,
            $latitude,
            $longitude
        );

        if (!$this->geoLocationService->isWithinRadius($distance, $office->radius_meters)) {
            throw ValidationException::withMessages([
                'location' => "You are {$distance}m away from the office. You must be within {$office->radius_meters}m to check in.",
            ]);
        }

        // Use a database transaction and row locking to prevent double check-ins
        return DB::transaction(function () use ($user, $today, $office, $distance, $accuracy) {
            
            // Lock the user row to serialize concurrent attendance attempts for this user
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            $existingAttendance = Attendance::where('user_id', $user->id)
                ->where('date', $today->format('Y-m-d'))
                ->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have already checked in today.',
                ]);
            }

            if ($existingAttendance) {
                 $existingAttendance->update([
                    'check_in' => Carbon::now(),
                    'status' => AttendanceStatus::Present,
                    'office_id' => $office->id,
                    'distance_meters' => $distance,
                    'gps_accuracy' => $accuracy,
                ]);
                return $existingAttendance;
            }

            return Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => Carbon::now(),
                'status' => AttendanceStatus::Present,
                'office_id' => $office->id,
                'distance_meters' => $distance,
                'gps_accuracy' => $accuracy,
            ]);
        });
    }
}
