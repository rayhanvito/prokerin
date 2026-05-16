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

    public function test_leadership_dashboard_kpis_match_organization_data(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');
        $activeProjects = DB::table('projects')
            ->where('organization_id', $organizationId)
            ->whereNotIn('status', ['completed', 'archived'])
            ->count();
        $members = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->count();

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/Index')
                ->where('payload.kpiMetrics.0.label', 'Proker Aktif')
                ->where('payload.kpiMetrics.0.value', $activeProjects)
                ->where('payload.kpiMetrics.2.label', 'Total Anggota')
                ->where('payload.kpiMetrics.2.value', $members));
    }

    public function test_treasurer_dashboard_remaining_budget_matches_database_totals(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');
        $rabTotal = (int) DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->sum('budget_lines.planned_amount');
        $realizedTotal = (int) DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->sum('budget_lines.realized_amount');
        $remainingBudget = 'Rp '.number_format(max(0, $rabTotal - $realizedTotal), 0, ',', '.');

        $this->actingAs($treasurer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard/Index')
                ->where('dashboardVariant', 'bendahara')
                ->where('payload.kpiMetrics.2.label', 'Sisa Anggaran')
                ->where('payload.kpiMetrics.2.value', $remainingBudget));
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

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
