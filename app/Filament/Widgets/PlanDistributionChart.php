<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PlanDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Plan Distribution';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        return Cache::remember('admin.widgets.plan_distribution', now()->addMinutes(5), function (): array {
            $rows = Organization::query()
                ->select('plan_tier', DB::raw('count(*) as total'))
                ->groupBy('plan_tier')
                ->pluck('total', 'plan_tier')
                ->all();

            return [
                'datasets' => [[
                    'data' => array_values($rows),
                    'backgroundColor' => ['#24695c', '#ba895d', '#1b4c43', '#d22d3d'],
                ]],
                'labels' => array_keys($rows),
            ];
        });
    }
}
