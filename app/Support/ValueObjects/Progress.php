<?php

declare(strict_types=1);

namespace App\Support\ValueObjects;

use InvalidArgumentException;

final readonly class Progress
{
    public function __construct(public int $percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidArgumentException('Progress percentage must be between 0 and 100.');
        }
    }

    public static function fromCompletedItems(int $completed, int $total): self
    {
        if ($completed < 0 || $total < 0) {
            throw new InvalidArgumentException('Progress items cannot be negative.');
        }

        if ($total === 0) {
            return new self(0);
        }

        return new self((int) round(($completed / $total) * 100));
    }

    public function isComplete(): bool
    {
        return $this->percentage === 100;
    }
}
