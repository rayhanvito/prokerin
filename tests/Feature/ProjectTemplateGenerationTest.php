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

final class ProjectTemplateGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_generate_project_scaffold_from_template(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('proker.templates.generate', ['template' => ProjectTemplateType::Workshop->value]), [
                'name' => 'Workshop Product Design',
                'description' => 'Workshop praktik product design untuk anggota baru.',
                'starts_at' => '2026-11-10',
                'ends_at' => '2026-11-11',
                'target_audience' => 'Anggota baru dan pengurus bidang produk.',
            ])
            ->assertRedirect(route('proker.detail', ['project' => 'workshop-product-design'], absolute: false))
            ->assertSessionHas('success', 'Draft proker berhasil dibuat dari template.');

        $projectId = (int) DB::table('projects')
            ->where('slug', 'workshop-product-design')
            ->value('id');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'project_lead_id' => $owner->id,
            'status' => ProjectStatus::Draft->value,
            'starts_at' => '2026-11-10',
            'ends_at' => '2026-11-11',
        ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $projectId,
            'user_id' => $owner->id,
            'role' => ProjectRole::ProjectLead->value,
        ]);

        $this->assertSame(4, DB::table('project_tasks')->where('project_id', $projectId)->count());
        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $projectId,
            'title' => 'Susun kurikulum workshop',
            'due_at' => '2026-10-13',
        ]);

        $this->assertSame(3, DB::table('budget_lines')->where('project_id', $projectId)->count());
        $this->assertDatabaseHas('budget_lines', [
            'project_id' => $projectId,
            'name' => 'Honor mentor',
            'planned_amount' => 2500000,
            'status' => 'draft',
        ]);

        $this->assertSame(4, DB::table('lpj_checklist_items')->where('project_id', $projectId)->count());
        $this->assertDatabaseHas('lpj_checklist_items', [
            'project_id' => $projectId,
            'title' => 'Rekap kehadiran peserta',
            'is_required' => true,
            'is_complete' => false,
        ]);

        $this->assertDatabaseHas('proposal_drafts', [
            'project_id' => $projectId,
            'title' => 'Proposal Workshop Product Design',
            'status' => 'draft',
        ]);
    }

    public function test_member_cannot_generate_project_from_template(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('proker.templates.generate', ['template' => ProjectTemplateType::Seminar->value]), [
                'name' => 'Seminar Anggota',
            ])
            ->assertForbidden();
    }

    public function test_template_generation_project_lead_must_belong_to_active_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $externalUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('proker.templates.generate', ['template' => ProjectTemplateType::Makrab->value]), [
                'name' => 'Makrab Kabinet',
                'starts_at' => '2026-12-01',
                'ends_at' => '2026-12-02',
                'project_lead_id' => $externalUser->id,
            ])
            ->assertSessionHasErrors('project_lead_id');
    }
}
