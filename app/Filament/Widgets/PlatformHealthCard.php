<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Actions\Admin\GetPlatformHealthAction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

final class PlatformHealthCard extends BaseWidget
{
    protected ?string $heading = 'Platform Health';

    protected function getStats(): array
    {
        $health = Cache::remember(
            'admin.widgets.platform_health',
            now()->addSeconds(30),
            static fn (): array => app(GetPlatformHealthAction::class)->execute(),
        );

        return collect($health)
            ->map(static fn (array $item, string $key): Stat => Stat::make(ucfirst($key), $item['status'])
                ->description($item['detail'])
                ->color(match ($item['status']) {
                    'up' => 'success',
                    'warning', 'unknown' => 'warning',
                    default => 'danger',
                }))
            ->values()
            ->all();
    }
}
