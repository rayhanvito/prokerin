<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Finance\BudgetSummaryData;
use App\Support\ValueObjects\Money;
use App\Support\ValueObjects\Progress;

final class CalculateBudgetSummaryAction
{
    /**
     * @param  array<int, BudgetLineData>  $lines
     */
    public function execute(array $lines): BudgetSummaryData
    {
        $plannedTotal = Money::rupiah(0);
        $realizedTotal = Money::rupiah(0);
        $approvedLineCount = 0;

        foreach ($lines as $line) {
            $plannedTotal = $plannedTotal->add($line->plannedAmount);
            $realizedTotal = $realizedTotal->add($line->realizedAmount);

            if ($line->isApproved()) {
                $approvedLineCount++;
            }
        }

        return new BudgetSummaryData(
            plannedTotal: $plannedTotal,
            realizedTotal: $realizedTotal,
            remainingBudget: $plannedTotal->subtract($realizedTotal),
            realizationProgress: $this->calculateRealizationProgress($plannedTotal, $realizedTotal),
            lineCount: count($lines),
            approvedLineCount: $approvedLineCount,
            hasOverspend: $realizedTotal->amount > $plannedTotal->amount,
        );
    }

    private function calculateRealizationProgress(Money $plannedTotal, Money $realizedTotal): Progress
    {
        if ($plannedTotal->amount === 0) {
            return new Progress(0);
        }

        return new Progress((int) min(100, round(($realizedTotal->amount / $plannedTotal->amount) * 100)));
    }
}
