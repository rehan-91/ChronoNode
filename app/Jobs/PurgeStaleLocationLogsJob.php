<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LocationLog;
use App\Services\LocationTrackingConfigService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PurgeStaleLocationLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(LocationTrackingConfigService $configService): void
    {
        $retentionDays = $configService->getRetentionDays();
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $deletedCount = LocationLog::where('logged_at', '<', $cutoffDate)->delete();

        Log::info("PurgeStaleLocationLogsJob completed.", [
            'retention_days' => $retentionDays,
            'records_deleted' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
        ]);
    }
}
