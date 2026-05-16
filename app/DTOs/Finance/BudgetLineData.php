<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use App\Domain\Finance\BudgetStatus;
use App\Support\ValueObjects\Money;

final readonly class BudgetLineData
{
    public function __construct(
        public string $name,
        public string $category,
        public Money $plannedAmount,
        public Money $realizedAmount,
        public BudgetStatus $status = BudgetStatus::Draft,
    ) {}

    public function isApproved(): bool
    {
        return $this->status === BudgetStatus::Approved
            || $this->status === BudgetStatus::Realized;
    }
}
