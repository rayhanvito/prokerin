<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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
        Queue::fake();

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
        Queue::assertPushed(
            SendWhatsAppReminderJob::class,
            fn (SendWhatsAppReminderJob $job): bool => $job->messageType === 'lpj_review_requested',
        );
    }

    public function test_lpj_submission_starts_active_multi_level_workflow(): void
    {
        Queue::fake();

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();
        $organizationId = (int) DB::table('projects')->where('id', $projectId)->value('organization_id');
        $this->completeChecklist($projectId);
        $this->definition($organizationId, [$owner->id], 'lpj');

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::Running->value]);

        $this->actingAs($secretary)
            ->post(route('reports.lpj.review', ['project' => $projectId]))
            ->assertRedirect();

        $this->assertDatabaseHas('approval_instances', [
            'subject_type' => 'project',
            'subject_id' => $projectId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('approval_step_records', [
            'step_order' => 1,
            'approver_id' => $owner->id,
            'decision' => 'pending',
        ]);
    }

    public function test_lpj_decision_route_processes_active_workflow_before_syncing_subject(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();
        $organizationId = (int) DB::table('projects')->where('id', $projectId)->value('organization_id');
        $definitionId = $this->definition($organizationId, [$owner->id], 'lpj');
        $instanceId = $this->workflowInstance($definitionId, 'project', $projectId, $owner->id);

        DB::table('projects')->where('id', $projectId)->update(['status' => ProjectStatus::LpjReview->value]);

        $this->actingAs($owner)
            ->patch(route('reports.lpj.decision', ['project' => $projectId]), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'LPJ disetujui dan proker selesai.');

        $this->assertDatabaseHas('approval_instances', [
            'id' => $instanceId,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'status' => ProjectStatus::Completed->value,
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

    /**
     * @param  array<int, int>  $approverIds
     */
    private function definition(int $organizationId, array $approverIds, string $workflowType): int
    {
        return (int) DB::table('approval_workflow_definitions')->insertGetId([
            'organization_id' => $organizationId,
            'workflow_type' => $workflowType,
            'steps' => json_encode(array_map(
                static fn (int $approverId, int $index): array => [
                    'step_order' => $index + 1,
                    'approver_id' => $approverId,
                ],
                $approverIds,
                array_keys($approverIds),
            )),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function workflowInstance(int $definitionId, string $subjectType, int $subjectId, int $submittedByUserId): int
    {
        $instanceId = (int) DB::table('approval_instances')->insertGetId([
            'workflow_definition_id' => $definitionId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'status' => 'pending',
            'current_step' => 1,
            'submitted_by_user_id' => $submittedByUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $steps = json_decode((string) DB::table('approval_workflow_definitions')->where('id', $definitionId)->value('steps'), true);

        foreach ($steps as $step) {
            DB::table('approval_step_records')->insert([
                'instance_id' => $instanceId,
                'step_order' => (int) $step['step_order'],
                'approver_id' => (int) $step['approver_id'],
                'decision' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $instanceId;
    }
}
