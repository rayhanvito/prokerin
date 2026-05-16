<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use App\Notifications\Channels\WhatsAppNotificationChannel;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class TaskDeadlineReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_queue_task_deadline_reminders_for_workspace_pics(): void
    {
        Notification::fake();
        Queue::fake();

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $lead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();

        DB::table('project_tasks')
            ->where('title', 'Konfirmasi narasumber')
            ->update(['due_at' => now()->addDay()->toDateString()]);

        $this->actingAs($secretary)
            ->post(route('notifications.task-deadline-reminders.store'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo(
            $lead,
            TaskDeadlineReminderNotification::class,
            fn (TaskDeadlineReminderNotification $notification): bool => $notification->taskTitle === 'Konfirmasi narasumber',
        );

        Queue::assertPushed(
            SendWhatsAppReminderJob::class,
            fn (SendWhatsAppReminderJob $job): bool => $job->userId === $lead->id
                && $job->recipientNumber === '+628155555555'
                && $job->messageType === 'task_deadline_reminder',
        );
    }

    public function test_member_cannot_queue_task_deadline_reminders(): void
    {
        Notification::fake();
        Queue::fake();

        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('notifications.task-deadline-reminders.store'))
            ->assertRedirect()
            ->assertSessionHas('success', '0 reminder deadline task masuk antrean notifikasi.');

        Notification::assertNothingSent();
        Queue::assertNotPushed(SendWhatsAppReminderJob::class);
    }

    public function test_whatsapp_reminder_is_not_queued_when_rule_opts_out(): void
    {
        Notification::fake();
        Queue::fake();

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        DB::table('notification_rules')
            ->where('event', 'task_deadline_reminder')
            ->update(['channels' => json_encode(['in_app', 'email'])]);

        DB::table('project_tasks')
            ->where('title', 'Konfirmasi narasumber')
            ->update(['due_at' => now()->addDay()->toDateString()]);

        $this->actingAs($secretary)
            ->post(route('notifications.task-deadline-reminders.store'))
            ->assertRedirect();

        Queue::assertNotPushed(SendWhatsAppReminderJob::class);
    }

    public function test_whatsapp_reminder_job_logs_successful_provider_delivery(): void
    {
        Http::fake([
            'https://wa.example.test/*' => Http::response(['id' => 'wa_123'], 200),
        ]);

        config([
            'services.whatsapp.url' => 'https://wa.example.test/messages',
            'services.whatsapp.token' => 'fake-token',
            'services.whatsapp.from_number' => '+628100000000',
        ]);

        $lead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();

        (new SendWhatsAppReminderJob(
            organizationId: $this->organizationId('bem-fakultas-teknologi'),
            userId: (int) $lead->id,
            recipientNumber: '+628155555555',
            messageType: 'task_deadline_reminder',
            message: 'Reminder test',
        ))->handle();

        $this->assertDatabaseHas('whatsapp_delivery_logs', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'user_id' => $lead->id,
            'recipient_number' => '+628155555555',
            'message_type' => 'task_deadline_reminder',
            'status' => 'sent',
        ]);
    }

    public function test_whatsapp_reminder_job_logs_failed_provider_delivery(): void
    {
        Http::fake([
            'https://wa.example.test/*' => Http::response(['error' => 'down'], 500),
        ]);

        config([
            'services.whatsapp.url' => 'https://wa.example.test/messages',
            'services.whatsapp.token' => 'fake-token',
            'services.whatsapp.from_number' => '+628100000000',
        ]);

        $this->expectException(RequestException::class);

        try {
            (new SendWhatsAppReminderJob(
                organizationId: $this->organizationId('bem-fakultas-teknologi'),
                userId: null,
                recipientNumber: '+628155555555',
                messageType: 'task_deadline_reminder',
                message: 'Reminder test',
            ))->handle();
        } finally {
            $this->assertDatabaseHas('whatsapp_delivery_logs', [
                'recipient_number' => '+628155555555',
                'message_type' => 'task_deadline_reminder',
                'status' => 'failed',
            ]);
        }
    }

    public function test_whatsapp_notification_channel_queues_provider_job(): void
    {
        Queue::fake();

        $lead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();
        $notification = new TaskDeadlineReminderNotification(
            taskTitle: 'Konfirmasi narasumber',
            projectName: 'Seminar Karier',
            dueAt: '2026-05-20',
        );

        (new WhatsAppNotificationChannel)->send($lead, $notification);

        Queue::assertPushed(
            SendWhatsAppReminderJob::class,
            fn (SendWhatsAppReminderJob $job): bool => $job->recipientNumber === '+628155555555'
                && $job->messageType === 'TaskDeadlineReminderNotification',
        );
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
