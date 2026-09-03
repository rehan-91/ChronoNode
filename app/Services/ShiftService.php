<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Shift;

class ShiftService
{
    public function createShift(array $data): Shift
    {
        return Shift::create($data);
    }

    public function updateShift(Shift $shift, array $data): bool
    {
        return $shift->update($data);
    }
}
