<?php

declare(strict_types=1);

namespace App\DTOs\Workspace;

final readonly class ActiveOrganizationContextData
{
    public function __construct(
        public int $organizationId,
        public string $role,
        public ?int $activePeriodId,
    ) {}
}
