<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Events\UserNotificationCreated;
use App\Models\User;
use App\Notifications\QueueJobFailedNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class NotificationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_database_notification_dispatches_realtime_event_to_user_channel(): void
    {
        Event::fake([UserNotificationCreated::class]);

        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $user->notify(new QueueJobFailedNotification('Export Proposal', 'Timeout', '/reports/exports'));

        Event::assertDispatched(
            UserNotificationCreated::class,
            function (UserNotificationCreated $event) use ($user): bool {
                $channels = $event->broadcastOn();

                return $event->userId === (int) $user->id
                    && $channels instanceof PrivateChannel
                    && $channels->name === 'private-App.Models.User.'.$user->id
                    && $event->broadcastAs() === 'UserNotificationCreated'
                    && $event->broadcastWith()['title'] === 'Export Proposal'
                    && $event->broadcastWith()['body'] === 'Timeout'
                    && $event->broadcastWith()['url'] === '/reports/exports';
            },
        );
    }

    public function test_cross_user_mark_read_is_rejected_for_realtime_notification_records(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $owner->notify(new QueueJobFailedNotification('Owner Export', 'Failed', null));
        $notificationId = (string) DB::table('notifications')
            ->where('notifiable_id', $owner->id)
            ->value('id');

        $this->actingAs($member)
            ->patch(route('notifications.read', ['notification' => $notificationId]))
            ->assertForbidden();
    }

    public function test_mark_all_read_populates_current_users_unread_notifications(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $user->notify(new QueueJobFailedNotification('A', 'Failed', null));
        $user->notify(new QueueJobFailedNotification('B', 'Failed', null));

        $this->actingAs($user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(
            0,
            (int) DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        );
    }
}
