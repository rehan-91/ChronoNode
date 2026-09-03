<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Holiday;

class HolidayService
{
    public function createHoliday(array $data): Holiday
    {
        return Holiday::create($data);
    }

    public function updateHoliday(Holiday $holiday, array $data): bool
    {
        return $holiday->update($data);
    }
}
