<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array{id: string, type: string, title: string, body: string, url: string|null, readAt: string|null, createdAt: string}  $notification
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $notification,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'UserNotificationCreated';
    }

    /**
     * @return array{id: string, type: string, title: string, body: string, url: string|null, readAt: string|null, createdAt: string}
     */
    public function broadcastWith(): array
    {
        return $this->notification;
    }
}
