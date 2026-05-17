<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Notifications\Concerns\SendsWebPushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

final class QueueJobFailedNotification extends Notification
{
    use Queueable, SendsWebPushNotifications;

    public function __construct(
        public readonly string $jobLabel,
        public readonly string $reason,
        public readonly ?string $resourceUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->withWebPush(['database']);
    }

    public function toWebPush(object $notifiable, ?Notification $notification = null): WebPushMessage
    {
        return $this->webPushMessage(
            title: 'Export gagal',
            body: "{$this->jobLabel}: {$this->reason}",
            url: $this->resourceUrl,
        );
    }

    /**
     * @return array{jobLabel: string, reason: string, resourceUrl: string|null}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'jobLabel' => $this->jobLabel,
            'reason' => $this->reason,
            'resourceUrl' => $this->resourceUrl,
        ];
    }
}
