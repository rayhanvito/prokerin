<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Task\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_quick_add_task(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId('seminar-karier-digital');

        $this->actingAs($owner)
            ->post(route('tasks.store'), [
                'project_id' => $projectId,
                'title' => 'Brief panitia registrasi',
                'due_at' => '2026-05-28',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Task berhasil dibuat.');

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $projectId,
            'title' => 'Brief panitia registrasi',
            'status' => TaskStatus::Backlog->value,
            'due_at' => '2026-05-28',
            'pic_user_id' => null,
        ]);
    }

    public function test_project_lead_can_quick_add_task_to_their_project(): void
    {
        $lead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();
        $projectId = $this->projectId('seminar-karier-digital');

        $this->actingAs($lead)
            ->post(route('tasks.store'), [
                'project_id' => $projectId,
                'title' => 'Cek kesiapan venue',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $projectId,
            'title' => 'Cek kesiapan venue',
            'status' => TaskStatus::Backlog->value,
        ]);
    }

    public function test_regular_member_cannot_quick_add_task(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('tasks.store'), [
                'project_id' => $this->projectId('seminar-karier-digital'),
                'title' => 'Task liar',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('project_tasks', [
            'title' => 'Task liar',
        ]);
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }
}
