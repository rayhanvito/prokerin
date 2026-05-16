<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class LpjApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_submit_complete_lpj_for_review(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();
        $this->completeChecklist($projectId);

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::Running->value]);

        $this->actingAs($secretary)
            ->post(route('reports.lpj.review', ['project' => $projectId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'LPJ berhasil dikirim ke review.');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'status' => ProjectStatus::LpjReview->value,
        ]);
    }

    public function test_incomplete_lpj_cannot_be_submitted_for_review(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::Running->value]);

        $this->actingAs($secretary)
            ->post(route('reports.lpj.review', ['project' => $projectId]))
            ->assertSessionHasErrors('lpj');
    }

    public function test_owner_can_approve_lpj_review(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::LpjReview->value]);

        $this->actingAs($owner)
            ->patch(route('reports.lpj.decision', ['project' => $projectId]), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'LPJ disetujui dan proker selesai.');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'status' => ProjectStatus::Completed->value,
        ]);
    }

    public function test_owner_can_request_lpj_revision(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::LpjReview->value]);

        $this->actingAs($owner)
            ->patch(route('reports.lpj.decision', ['project' => $projectId]), [
                'decision' => 'request_changes',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'LPJ dikembalikan untuk revisi.');

        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'status' => ProjectStatus::Running->value,
        ]);
    }

    public function test_secretary_cannot_decide_lpj_review(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::LpjReview->value]);

        $this->actingAs($secretary)
            ->patch(route('reports.lpj.decision', ['project' => $projectId]), [
                'decision' => 'approve',
            ])
            ->assertForbidden();
    }

    private function completeChecklist(int $projectId): void
    {
        DB::table('lpj_checklist_items')
            ->where('project_id', $projectId)
            ->update(['is_complete' => true]);
    }

    private function projectId(): int
    {
        return (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');
    }
}
