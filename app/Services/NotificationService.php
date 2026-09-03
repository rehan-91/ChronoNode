<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InternalNotification;

class NotificationService
{
    /**
     * Dispatches internal alerts directly into the decoupled notification frame.
     */
    public function send(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        InternalNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type, // success, warning, info, error
            'action_link' => $link,
        ]);
    }
}
