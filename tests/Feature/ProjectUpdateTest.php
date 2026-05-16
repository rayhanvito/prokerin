<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectTemplateType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProjectUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_update_project_in_their_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('proker.update', ['project' => 'seminar-karier-digital']), [
                'name' => 'Seminar Karier Digital Nasional',
                'description' => 'Versi nasional untuk persiapan karier digital.',
                'template_type' => ProjectTemplateType::Seminar->value,
                'starts_at' => '2026-10-01',
                'ends_at' => '2026-10-02',
                'project_lead_id' => $secretary->id,
            ])
            ->assertRedirect(route('proker.detail', ['project' => 'seminar-karier-digital-nasional'], absolute: false))
            ->assertSessionHas('success', 'Data proker berhasil diperbarui.');

        $projectId = (int) DB::table('projects')
            ->where('slug', 'seminar-karier-digital-nasional')
            ->value('id');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'project_lead_id' => $secretary->id,
            'name' => 'Seminar Karier Digital Nasional',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-02',
        ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $projectId,
            'user_id' => $secretary->id,
            'role' => ProjectRole::ProjectLead->value,
        ]);
    }

    public function test_member_cannot_update_project(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('proker.update', ['project' => 'seminar-karier-digital']), [
                'name' => 'Seminar Edit Member',
                'template_type' => ProjectTemplateType::Seminar->value,
                'starts_at' => '2026-10-01',
                'ends_at' => '2026-10-02',
            ])
            ->assertForbidden();
    }

    public function test_project_lead_must_belong_to_project_organization_when_updating(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $externalUser = User::factory()->create();

        $this->actingAs($owner)
            ->patch(route('proker.update', ['project' => 'seminar-karier-digital']), [
                'name' => 'Seminar Karier Digital',
                'template_type' => ProjectTemplateType::Seminar->value,
                'starts_at' => '2026-10-01',
                'ends_at' => '2026-10-02',
                'project_lead_id' => $externalUser->id,
            ])
            ->assertSessionHasErrors('project_lead_id');
    }

    public function test_user_cannot_update_project_outside_their_organizations(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('proker.update', ['project' => 'workshop-ui-ux-hmif']), [
                'name' => 'Workshop UI/UX Edited',
                'template_type' => ProjectTemplateType::Workshop->value,
                'starts_at' => '2026-10-01',
                'ends_at' => '2026-10-02',
            ])
            ->assertForbidden();
    }
}
