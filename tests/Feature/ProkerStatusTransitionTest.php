<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ProkerStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_project_detail_includes_next_statuses(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.detail', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Show')
                ->where('nextStatuses.0.value', ProjectStatus::Draft->value)
                ->where('nextStatuses.1.value', ProjectStatus::RabApproval->value));
    }

    public function test_owner_can_transition_project_status(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('proker.status.update', ['project' => 'seminar-karier-digital']), [
                'status' => ProjectStatus::RabApproval->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status proker berhasil diperbarui.');

        $this->assertDatabaseHas('projects', [
            'slug' => 'seminar-karier-digital',
            'status' => ProjectStatus::RabApproval->value,
        ]);
    }

    public function test_invalid_project_status_transition_is_rejected(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('proker.status.update', ['project' => 'seminar-karier-digital']), [
                'status' => ProjectStatus::Completed->value,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('projects', [
            'slug' => 'seminar-karier-digital',
            'status' => ProjectStatus::ProposalReview->value,
        ]);
    }

    public function test_member_cannot_transition_project_status(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('proker.status.update', ['project' => 'seminar-karier-digital']), [
                'status' => ProjectStatus::RabApproval->value,
            ])
            ->assertForbidden();
    }

    public function test_task_status_update_recomputes_project_progress(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')
            ->where('slug', 'seminar-karier-digital')
            ->value('id');

        DB::table('project_tasks')
            ->where('project_id', $projectId)
            ->update([
                'status' => 'todo',
                'completed_at' => null,
            ]);
        DB::table('projects')
            ->where('id', $projectId)
            ->update(['progress' => 0]);

        $taskIds = DB::table('project_tasks')
            ->where('project_id', $projectId)
            ->orderBy('id')
            ->pluck('id');

        $this->actingAs($owner)
            ->patch(route('tasks.status.update', ['task' => (int) $taskIds[0]]), [
                'status' => 'done',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'progress' => 25,
        ]);

        foreach ($taskIds->slice(1) as $taskId) {
            $this->actingAs($owner)
                ->patch(route('tasks.status.update', ['task' => (int) $taskId]), [
                    'status' => 'done',
                ])
                ->assertRedirect();
        }

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'progress' => 100,
        ]);
    }
}
