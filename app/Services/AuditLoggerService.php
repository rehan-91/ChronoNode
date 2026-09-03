<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLoggerService
{
    /**
     * Records an immutable, append-only audit trail for administrative and critical actions.
     */
    public function log(string $action, string $modelType, int|string $modelId, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(), // Resolves the active signed-in admin/user
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => (string) $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
