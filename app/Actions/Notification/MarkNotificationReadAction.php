<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class MarkNotificationReadAction
{
    public function execute(int $actorUserId, string $notificationId): void
    {
        $notification = DB::table('notifications')
            ->where('id', $notificationId)
            ->first(['notifiable_id', 'notifiable_type']);

        if ($notification === null) {
            return;
        }

        if ((int) $notification->notifiable_id !== $actorUserId
            || (string) $notification->notifiable_type !== User::class) {
            throw new AuthorizationException('Notification is not owned by current user.');
        }

        DB::table('notifications')
            ->where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }
}
