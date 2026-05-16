<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Notification\NotificationEvent;
use App\Jobs\SendWhatsAppReminderJob;
use Illuminate\Support\Facades\DB;

final class QueueWhatsAppNotificationAction
{
    /**
     * @param  array<int, int>  $userIds
     */
    public function execute(
        int $organizationId,
        NotificationEvent $event,
        array $userIds,
        string $messageType,
        string $message,
    ): int {
        if (! $this->eventAllowsWhatsApp($organizationId, $event)) {
            return 0;
        }

        $users = DB::table('users')
            ->whereIn('id', array_values(array_unique($userIds)))
            ->whereNotNull('whatsapp_number')
            ->get(['id', 'whatsapp_number']);

        foreach ($users as $user) {
            SendWhatsAppReminderJob::dispatch(
                organizationId: $organizationId,
                userId: (int) $user->id,
                recipientNumber: (string) $user->whatsapp_number,
                messageType: $messageType,
                message: $message,
            )->onQueue('notifications');
        }

        return $users->count();
    }

    private function eventAllowsWhatsApp(int $organizationId, NotificationEvent $event): bool
    {
        $channels = DB::table('notification_rules')
            ->where('organization_id', $organizationId)
            ->where('event', $event->value)
            ->value('channels');

        if ($channels === null) {
            return false;
        }

        return in_array('whatsapp', json_decode((string) $channels, true) ?: [], true);
    }
}
