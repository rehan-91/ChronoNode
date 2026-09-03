<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $employeeQuery = User::where('status', 'active');
        $attendanceQuery = Attendance::where('date', $today);

        // Scope to Manager's assigned subordinates if they aren't HR Admin
        if ($user->role->value === 'manager') {
            $employeeQuery->where('manager_id', $user->id);
            $attendanceQuery->whereHas('user', function ($query) use ($user) {
                $query->where('manager_id', $user->id);
            });
        }

        $totalEmployees = $employeeQuery->count();
        $todayAttendances = $attendanceQuery->get();

        $present = $todayAttendances->where('status', 'present')->count();
        $late = $todayAttendances->where('late_minutes', '>', 0)->count();
        $halfDay = $todayAttendances->where('status', 'half_day')->count();
        $onLeave = $todayAttendances->whereIn('status', ['leave', 'holiday'])->count();
        $missingCheckout = $todayAttendances->whereNotNull('check_in')->whereNull('check_out')->count();
        
        $absent = $totalEmployees - ($present + $halfDay + $onLeave);

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'total_employees' => $totalEmployees,
                'present' => $present,
                'late' => $late,
                'half_day' => $halfDay,
                'on_leave' => $onLeave,
                'missing_checkout' => $missingCheckout,
                'absent' => max(0, $absent),
            ]
        ]);
    }
}
