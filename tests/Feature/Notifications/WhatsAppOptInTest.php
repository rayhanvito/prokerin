<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use App\Notifications\Channels\WhatsAppNotificationChannel;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class WhatsAppOptInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_whatsapp_channel_skips_dispatch_when_user_opted_out(): void
    {
        Queue::fake();

        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->update([
            'whatsapp_number' => '+628123456789',
            'whatsapp_opt_in' => false,
        ]);

        $channel = new WhatsAppNotificationChannel;
        $channel->send($user, new TaskDeadlineReminderNotification(
            taskTitle: 'Test',
            projectName: 'Demo',
            dueAt: 'today',
        ));

        Queue::assertNothingPushed();
    }

    public function test_whatsapp_channel_dispatches_when_user_opted_in_with_number(): void
    {
        Queue::fake();

        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->update([
            'whatsapp_number' => '+628123456789',
            'whatsapp_opt_in' => true,
        ]);

        $channel = new WhatsAppNotificationChannel;
        $channel->send($user, new TaskDeadlineReminderNotification(
            taskTitle: 'Test',
            projectName: 'Demo',
            dueAt: 'today',
        ));

        Queue::assertPushed(SendWhatsAppReminderJob::class);
    }

    public function test_user_can_update_whatsapp_opt_in_via_profile_form(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp_number' => '+628111000222',
            'whatsapp_opt_in' => false,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('+628111000222', (string) $user->whatsapp_number);
        $this->assertFalse((bool) $user->whatsapp_opt_in);
    }
}
