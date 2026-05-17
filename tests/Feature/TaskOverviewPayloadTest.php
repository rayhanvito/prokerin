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

final class TaskOverviewPayloadTest extends TestCase
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

    public function test_task_overview_uses_database_backed_metrics_and_urgent_tasks(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('project_tasks')->insert([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'pic_user_id' => $this->userId('lead@prokerin.test'),
            'title' => 'Follow up sponsor telat',
            'division' => 'Sponsorship',
            'status' => TaskStatus::InProgress->value,
            'due_at' => '2026-05-16',
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Index')
                ->where('metrics.0.value', '4')
                ->where('metrics.1.value', '1')
                ->where('urgentTasks.0.title', 'Follow up sponsor telat')
                ->where('urgentTasks.0.isOverdue', true)
                ->has('projects', 1));
    }

    public function test_task_overview_respects_active_organization_scope(): void
    {
        $owner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Index')
                ->where('metrics.0.value', '0')
                ->has('urgentTasks', 0)
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
