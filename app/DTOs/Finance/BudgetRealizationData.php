<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use App\Support\ValueObjects\Money;

final readonly class BudgetRealizationData
{
    public function __construct(
        public string $transactionName,
        public Money $amount,
        public bool $hasReceipt,
    ) {}
}
