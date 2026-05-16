<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Organization\Enums\PlanTier;
use App\Models\Organization;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PlatformStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Platform Stats';

    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $usersThisWeek = User::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $totalOrganizations = Organization::query()->count();
        $organizationsThisWeek = Organization::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $activeProjects = DB::table('projects')
            ->whereNotIn('status', ['archived', 'completed'])
            ->count();

        $planBreakdown = Organization::query()
            ->select('plan_tier', DB::raw('count(*) as total'))
            ->groupBy('plan_tier')
            ->pluck('total', 'plan_tier')
            ->all();

        $planSummary = collect(PlanTier::cases())
            ->map(fn (PlanTier $tier): string => sprintf('%s: %d', $tier->label(), (int) ($planBreakdown[$tier->value] ?? 0)))
            ->implode(' · ');

        return [
            Stat::make('Total Users', (string) $totalUsers)
                ->description(sprintf('+%d this week', $usersThisWeek))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Organizations', (string) $totalOrganizations)
                ->description(sprintf('+%d this week', $organizationsThisWeek))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Active Projects', (string) $activeProjects)
                ->description('across all organizations')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),

            Stat::make('Plans Breakdown', $planSummary === '' ? 'No data' : $planSummary)
                ->description('organizations per plan tier')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('warning'),
        ];
    }
}
