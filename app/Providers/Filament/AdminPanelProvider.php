<?php

declare(strict_types=1);

namespace App\Providers\Filament;

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
use App\Http\Middleware\EnsureAdminIpAllowed;
use App\Http\Middleware\EnsureAdminSessionFresh;
use App\Http\Middleware\SetAdminSecurityHeaders;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path((string) config('prokerin.filament.admin_path', 'internal-admin'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName((string) config('prokerin.filament.brand_name', 'Prokerin Admin'))
            ->font('Plus Jakarta Sans')
            ->sidebarWidth('255px')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch()
            ->navigationGroups([
                NavigationGroup::make('Platform')->icon('heroicon-o-globe-asia-australia'),
                NavigationGroup::make('Operations')->icon('heroicon-o-rectangle-group'),
                NavigationGroup::make('Configuration')->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make('Insights')->icon('heroicon-o-chart-bar-square'),
            ])
            ->colors([
                'primary' => Color::hex('#24695c'),
                'success' => Color::hex('#1b4c43'),
                'danger' => Color::hex('#d22d3d'),
                'warning' => Color::hex('#ba895d'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                PrkAdminDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
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
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                EnsureAdminIpAllowed::class,
                EnsureAdminSessionFresh::class,
                SetAdminSecurityHeaders::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
