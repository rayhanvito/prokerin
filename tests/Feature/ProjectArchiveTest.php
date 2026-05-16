<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProjectArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_archive_project_in_their_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId('seminar-karier-digital');

        $this->actingAs($owner)
            ->delete(route('proker.destroy', ['project' => 'seminar-karier-digital']))
            ->assertRedirect(route('proker.index', absolute: false))
            ->assertSessionHas('success', 'Proker berhasil diarsipkan.');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'status' => ProjectStatus::Archived->value,
        ]);

        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $projectId,
            'title' => 'Finalisasi proposal',
        ]);
    }

    public function test_secretary_cannot_archive_project(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $this->actingAs($secretary)
            ->delete(route('proker.destroy', ['project' => 'seminar-karier-digital']))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'slug' => 'seminar-karier-digital',
            'status' => ProjectStatus::ProposalReview->value,
        ]);
    }

    public function test_user_cannot_archive_project_outside_their_organizations(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('proker.destroy', ['project' => 'workshop-ui-ux-hmif']))
            ->assertForbidden();
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')
            ->where('slug', $slug)
            ->value('id');
    }
}
