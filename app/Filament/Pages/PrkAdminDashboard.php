<?php

declare(strict_types=1);

namespace App\Filament\Pages;

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
use Filament\Pages\Dashboard;

final class PrkAdminDashboard extends Dashboard
{
    protected static ?string $title = 'Prokerin Operations';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
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
        ];
    }
}
