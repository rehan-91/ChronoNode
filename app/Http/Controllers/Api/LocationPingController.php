<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationLog;
use App\Services\LocationTrackingConfigService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationPingController extends Controller
{
    public function __construct(
        private readonly LocationTrackingConfigService $configService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'max:500'], // Reject wildly inaccurate GPS spikes
        ]);

        LocationLog::create([
            'user_id' => $request->user()->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'],
            'logged_at' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'recorded',
            'next_ping_interval' => $this->configService->getPingIntervalSeconds(),
        ]);
    }
}
