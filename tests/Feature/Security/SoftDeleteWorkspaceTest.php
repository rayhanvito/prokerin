<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Organization;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SoftDeleteWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_organization_soft_delete_keeps_record_recoverable(): void
    {
        $organization = Organization::query()->where('slug', 'bem-fakultas-teknologi')->firstOrFail();

        $organization->delete();

        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);

        $organization->restore();

        $this->assertNotSoftDeleted('organizations', ['id' => $organization->id]);
    }

    public function test_project_soft_delete_keeps_record_recoverable(): void
    {
        $project = Project::query()->where('slug', 'seminar-karier-digital')->firstOrFail();

        $project->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        $project->restore();

        $this->assertNotSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_default_query_excludes_soft_deleted_records(): void
    {
        $project = Project::query()->where('slug', 'seminar-karier-digital')->firstOrFail();
        $project->delete();

        $this->assertNull(Project::query()->find($project->id));
        $this->assertNotNull(Project::withTrashed()->find($project->id));
    }
}
