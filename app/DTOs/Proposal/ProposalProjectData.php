<?php

declare(strict_types=1);

namespace App\DTOs\Proposal;

use DateTimeImmutable;

final readonly class ProposalProjectData
{
    public function __construct(
        public string $name,
        public string $organizationName,
        public string $description,
        public string $targetAudience,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
        public string $projectLeadName,
    ) {}
}
