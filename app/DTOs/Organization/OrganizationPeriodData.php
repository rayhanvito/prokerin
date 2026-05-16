<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

use DateTimeImmutable;

final readonly class OrganizationPeriodData
{
    public function __construct(
        public string $id,
        public string $name,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
    ) {}

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startsAt && $date <= $this->endsAt;
    }
}
