<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\QueueJobFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class NotificationDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_inertia_share_includes_notifications_context_with_recent_and_unread(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->notify(new QueueJobFailedNotification('Export Test', 'Timeout', null));
        $user->notify(new QueueJobFailedNotification('Export Older', 'Timeout', null));

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('notifications.unreadCount', 2)
                ->has('notifications.recent', 2)
        );
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->notify(new QueueJobFailedNotification('Export Test', 'Timeout', null));
        $notificationId = (string) DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->orderByDesc('created_at')
            ->value('id');

        $response = $this->actingAs($user)->patch(
            route('notifications.read', ['notification' => $notificationId]),
        );

        $response->assertRedirect();
        $this->assertNotNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at'),
        );
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $owner->notify(new QueueJobFailedNotification('Owner Job', 'Failed', null));
        $notificationId = (string) DB::table('notifications')
            ->where('notifiable_id', $owner->id)
            ->orderByDesc('created_at')
            ->value('id');

        $response = $this->actingAs($member)->patch(
            route('notifications.read', ['notification' => $notificationId]),
        );

        $response->assertForbidden();
        $this->assertNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at'),
        );
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->notify(new QueueJobFailedNotification('A', 'Failed', null));
        $user->notify(new QueueJobFailedNotification('B', 'Failed', null));

        $response = $this->actingAs($user)->patch(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertSame(
            0,
            (int) DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        );
    }

    public function test_recent_notifications_endpoint_returns_latest_five_for_current_user(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $otherUser = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        foreach (range(1, 6) as $index) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => QueueJobFailedNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'jobLabel' => "Export {$index}",
                    'reason' => "Reason {$index}",
                    'resourceUrl' => null,
                ]),
                'read_at' => $index === 1 ? now() : null,
                'created_at' => now()->addMinutes($index),
                'updated_at' => now()->addMinutes($index),
            ]);
        }

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => QueueJobFailedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode([
                'jobLabel' => 'Other User Export',
                'reason' => 'Hidden',
                'resourceUrl' => null,
            ]),
            'read_at' => null,
            'created_at' => now()->addMinutes(10),
            'updated_at' => now()->addMinutes(10),
        ]);

        $this->actingAs($user)
            ->getJson(route('notifications.recent'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unreadCount', 5)
            ->assertJsonCount(5, 'data.recent')
            ->assertJsonPath('data.recent.0.title', 'Export 6')
            ->assertJsonMissing(['title' => 'Export 1'])
            ->assertJsonMissing(['title' => 'Other User Export']);
    }
}
