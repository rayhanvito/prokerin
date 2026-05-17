<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

final class UserGrowthChart extends ChartWidget
{
    protected ?string $heading = 'User Growth';

    protected ?string $description = '30 hari terakhir';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return Cache::remember('admin.widgets.user_growth.30d', now()->addMinutes(5), function (): array {
            $labels = [];
            $values = [];

            foreach (range(29, 0) as $offset) {
                $date = now()->subDays($offset)->toDateString();
                $labels[] = $date;
                $values[] = User::query()->whereDate('created_at', '<=', $date)->count();
            }

            return [
                'datasets' => [[
                    'label' => 'Users',
                    'data' => $values,
                    'borderColor' => '#24695c',
                    'backgroundColor' => 'rgba(36, 105, 92, 0.12)',
                ]],
                'labels' => $labels,
            ];
        });
    }
}
