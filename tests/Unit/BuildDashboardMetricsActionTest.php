<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Dashboard\BuildDashboardMetricsAction;
use App\Actions\Finance\CalculateBudgetSummaryAction;
use App\Actions\Report\CalculateLpjReadinessAction;
use App\Actions\Task\CalculateTaskBoardSummaryAction;
use App\Domain\Finance\BudgetStatus;
use App\Domain\Task\TaskStatus;
use App\DTOs\Dashboard\DashboardAggregateInputData;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Report\LpjChecklistItemData;
use App\DTOs\Task\TaskLineData;
use App\Support\ValueObjects\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildDashboardMetricsActionTest extends TestCase
{
    public function test_it_builds_dashboard_metrics_from_domain_summaries(): void
    {
        $now = new DateTimeImmutable('2026-05-16 10:00:00');
        $metrics = (new BuildDashboardMetricsAction)->execute(
            new DashboardAggregateInputData(
                activeProjectCount: 12,
                proposalReviewCount: 4,
                taskSummary: (new CalculateTaskBoardSummaryAction)->execute([
                    new TaskLineData('Final proposal', 'Seminar', 'Nadia', TaskStatus::InProgress, new DateTimeImmutable('2026-05-15 10:00:00')),
                    new TaskLineData('Upload dokumentasi', 'Workshop', 'Raka', TaskStatus::Done, new DateTimeImmutable('2026-05-17 10:00:00')),
                ], $now),
                budgetSummary: (new CalculateBudgetSummaryAction)->execute([
                    new BudgetLineData('Konsumsi', 'Konsumsi', Money::rupiah(6500000), Money::rupiah(0), BudgetStatus::Approved),
                ]),
                lpjReadiness: (new CalculateLpjReadinessAction)->execute([
                    new LpjChecklistItemData('Dokumentasi kegiatan', true),
                    new LpjChecklistItemData('Daftar hadir', false),
                ]),
            ),
        );

        $this->assertCount(4, $metrics);
        $this->assertSame('Active Proker', $metrics[0]['label']);
        $this->assertSame('12', $metrics[0]['value']);
        $this->assertSame('danger', $metrics[1]['tone']);
        $this->assertSame('Rp6.500.000', $metrics[2]['value']);
        $this->assertSame('50%', $metrics[3]['value']);
    }
}
