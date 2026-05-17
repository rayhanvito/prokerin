<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Actions\Admin\GetPlatformHealthAction;
use App\Filament\Pages\PrkAdminDashboard;
use App\Filament\Widgets\ActiveProkerByPhase;
use App\Filament\Widgets\EngagedOrganizationsTable;
use App\Filament\Widgets\FailedJobsCounter;
use App\Filament\Widgets\OrganizationGrowthChart;
use App\Filament\Widgets\PlanDistributionChart;
use App\Filament\Widgets\PlatformHealthCard;
use App\Filament\Widgets\PlatformStatsOverview;
use App\Filament\Widgets\RecentOrganizationsTable;
use App\Filament\Widgets\RecentUsersTable;
use App\Filament\Widgets\UserGrowthChart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_dashboard_loads(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin')
            ->assertOk()
            ->assertSee('Prokerin Operations');
    }

    public function test_dashboard_registers_v2_widgets(): void
    {
        $widgets = (new PrkAdminDashboard)->getWidgets();

        $this->assertSame([
            PlatformStatsOverview::class,
            FailedJobsCounter::class,
            UserGrowthChart::class,
            OrganizationGrowthChart::class,
            PlanDistributionChart::class,
            ActiveProkerByPhase::class,
            EngagedOrganizationsTable::class,
            RecentOrganizationsTable::class,
            RecentUsersTable::class,
            PlatformHealthCard::class,
        ], $widgets);
    }

    public function test_platform_health_action_reports_core_services(): void
    {
        $health = app(GetPlatformHealthAction::class)->execute();

        $this->assertArrayHasKey('database', $health);
        $this->assertArrayHasKey('queue', $health);
        $this->assertArrayHasKey('storage', $health);
        $this->assertArrayHasKey('mail', $health);
        $this->assertSame('up', $health['database']['status']);
    }
}
