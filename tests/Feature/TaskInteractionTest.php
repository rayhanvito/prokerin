<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Task\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class TaskInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_kanban_page_receives_database_backed_columns(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('tasks.kanban'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Kanban')
                ->has('columns', 6)
                ->where('columns.0.status', 'backlog')
                ->where('columns.0.tasks.0.title', 'Konfirmasi narasumber')
                ->where('columns.3.status', 'review')
                ->where('columns.3.tasks.0.title', 'Finalisasi proposal'));
    }

    public function test_calendar_page_receives_database_backed_deadlines(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('tasks.calendar'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Calendar')
                ->has('days', 4)
                ->where('days.0.title', 'Finalisasi proposal')
                ->where('days.0.date', '22')
                ->where('days.0.project', 'Seminar Karier Digital'));
    }

    public function test_owner_can_update_task_status_from_kanban(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $taskId = $this->taskId('Konfirmasi narasumber');

        $this->actingAs($owner)
            ->patch(route('tasks.status.update', ['task' => $taskId]), [
                'status' => TaskStatus::Done->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status task berhasil diperbarui.');

        $task = DB::table('project_tasks')->where('id', $taskId)->first();

        $this->assertSame(TaskStatus::Done->value, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_member_cannot_update_task_status(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('tasks.status.update', ['task' => $this->taskId('Konfirmasi narasumber')]), [
                'status' => TaskStatus::Review->value,
            ])
            ->assertForbidden();
    }

    private function taskId(string $title): int
    {
        return (int) DB::table('project_tasks')
            ->where('title', $title)
            ->value('id');
    }
}
