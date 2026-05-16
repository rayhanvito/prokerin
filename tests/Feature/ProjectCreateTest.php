<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ProjectTemplateType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProjectCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_create_project_draft_for_active_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId();

        $this->actingAs($owner)
            ->post(route('proker.store'), [
                'name' => 'Seminar Karier Digital',
                'description' => 'Diskusi karier lanjutan bersama alumni.',
                'template_type' => ProjectTemplateType::Seminar->value,
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-09-01',
                'project_lead_id' => $admin->id,
            ])
            ->assertRedirect(route('proker.detail', ['project' => 'seminar-karier-digital-2'], absolute: false))
            ->assertSessionHas('success', 'Draft proker berhasil dibuat.');

        $this->assertDatabaseHas('projects', [
            'organization_id' => $organizationId,
            'project_lead_id' => $admin->id,
            'name' => 'Seminar Karier Digital',
            'slug' => 'seminar-karier-digital-2',
            'status' => ProjectStatus::Draft->value,
            'progress' => 0,
        ]);

        $projectId = (int) DB::table('projects')
            ->where('organization_id', $organizationId)
            ->where('slug', 'seminar-karier-digital-2')
            ->value('id');

        $this->assertDatabaseHas('project_members', [
            'project_id' => $projectId,
            'user_id' => $admin->id,
            'role' => ProjectRole::ProjectLead->value,
        ]);
    }

    public function test_member_cannot_create_project_draft(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('proker.store'), [
                'name' => 'Kajian Anggota',
                'template_type' => ProjectTemplateType::Workshop->value,
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-09-01',
            ])
            ->assertForbidden();
    }

    public function test_project_lead_must_belong_to_active_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $externalUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('proker.store'), [
                'name' => 'Kolaborasi Kampus',
                'template_type' => ProjectTemplateType::Workshop->value,
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-09-02',
                'project_lead_id' => $externalUser->id,
            ])
            ->assertSessionHasErrors('project_lead_id');
    }

    public function test_project_end_date_cannot_be_before_start_date(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('proker.store'), [
                'name' => 'Workshop Singkat',
                'template_type' => ProjectTemplateType::Workshop->value,
                'starts_at' => '2026-09-03',
                'ends_at' => '2026-09-01',
            ])
            ->assertSessionHasErrors('ends_at');
    }

    private function organizationId(): int
    {
        return (int) DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');
    }
}
