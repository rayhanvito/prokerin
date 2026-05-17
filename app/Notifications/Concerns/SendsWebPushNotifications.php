<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

trait SendsWebPushNotifications
{
    /**
     * @param  array<int, class-string|string>  $channels
     * @return array<int, class-string|string>
     */
    private function withWebPush(array $channels): array
    {
        if (filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key'))) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    private function webPushMessage(string $title, string $body, ?string $url = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->data([
                'url' => $url ?? route('notifications.index', absolute: false),
            ]);
    }
}
