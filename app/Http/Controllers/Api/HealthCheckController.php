<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Execute a deep infrastructure validation sweep.
     * Validates database connectivity and returns a status JSON payload.
     */
    public function __invoke(): JsonResponse
    {
        try {
            // Atomic query against MySQL database connection pool
            DB::connection()->getPdo();

            return response()->json([
                'status' => 'healthy',
                'message' => 'Infrastructure operational',
                'database' => 'connected',
            ], 200);
        } catch (Throwable $e) {
            // Secure log warning to standard error streams
            Log::channel('stderr')->error('Health check failed: Database connection dropped.', [
                'exception' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'status' => 'unhealthy',
                'message' => 'Internal infrastructure failure',
            ], 500);
        }
    }
}
