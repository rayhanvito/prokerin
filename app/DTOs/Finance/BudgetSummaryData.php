<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use App\Support\ValueObjects\Money;
use App\Support\ValueObjects\Progress;

final readonly class BudgetSummaryData
{
    public function __construct(
        public Money $plannedTotal,
        public Money $realizedTotal,
        public Money $remainingBudget,
        public Progress $realizationProgress,
        public int $lineCount,
        public int $approvedLineCount,
        public bool $hasOverspend,
    ) {}

    /**
     * @return array{
     *     plannedTotal: int,
     *     realizedTotal: int,
     *     remainingBudget: int,
     *     realizationProgress: int,
     *     lineCount: int,
     *     approvedLineCount: int,
     *     hasOverspend: bool,
     *     currency: string
     * }
     */
    public function toArray(): array
    {
        return [
            'plannedTotal' => $this->plannedTotal->amount,
            'realizedTotal' => $this->realizedTotal->amount,
            'remainingBudget' => $this->remainingBudget->amount,
            'realizationProgress' => $this->realizationProgress->percentage,
            'lineCount' => $this->lineCount,
            'approvedLineCount' => $this->approvedLineCount,
            'hasOverspend' => $this->hasOverspend,
            'currency' => $this->plannedTotal->currency,
        ];
    }
}
