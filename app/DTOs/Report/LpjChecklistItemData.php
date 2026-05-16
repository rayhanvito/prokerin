<?php

declare(strict_types=1);

namespace App\DTOs\Report;

final readonly class LpjChecklistItemData
{
    public function __construct(
        public string $title,
        public bool $isComplete,
        public bool $isRequired = true,
    ) {}
}
