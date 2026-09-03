<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;

class AttendanceCalculationService
{
    /**
     * Calculate metrics such as working minutes, late minutes, early departure minutes, and overtime.
     */
    public function calculateMetrics(Attendance $attendance, ?Shift $shift): void
    {
        if (!$attendance->check_in || !$attendance->check_out) {
            return;
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($attendance->check_out);

        // Total Working Minutes
        $workingMinutes = $checkIn->diffInMinutes($checkOut);
        $attendance->working_minutes = max(0, $workingMinutes);

        if ($shift) {
            $shiftStart = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . Carbon::parse($shift->start_time)->format('H:i:s'));
            $shiftEnd = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . Carbon::parse($shift->end_time)->format('H:i:s'));

            // If it's a night shift, the end time might be on the next day.
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }

            // Late Minutes
            $lateMinutes = 0;
            $allowedCheckIn = $shiftStart->copy()->addMinutes($shift->late_threshold_minutes);
            if ($checkIn->greaterThan($allowedCheckIn)) {
                $lateMinutes = $shiftStart->diffInMinutes($checkIn);
            }
            $attendance->late_minutes = max(0, $lateMinutes);

            // Early Departure Minutes
            $earlyDepartureMinutes = 0;
            $allowedCheckOut = $shiftEnd->copy()->subMinutes($shift->early_departure_threshold_minutes);
            if ($checkOut->lessThan($allowedCheckOut)) {
                $earlyDepartureMinutes = $checkOut->diffInMinutes($shiftEnd);
            }
            $attendance->early_departure_minutes = max(0, $earlyDepartureMinutes);

            // Overtime Minutes
            $overtimeMinutes = 0;
            if ($checkOut->greaterThan($shiftEnd)) {
                $overtimeMinutes = $shiftEnd->diffInMinutes($checkOut);
            }
            $attendance->overtime_minutes = max(0, $overtimeMinutes);
            
            // Half Day Logic: if working minutes is less than half of shift duration
            $shiftDurationMinutes = $shiftStart->diffInMinutes($shiftEnd);
            if ($attendance->working_minutes > 0 && $attendance->working_minutes < ($shiftDurationMinutes / 2)) {
                $attendance->status = AttendanceStatus::HalfDay;
            } else {
                $attendance->status = AttendanceStatus::Present;
            }
        } else {
            // No strict shift assigned, default to present if worked.
            if ($attendance->working_minutes > 0) {
                $attendance->status = AttendanceStatus::Present;
            }
        }
    }
}
