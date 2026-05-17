<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class QueueJobFailedNotification extends Notification
{
    use Queueable;

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
        return ['database'];
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
