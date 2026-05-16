<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Finance\RecordBudgetRealizationAction;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Finance\BudgetRealizationData;
use App\Support\ValueObjects\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class RecordBudgetRealizationActionTest extends TestCase
{
    public function test_it_records_realization_on_approved_budget_line(): void
    {
        $line = (new RecordBudgetRealizationAction)->execute(
            $this->budgetLine(BudgetStatus::Approved, 500000),
            new BudgetRealizationData(
                transactionName: 'DP konsumsi',
                amount: Money::rupiah(2000000),
                hasReceipt: true,
            ),
        );

        $this->assertSame(BudgetStatus::Realized, $line->status);
        $this->assertSame(2500000, $line->realizedAmount->amount);
    }

    public function test_it_rejects_realization_for_unapproved_budget_line(): void
    {
        $this->expectException(DomainException::class);

        (new RecordBudgetRealizationAction)->execute(
            $this->budgetLine(BudgetStatus::Review, 0),
            new BudgetRealizationData('DP konsumsi', Money::rupiah(2000000), true),
        );
    }

    public function test_it_requires_receipt_for_realization(): void
    {
        $this->expectException(DomainException::class);

        (new RecordBudgetRealizationAction)->execute(
            $this->budgetLine(BudgetStatus::Approved, 0),
            new BudgetRealizationData('DP konsumsi', Money::rupiah(2000000), false),
        );
    }

    private function budgetLine(BudgetStatus $status, int $realizedAmount): BudgetLineData
    {
        return new BudgetLineData(
            name: 'Konsumsi peserta',
            category: 'Konsumsi',
            plannedAmount: Money::rupiah(6500000),
            realizedAmount: Money::rupiah($realizedAmount),
            status: $status,
        );
    }
}
