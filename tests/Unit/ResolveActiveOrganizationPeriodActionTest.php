<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Organization\ResolveActiveOrganizationPeriodAction;
use App\DTOs\Organization\OrganizationPeriodData;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ResolveActiveOrganizationPeriodActionTest extends TestCase
{
    public function test_it_resolves_active_period_for_given_date(): void
    {
        $period = (new ResolveActiveOrganizationPeriodAction)->execute(
            [
                $this->period('2025', '2025-01-01', '2025-12-31'),
                $this->period('2026', '2026-01-01', '2026-12-31'),
            ],
            new DateTimeImmutable('2026-05-16'),
        );

        $this->assertNotNull($period);
        $this->assertSame('2026', $period->name);
    }

    public function test_it_prefers_latest_start_when_periods_overlap(): void
    {
        $period = (new ResolveActiveOrganizationPeriodAction)->execute(
            [
                $this->period('2026', '2026-01-01', '2026-12-31'),
                $this->period('2025/2026', '2025-08-01', '2026-07-31'),
            ],
            new DateTimeImmutable('2026-05-16'),
        );

        $this->assertNotNull($period);
        $this->assertSame('2026', $period->name);
    }

    public function test_it_returns_null_when_no_period_contains_date(): void
    {
        $period = (new ResolveActiveOrganizationPeriodAction)->execute(
            [$this->period('2025', '2025-01-01', '2025-12-31')],
            new DateTimeImmutable('2026-05-16'),
        );

        $this->assertNull($period);
    }

    public function test_it_orders_periods_for_switcher_by_latest_start(): void
    {
        $periods = (new ResolveActiveOrganizationPeriodAction)->orderedForSwitcher([
            $this->period('2025', '2025-01-01', '2025-12-31'),
            $this->period('2026', '2026-01-01', '2026-12-31'),
        ]);

        $this->assertSame('2026', $periods[0]->name);
        $this->assertSame('2025', $periods[1]->name);
    }

    private function period(string $name, string $startsAt, string $endsAt): OrganizationPeriodData
    {
        return new OrganizationPeriodData(
            id: 'period-'.$name,
            name: $name,
            startsAt: new DateTimeImmutable($startsAt),
            endsAt: new DateTimeImmutable($endsAt),
        );
    }
}
