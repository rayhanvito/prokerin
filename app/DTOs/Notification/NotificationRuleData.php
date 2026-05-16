<?php

declare(strict_types=1);

namespace App\DTOs\Notification;

use App\Domain\Notification\NotificationChannel;
use App\Domain\Notification\NotificationEvent;

final readonly class NotificationRuleData
{
    /**
     * @param  array<int, NotificationChannel>  $channels
     */
    public function __construct(
        public NotificationEvent $event,
        public string $audience,
        public array $channels,
        public string $trigger,
        public bool $isActive = false,
    ) {}

    /**
     * @return array{event: string, label: string, audience: string, channels: array<int, string>, trigger: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event->value,
            'label' => $this->event->label(),
            'audience' => $this->audience,
            'channels' => array_map(
                static fn (NotificationChannel $channel): string => $channel->value,
                $this->channels,
            ),
            'trigger' => $this->trigger,
            'status' => $this->isActive ? 'active' : 'planned',
        ];
    }
}
