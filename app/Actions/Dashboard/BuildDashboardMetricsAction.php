<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\DashboardAggregateInputData;
use App\DTOs\Dashboard\DashboardTone;
use App\DTOs\Dashboard\MetricCardData;

final class BuildDashboardMetricsAction
{
    /**
     * @return array<int, array{label: string, value: string, note: string, tone: string}>
     */
    public function execute(DashboardAggregateInputData $input): array
    {
        return [
            (new MetricCardData(
                label: 'Active Proker',
                value: (string) $input->activeProjectCount,
                note: $input->proposalReviewCount.' masuk fase proposal',
                tone: DashboardTone::Primary,
            ))->toArray(),
            (new MetricCardData(
                label: 'Open Tasks',
                value: (string) $input->taskSummary->openTasks,
                note: $input->taskSummary->dueSoonTasks.' deadline minggu ini',
                tone: $input->taskSummary->overdueTasks > 0 ? DashboardTone::Danger : DashboardTone::Success,
            ))->toArray(),
            (new MetricCardData(
                label: 'RAB Draft',
                value: $input->budgetSummary->plannedTotal->formatted(),
                note: $input->budgetSummary->approvedLineCount.' item approved',
                tone: $input->budgetSummary->hasOverspend ? DashboardTone::Danger : DashboardTone::Warning,
            ))->toArray(),
            (new MetricCardData(
                label: 'LPJ Readiness',
                value: $input->lpjReadiness->completionProgress->percentage.'%',
                note: count($input->lpjReadiness->missingRequiredItems).' item belum lengkap',
                tone: $input->lpjReadiness->isReadyForReview ? DashboardTone::Success : DashboardTone::Default,
            ))->toArray(),
        ];
    }
}
