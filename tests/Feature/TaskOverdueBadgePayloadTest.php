<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Task\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class TaskOverdueBadgePayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-05-17 10:00:00');
        $this->seed();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_kanban_payload_marks_overdue_open_tasks(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('project_tasks')->insert([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'pic_user_id' => $this->userId('lead@prokerin.test'),
            'title' => 'Deadline kemarin',
            'division' => 'Acara',
            'status' => TaskStatus::Backlog->value,
            'due_at' => '2026-05-16',
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('tasks.kanban'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Kanban')
                ->where('columns.0.tasks.0.title', 'Deadline kemarin')
                ->where('columns.0.tasks.0.isOverdue', true)
                ->has('projects', 1));
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function userId(string $email): int
    {
        return (int) DB::table('users')->where('email', $email)->value('id');
    }
}
