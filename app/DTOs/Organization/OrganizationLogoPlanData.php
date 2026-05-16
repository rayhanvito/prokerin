<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

final readonly class OrganizationLogoPlanData
{
    public function __construct(
        public bool $isValid,
        public string $disk,
        public ?string $storagePath,
        /** @var array<int, string> */
        public array $errors,
    ) {}
}
