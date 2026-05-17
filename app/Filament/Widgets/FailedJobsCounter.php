<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\FailedJob;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class FailedJobsCounter extends BaseWidget
{
    protected ?string $heading = 'Queue Health';

    protected function getStats(): array
    {
        $failedThisWeek = FailedJob::query()
            ->where('failed_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Failed Jobs This Week', (string) $failedThisWeek)
                ->description('Open Failed Jobs resource to inspect and retry')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($failedThisWeek > 0 ? 'danger' : 'success'),
        ];
    }
}
