<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class MarkAllNotificationsReadAction
{
    public function execute(int $actorUserId): int
    {
        return DB::table('notifications')
            ->where('notifiable_id', $actorUserId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }
}
