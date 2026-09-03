<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Office;

class OfficeService
{
    public function createOffice(array $data): Office
    {
        return Office::create($data);
    }

    public function updateOffice(Office $office, array $data): bool
    {
        return $office->update($data);
    }

    public function toggleStatus(Office $office): bool
    {
        return $office->update(['is_active' => !$office->is_active]);
    }
}
