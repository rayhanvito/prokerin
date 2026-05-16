<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class BudgetApprovalDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_finance_approval_page_receives_database_backed_queue(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->get(route('finance.approval'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Finance/Approval')
                ->has('approvals', 2)
                ->where('approvals.0.title', 'Sewa aula dan sound system')
                ->where('approvals.0.status', 'review')
                ->where('approvals.0.canDecide', true));
    }

    public function test_treasurer_can_approve_review_budget_line(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $budgetLineId = $this->budgetLineId('Sewa aula dan sound system');

        $this->actingAs($treasurer)
            ->patch(route('finance.approvals.update', ['budgetLine' => $budgetLineId]), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'RAB disetujui dan siap direalisasikan.');

        $this->assertDatabaseHas('budget_lines', [
            'id' => $budgetLineId,
            'status' => 'approved',
        ]);
    }

    public function test_budget_decision_route_processes_active_multi_level_workflow(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $budgetLineId = $this->budgetLineId('Sewa aula dan sound system');
        $organizationId = (int) DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('budget_lines.id', $budgetLineId)
            ->value('projects.organization_id');
        $this->definition($organizationId, [$treasurer->id], 'rab');

        $this->actingAs($treasurer)
            ->patch(route('finance.approvals.update', ['budgetLine' => $budgetLineId]), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'RAB disetujui dan siap direalisasikan.');

        $this->assertDatabaseHas('approval_instances', [
            'subject_type' => 'budget_line',
            'subject_id' => $budgetLineId,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('budget_lines', [
            'id' => $budgetLineId,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_review_budget_line(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $budgetLineId = $this->budgetLineId('Sewa aula dan sound system');

        $this->actingAs($admin)
            ->patch(route('finance.approvals.update', ['budgetLine' => $budgetLineId]), [
                'decision' => 'reject',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'RAB ditolak dan dikembalikan untuk revisi.');

        $this->assertDatabaseHas('budget_lines', [
            'id' => $budgetLineId,
            'status' => 'rejected',
        ]);
    }

    public function test_member_cannot_decide_budget_approval(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('finance.approvals.update', ['budgetLine' => $this->budgetLineId('Sewa aula dan sound system')]), [
                'decision' => 'approve',
            ])
            ->assertForbidden();
    }

    public function test_approved_budget_line_cannot_be_decided_again(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->patch(route('finance.approvals.update', ['budgetLine' => $this->budgetLineId('Publikasi dan printing')]), [
                'decision' => 'reject',
            ])
            ->assertSessionHasErrors('budget_line');
    }

    private function budgetLineId(string $name): int
    {
        return (int) DB::table('budget_lines')->where('name', $name)->value('id');
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
}
