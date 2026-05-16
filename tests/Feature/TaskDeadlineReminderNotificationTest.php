<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
    }

    public function test_member_cannot_queue_task_deadline_reminders(): void
    {
        Notification::fake();

        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('notifications.task-deadline-reminders.store'))
            ->assertRedirect()
            ->assertSessionHas('success', '0 reminder deadline task masuk antrean notifikasi.');

        Notification::assertNothingSent();
    }
}
