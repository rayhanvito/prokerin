<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class DashboardVariantRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_dashboard_renders_expected_variant_for_seeded_roles(): void
    {
        $this->assertDashboardVariant('owner@prokerin.test', 'pimpinan');
        $this->assertDashboardVariant('admin@prokerin.test', 'pimpinan');
        $this->assertDashboardVariant('sekretaris@prokerin.test', 'sekretaris');
        $this->assertDashboardVariant('bendahara@prokerin.test', 'bendahara');
        $this->assertDashboardVariant('lead@prokerin.test', 'operasional');
        $this->assertDashboardVariant('koordinator@prokerin.test', 'operasional');
        $this->assertDashboardVariant('member@prokerin.test', 'member');
        $this->assertDashboardVariant('viewer@prokerin.test', 'viewer');
    }

    public function test_dashboard_payload_is_scoped_to_active_user_organization(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $externalOrganizationId = (int) DB::table('organizations')->insertGetId([
            'name' => 'External Org',
            'slug' => 'external-org',
            'logo_path' => null,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->insert([
            'organization_id' => $externalOrganizationId,
            'organization_period_id' => null,
            'project_template_id' => null,
            'project_lead_id' => null,
            'name' => 'External Project',
            'slug' => 'external-project',
            'description' => 'Should not leak.',
            'status' => 'draft',
            'progress' => 90,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-02',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/Index')
                ->where('dashboardVariant', 'pimpinan')
                ->where('payload.priorityProjects.0.name', 'Seminar Karier Digital')
                ->missing('payload.priorityProjects.1'));
    }

    private function assertDashboardVariant(string $email, string $variant): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/Index')
                ->where('dashboardVariant', $variant)
                ->has('payload.kpiMetrics'));
    }
}
