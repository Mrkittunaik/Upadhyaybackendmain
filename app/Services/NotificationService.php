<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Notification;

class NotificationService
{
    public function send(int $userId, NotificationType $type, string $title, ?string $message = null, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ?: null,
        ]);
    }
}
