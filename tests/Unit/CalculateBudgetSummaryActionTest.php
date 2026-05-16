<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Finance\CalculateBudgetSummaryAction;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use App\Support\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CalculateBudgetSummaryActionTest extends TestCase
{
    public function test_it_calculates_budget_summary_from_lines(): void
    {
        $summary = (new CalculateBudgetSummaryAction)->execute([
            new BudgetLineData(
                name: 'Konsumsi panitia',
                category: 'Konsumsi',
                plannedAmount: Money::rupiah(2500000),
                realizedAmount: Money::rupiah(2000000),
                status: BudgetStatus::Realized,
            ),
            new BudgetLineData(
                name: 'Sewa aula',
                category: 'Venue',
                plannedAmount: Money::rupiah(3000000),
                realizedAmount: Money::rupiah(0),
                status: BudgetStatus::Approved,
            ),
        ]);

        $this->assertSame(5500000, $summary->plannedTotal->amount);
        $this->assertSame(2000000, $summary->realizedTotal->amount);
        $this->assertSame(3500000, $summary->remainingBudget->amount);
        $this->assertSame(36, $summary->realizationProgress->percentage);
        $this->assertSame(2, $summary->lineCount);
        $this->assertSame(2, $summary->approvedLineCount);
        $this->assertFalse($summary->hasOverspend);
    }

    public function test_empty_budget_lines_return_zero_summary(): void
    {
        $summary = (new CalculateBudgetSummaryAction)->execute([]);

        $this->assertSame(0, $summary->plannedTotal->amount);
        $this->assertSame(0, $summary->realizedTotal->amount);
        $this->assertSame(0, $summary->remainingBudget->amount);
        $this->assertSame(0, $summary->realizationProgress->percentage);
        $this->assertSame(0, $summary->lineCount);
    }

    public function test_it_caps_realization_progress_and_flags_overspend(): void
    {
        $summary = (new CalculateBudgetSummaryAction)->execute([
            new BudgetLineData(
                name: 'Dokumentasi',
                category: 'Publikasi',
                plannedAmount: Money::rupiah(1000000),
                realizedAmount: Money::rupiah(1250000),
                status: BudgetStatus::Realized,
            ),
        ]);

        $this->assertSame(0, $summary->remainingBudget->amount);
        $this->assertSame(100, $summary->realizationProgress->percentage);
        $this->assertTrue($summary->hasOverspend);
    }

    public function test_it_rejects_mismatched_budget_currencies(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CalculateBudgetSummaryAction)->execute([
            new BudgetLineData(
                name: 'Honor narasumber',
                category: 'Program',
                plannedAmount: new Money(100, 'USD'),
                realizedAmount: new Money(0, 'USD'),
                status: BudgetStatus::Approved,
            ),
        ]);
    }
}
