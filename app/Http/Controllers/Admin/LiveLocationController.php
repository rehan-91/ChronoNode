<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LiveLocationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(in_array($request->user()->role->value, ['super_admin', 'hr_admin', 'manager']), 403);

        // Fetch only the single latest footprint for each user within the last 14 hours (working day boundary)
        $subQuery = LocationLog::select('user_id', DB::raw('MAX(logged_at) as last_log'))
            ->where('logged_at', '>=', Carbon::now()->subHours(14))
            ->groupBy('user_id');

        $activeLocations = LocationLog::joinSub($subQuery, 'latest_logs', function ($join) {
                $join->on('location_logs.user_id', '=', 'latest_logs.user_id')
                     ->on('location_logs.logged_at', '=', 'latest_logs.last_log');
            })
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'employee_code', 'department_id', 'manager_id')
                      ->with('department:id,name');
            }])
            ->get();

        if ($request->user()->role->value === 'manager') {
            $activeLocations = $activeLocations->filter(fn($log) => $log->user->manager_id === $request->user()->id)->values();
        }

        return Inertia::render('Admin/Locations/Live', [
            'locations' => $activeLocations,
            'currentTime' => Carbon::now()->toISOString(),
        ]);
    }
}
