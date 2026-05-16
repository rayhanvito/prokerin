<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\DTOs\Organization\OrganizationPeriodData;
use DateTimeImmutable;

final class ResolveActiveOrganizationPeriodAction
{
    /**
     * @param  array<int, OrganizationPeriodData>  $periods
     */
    public function execute(array $periods, DateTimeImmutable $date): ?OrganizationPeriodData
    {
        $activePeriods = array_values(array_filter(
            $periods,
            static fn (OrganizationPeriodData $period): bool => $period->contains($date),
        ));

        usort(
            $activePeriods,
            static fn (OrganizationPeriodData $left, OrganizationPeriodData $right): int => $right->startsAt <=> $left->startsAt,
        );

        return $activePeriods[0] ?? null;
    }

    /**
     * @param  array<int, OrganizationPeriodData>  $periods
     * @return array<int, OrganizationPeriodData>
     */
    public function orderedForSwitcher(array $periods): array
    {
        usort(
            $periods,
            static fn (OrganizationPeriodData $left, OrganizationPeriodData $right): int => $right->startsAt <=> $left->startsAt,
        );

        return $periods;
    }
}
