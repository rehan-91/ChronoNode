<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\ReportExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompileReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Set high timeout to allow intensive reports to finish.
     */
    public $timeout = 300;

    public function __construct(
        private readonly User $requester,
        private readonly array $filters
    ) {}

    public function handle(ReportExportService $service): void
    {
        $filename = $service->compileCsv(
            $this->filters['type'],
            $this->filters['start_date'],
            $this->filters['end_date'],
            isset($this->filters['department_id']) ? (int) $this->filters['department_id'] : null
        );

        // In a complete event-driven setup, we would dispatch a Notification here
        // to inform $this->requester that $filename is ready for download.
    }
}
