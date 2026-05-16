<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Finance\BudgetRealizationData;
use DomainException;

final class RecordBudgetRealizationAction
{
    public function execute(BudgetLineData $line, BudgetRealizationData $realization): BudgetLineData
    {
        if (! $line->isApproved()) {
            throw new DomainException('Only approved budget lines can receive realization transactions.');
        }

        if ($realization->amount->amount <= 0) {
            throw new DomainException('Budget realization amount must be greater than zero.');
        }

        if (! $realization->hasReceipt) {
            throw new DomainException('Budget realization receipt is required.');
        }

        return new BudgetLineData(
            name: $line->name,
            category: $line->category,
            plannedAmount: $line->plannedAmount,
            realizedAmount: $line->realizedAmount->add($realization->amount),
            status: BudgetStatus::Realized,
        );
    }
}
