<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceCalculationService;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckOutAction
{
    public function __construct(
        private readonly GeoLocationService $geoLocationService,
        private readonly AttendanceCalculationService $calculationService
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

        // Validate accuracy
        if ($accuracy > 150) { 
            throw ValidationException::withMessages([
                'location' => "GPS accuracy is currently {$accuracy}m. Please move to an area with a clearer signal.",
            ]);
        }

        $distance = $this->geoLocationService->calculateDistanceMeters(
            (float) $office->latitude,
            (float) $office->longitude,
            $latitude,
            $longitude
        );

        if (!$this->geoLocationService->isWithinRadius($distance, $office->radius_meters)) {
            throw ValidationException::withMessages([
                'location' => "You are {$distance}m away from the office. You must be within {$office->radius_meters}m to check out.",
            ]);
        }

        return DB::transaction(function () use ($user, $today) {
            
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today->format('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if (!$attendance || !$attendance->check_in) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have not checked in today.',
                ]);
            }

            if ($attendance->check_out) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have already checked out today.',
                ]);
            }

            $attendance->check_out = Carbon::now();
            
            // Calculate shift metrics (late, early departure, overtime, working minutes)
            $this->calculationService->calculateMetrics($attendance, $user->shift);

            $attendance->save();

            return $attendance;
        });
    }
}
