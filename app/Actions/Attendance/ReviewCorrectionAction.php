<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use App\Models\AttendanceCorrection;
use App\Services\AttendanceCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReviewCorrectionAction
{
    public function __construct(
        private readonly AttendanceCalculationService $calculationService
    ) {}

    public function execute(
        AttendanceCorrection $correction,
        string $status,
        string $reason,
        ?string $editedCheckIn,
        ?string $editedCheckOut
    ): AttendanceCorrection {
        return DB::transaction(function () use ($correction, $status, $reason, $editedCheckIn, $editedCheckOut) {
            
            // Lock row
            $correction = AttendanceCorrection::where('id', $correction->id)->lockForUpdate()->firstOrFail();

            $correction->status = $status;
            $correction->reviewer_reason = $reason;
            $correction->reviewed_at = Carbon::now();

            if ($status === 'approved') {
                $attendance = $correction->attendance;
                
                if ($attendance) {
                    $attendance->check_in = $editedCheckIn ?? $correction->requested_check_in ?? $attendance->check_in;
                    $attendance->check_out = $editedCheckOut ?? $correction->requested_check_out ?? $attendance->check_out;

                    // Re-run shift metrics since times have changed
                    $this->calculationService->calculateMetrics($attendance, $correction->user->shift);
                    $attendance->save();
                }
            }

            $correction->save();

            return $correction;
        });
    }
}
