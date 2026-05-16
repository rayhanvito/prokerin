<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetApprovalDecision;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use DomainException;

final class DecideBudgetApprovalAction
{
    public function execute(BudgetLineData $line, BudgetApprovalDecision $decision): BudgetLineData
    {
        if ($line->status !== BudgetStatus::Review) {
            throw new DomainException('Only budget lines in review can be approved or rejected.');
        }

        return new BudgetLineData(
            name: $line->name,
            category: $line->category,
            plannedAmount: $line->plannedAmount,
            realizedAmount: $line->realizedAmount,
            status: $decision === BudgetApprovalDecision::Approve
                ? BudgetStatus::Approved
                : BudgetStatus::Rejected,
        );
    }
}
