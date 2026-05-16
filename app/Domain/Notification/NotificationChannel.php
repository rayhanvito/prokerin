<?php

declare(strict_types=1);

namespace App\Domain\Notification;

enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::InApp => 'In-app',
            self::Email => 'Email',
            self::WhatsApp => 'WhatsApp',
        };
    }
}
