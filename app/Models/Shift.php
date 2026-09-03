<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'name', 'start_time', 'end_time', 'late_threshold_minutes',
        'early_departure_threshold_minutes', 'is_flexible'
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'late_threshold_minutes' => 'integer',
            'early_departure_threshold_minutes' => 'integer',
            'is_flexible' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
