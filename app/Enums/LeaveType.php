<?php

declare(strict_types=1);

namespace App\Enums;

enum LeaveType: string
{
    case Casual = 'casual';
    case Sick = 'sick';
    case Earned = 'earned';
    case Unpaid = 'unpaid';
}
