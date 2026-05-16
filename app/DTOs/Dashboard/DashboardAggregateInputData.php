<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

use App\DTOs\Finance\BudgetSummaryData;
use App\DTOs\Report\LpjReadinessData;
use App\DTOs\Task\TaskBoardSummaryData;

final readonly class DashboardAggregateInputData
{
    public function __construct(
        public int $activeProjectCount,
        public int $proposalReviewCount,
        public TaskBoardSummaryData $taskSummary,
        public BudgetSummaryData $budgetSummary,
        public LpjReadinessData $lpjReadiness,
    ) {}
}
