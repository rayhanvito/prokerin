<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserNotificationCreated;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;

final class BroadcastDatabaseNotification
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || ! $event->notifiable instanceof User) {
            return;
        }

        if (! $event->response instanceof DatabaseNotification) {
            return;
        }

        UserNotificationCreated::dispatch(
            userId: (int) $event->notifiable->id,
            notification: $this->payload($event->response),
        );
    }

    /**
     * @return array{id: string, type: string, title: string, body: string, url: string|null, readAt: string|null, createdAt: string}
     */
    private function payload(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => (string) $notification->id,
            'type' => (string) $notification->type,
            'title' => (string) ($data['title'] ?? $data['jobLabel'] ?? $data['projectName'] ?? class_basename((string) $notification->type)),
            'body' => (string) ($data['body'] ?? $data['reason'] ?? $data['decision'] ?? ''),
            'url' => isset($data['resourceUrl']) && is_string($data['resourceUrl']) ? $data['resourceUrl'] : null,
            'readAt' => $notification->read_at === null ? null : (string) $notification->read_at,
            'createdAt' => (string) $notification->created_at,
        ];
    }
}
