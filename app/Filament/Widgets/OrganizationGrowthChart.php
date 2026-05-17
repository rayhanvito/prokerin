<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

final class OrganizationGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Organization Growth';

    protected ?string $description = '30 hari terakhir';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return Cache::remember('admin.widgets.organization_growth.30d', now()->addMinutes(5), function (): array {
            $labels = [];
            $values = [];

            foreach (range(29, 0) as $offset) {
                $date = now()->subDays($offset)->toDateString();
                $labels[] = $date;
                $values[] = Organization::query()->whereDate('created_at', '<=', $date)->count();
            }

            return [
                'datasets' => [[
                    'label' => 'Organizations',
                    'data' => $values,
                    'borderColor' => '#ba895d',
                    'backgroundColor' => 'rgba(186, 137, 93, 0.14)',
                ]],
                'labels' => $labels,
            ];
        });
    }
}
