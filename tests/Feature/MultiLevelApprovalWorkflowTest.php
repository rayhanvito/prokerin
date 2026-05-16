<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Approval\DelegateApprovalStepAction;
use App\Actions\Approval\ProcessApprovalStepAction;
use App\Actions\Approval\StartApprovalWorkflowAction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MultiLevelApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_full_workflow_executes_steps_in_order_until_approved(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $instanceId = $this->startWorkflow([$treasurer->id, $owner->id], $secretary->id);

        app(ProcessApprovalStepAction::class)->execute($treasurer->id, $instanceId, 'approved', 'Bendahara ok.');

        $this->assertDatabaseHas('approval_instances', [
            'id' => $instanceId,
            'status' => 'pending',
            'current_step' => 2,
        ]);

        app(ProcessApprovalStepAction::class)->execute($owner->id, $instanceId, 'approved', 'Ketua ok.');

        $this->assertDatabaseHas('approval_instances', [
            'id' => $instanceId,
            'status' => 'approved',
            'current_step' => 2,
        ]);
    }

    public function test_rejection_at_second_step_terminates_workflow(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $instanceId = $this->startWorkflow([$treasurer->id, $owner->id], $treasurer->id);

        app(ProcessApprovalStepAction::class)->execute($treasurer->id, $instanceId, 'approved');
        app(ProcessApprovalStepAction::class)->execute($owner->id, $instanceId, 'rejected', 'Nominal perlu revisi.');

        $this->assertDatabaseHas('approval_instances', [
            'id' => $instanceId,
            'status' => 'rejected',
            'current_step' => 2,
        ]);
        $this->assertDatabaseHas('approval_step_records', [
            'instance_id' => $instanceId,
            'step_order' => 2,
            'decision' => 'rejected',
        ]);
    }

    public function test_revision_request_sends_subject_back_to_submitter_state(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $instanceId = $this->startWorkflow([$treasurer->id], $treasurer->id);

        app(ProcessApprovalStepAction::class)->execute($treasurer->id, $instanceId, 'revision_requested', 'Lengkapi lampiran.');

        $this->assertDatabaseHas('approval_instances', [
            'id' => $instanceId,
            'status' => 'revision_requested',
        ]);
    }

    public function test_cross_tenant_user_cannot_approve_another_organization_workflow(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('hima-informatika');
        $this->definition($organizationId, [$admin->id], 'proposal');

        $instance = app(StartApprovalWorkflowAction::class)->execute(
            organizationId: $organizationId,
            workflowType: 'proposal',
            subjectType: 'proposal_draft',
            subjectId: 999,
            submittedByUserId: $admin->id,
        );

        $this->expectException(AuthorizationException::class);

        app(ProcessApprovalStepAction::class)->execute($owner->id, $instance['id'], 'approved');
    }

    public function test_delegate_reassignment_is_logged(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $instanceId = $this->startWorkflow([$treasurer->id], $treasurer->id);

        app(DelegateApprovalStepAction::class)->execute($treasurer->id, $instanceId, $admin->id, 'Admin bantu review.');

        $this->assertDatabaseHas('approval_step_records', [
            'instance_id' => $instanceId,
            'step_order' => 1,
            'approver_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('approval_delegations', [
            'delegated_from_user_id' => $treasurer->id,
            'delegated_to_user_id' => $admin->id,
            'note' => 'Admin bantu review.',
        ]);
    }

    /**
     * @param  array<int, int>  $approverIds
     */
    private function startWorkflow(array $approverIds, int $submittedByUserId): int
    {
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        $this->definition($organizationId, $approverIds, 'rab');

        $instance = app(StartApprovalWorkflowAction::class)->execute(
            organizationId: $organizationId,
            workflowType: 'rab',
            subjectType: 'budget_line',
            subjectId: $this->budgetLineId(),
            submittedByUserId: $submittedByUserId,
        );

        return $instance['id'];
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

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function budgetLineId(): int
    {
        return (int) DB::table('budget_lines')->where('name', 'Sewa aula dan sound system')->value('id');
    }
}
