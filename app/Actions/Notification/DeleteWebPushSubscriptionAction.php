<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;

final class DeleteWebPushSubscriptionAction
{
    public function execute(User $user, string $endpoint): void
    {
        $user->deletePushSubscription($endpoint);
    }
}
