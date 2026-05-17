<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;
use NotificationChannels\WebPush\PushSubscription;

final class StoreWebPushSubscriptionAction
{
    /**
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}, expirationTime?: int|null}  $subscription
     */
    public function execute(User $user, array $subscription): PushSubscription
    {
        return $user->updatePushSubscription(
            endpoint: $subscription['endpoint'],
            key: $subscription['keys']['p256dh'],
            token: $subscription['keys']['auth'],
            contentEncoding: 'aesgcm',
        );
    }
}
