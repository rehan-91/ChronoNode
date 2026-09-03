<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function submitRequest(User $user, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lessThan($startDate)) {
            throw ValidationException::withMessages(['end_date' => 'End date cannot be before start date.']);
        }

        return LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    public function reviewRequest(LeaveRequest $leaveRequest, string $status, ?string $reviewerReason, User $reviewer): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $status, $reviewerReason, $reviewer) {
            $leaveRequest = LeaveRequest::where('id', $leaveRequest->id)->lockForUpdate()->firstOrFail();
            
            $leaveRequest->status = $status;
            $leaveRequest->reviewer_reason = $reviewerReason;
            $leaveRequest->reviewed_by = $reviewer->id;
            $leaveRequest->reviewed_at = Carbon::now();
            $leaveRequest->save();

            // If approved, preemptively inject Leave attendance records to prevent auto-absence markers
            if ($status === 'approved') {
                $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
                
                foreach ($period as $date) {
                    $dateString = $date->format('Y-m-d');
                    
                    // Avoid overriding if already present (e.g. they checked in anyway)
                    $existing = Attendance::where('user_id', $leaveRequest->user_id)->where('date', $dateString)->first();
                    
                    if (!$existing) {
                        Attendance::create([
                            'user_id' => $leaveRequest->user_id,
                            'date' => $dateString,
                            'status' => 'leave',
                            'working_minutes' => 0,
                            'late_minutes' => 0,
                            'early_departure_minutes' => 0,
                            'overtime_minutes' => 0,
                        ]);
                    }
                }
            }

            return $leaveRequest;
        });
    }
}
