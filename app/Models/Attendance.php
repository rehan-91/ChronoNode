<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'check_in', 'check_out', 'status',
        'working_minutes', 'late_minutes', 'early_departure_minutes',
        'overtime_minutes', 'office_id', 'distance_meters', 'gps_accuracy',
        'is_automatic_checkout'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'status' => AttendanceStatus::class,
            'working_minutes' => 'integer',
            'late_minutes' => 'integer',
            'early_departure_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'distance_meters' => 'integer',
            'gps_accuracy' => 'integer',
            'is_automatic_checkout' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function correctionRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
