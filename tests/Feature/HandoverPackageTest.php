<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class HandoverPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_handover_page_receives_live_tenant_scoped_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('organization.handover'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Handover')
                ->where('organization.name', 'BEM Fakultas Teknologi')
                ->where('organization.periodName', '2026')
                ->has('metrics', 3)
                ->where('package', null)
                ->where('items', [])
                ->where('canManage', true));
    }

    public function test_owner_can_initiate_handover_package_from_current_snapshot(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organization.handover.store'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Paket handover berhasil disiapkan.');

        $package = DB::table('handover_packages')->first();

        $this->assertNotNull($package);
        $this->assertSame('draft', $package->status);
        $this->assertDatabaseHas('handover_packages', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'from_period_id' => $this->periodId('bem-fakultas-teknologi', '2026'),
            'created_by' => $owner->id,
        ]);
        $this->assertDatabaseCount('handover_items', 4);

        $snapshot = json_decode((string) $package->snapshot, true);

        $this->assertSame(3, $snapshot['documents']);
        $this->assertSame(3, $snapshot['open_tasks']);
        $this->assertArrayHasKey('planned_budget', $snapshot);
        $this->assertArrayHasKey('outstanding_lpj_items', $snapshot);

        $this->actingAs($owner)
            ->get(route('organization.handover'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Handover')
                ->where('package.status', 'draft')
                ->has('items', 4)
                ->where('metrics.2.value', '4'));
    }

    public function test_member_cannot_initiate_handover_package(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('organization.handover.store'))
            ->assertForbidden();

        $this->assertDatabaseCount('handover_packages', 0);
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function periodId(string $organizationSlug, string $periodName): int
    {
        return (int) DB::table('organization_periods')
            ->join('organizations', 'organizations.id', '=', 'organization_periods.organization_id')
            ->where('organizations.slug', $organizationSlug)
            ->where('organization_periods.name', $periodName)
            ->value('organization_periods.id');
    }
}
