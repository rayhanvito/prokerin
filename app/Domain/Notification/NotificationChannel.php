<?php

declare(strict_types=1);

namespace App\Domain\Notification;

enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::InApp => 'In-app',
            self::Email => 'Email',
        };
    }
}
