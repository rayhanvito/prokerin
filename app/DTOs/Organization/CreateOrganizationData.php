<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

final readonly class CreateOrganizationData
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public string $planTier,
    ) {}
}
