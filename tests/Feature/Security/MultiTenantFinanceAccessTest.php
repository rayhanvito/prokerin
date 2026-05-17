<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class MultiTenantFinanceAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_other_organization_owner_does_not_receive_source_org_finance_payload(): void
    {
        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($otherOwner)
            ->get(route('finance.realization'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Finance/Realization')
                ->has('budgetLines', 0)
                ->has('transactions', 0));

        $this->actingAs($otherOwner)
            ->get(route('finance.approval'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Finance/Approval')
                ->has('approvals', 0));
    }

    public function test_member_cannot_open_finance_pages_by_direct_url(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        foreach (['finance.index', 'finance.budget-draft', 'finance.realization', 'finance.approval'] as $routeName) {
            $this->actingAs($member)
                ->get(route($routeName))
                ->assertForbidden();
        }
    }

    public function test_treasurer_can_open_finance_pages(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        foreach (['finance.index', 'finance.budget-draft', 'finance.realization', 'finance.approval'] as $routeName) {
            $this->actingAs($treasurer)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_posted_organization_id_is_ignored_for_cross_tenant_finance_mutation(): void
    {
        Storage::fake('s3');

        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();
        $sourceOrganizationId = (int) DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');
        $sourceBudgetLineId = (int) DB::table('budget_lines')
            ->where('name', 'Publikasi dan printing')
            ->value('id');

        $this->actingAs($otherOwner)
            ->post(route('finance.realizations.store', ['budgetLine' => $sourceBudgetLineId]), [
                'organization_id' => $sourceOrganizationId,
                'name' => 'Cross tenant receipt attempt',
                'amount' => 100000,
                'receipt' => UploadedFile::fake()->create('receipt-cross-tenant.jpg', 128, 'image/jpeg'),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('budget_transactions', [
            'budget_line_id' => $sourceBudgetLineId,
            'name' => 'Cross tenant receipt attempt',
        ]);
    }

    public function test_viewer_is_blocked_from_representative_workspace_mutations(): void
    {
        $viewer = User::query()->where('email', 'viewer@prokerin.test')->firstOrFail();

        $this->actingAs($viewer)
            ->post(route('proker.store'), [
                'name' => 'Viewer Mutation Attempt',
                'template_type' => 'seminar',
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-09-01',
            ])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->patch(route('finance.approvals.update', ['budgetLine' => $this->budgetLineId('Sewa aula dan sound system')]), [
                'decision' => 'approve',
            ])
            ->assertForbidden();
    }

    private function budgetLineId(string $name): int
    {
        return (int) DB::table('budget_lines')->where('name', $name)->value('id');
    }
}
