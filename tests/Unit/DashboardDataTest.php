<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Dashboard\DashboardTone;
use App\DTOs\Dashboard\MetricCardData;
use App\DTOs\Dashboard\PriorityItemData;
use PHPUnit\Framework\TestCase;

final class DashboardDataTest extends TestCase
{
    public function test_metric_card_data_can_be_serialized_for_inertia(): void
    {
        $data = new MetricCardData(
            'Open Task',
            '86',
            '11 deadline minggu ini',
            DashboardTone::Warning,
        );

        $this->assertSame([
            'label' => 'Open Task',
            'value' => '86',
            'note' => '11 deadline minggu ini',
            'tone' => 'warning',
        ], $data->toArray());
    }

    public function test_priority_item_data_can_include_optional_href(): void
    {
        $data = new PriorityItemData(
            title: 'Finalisasi proposal',
            meta: 'Sekretaris',
            status: 'Review',
            progress: 72,
            href: '/proker/sample',
        );

        $this->assertSame('/proker/sample', $data->toArray()['href']);
    }
}
