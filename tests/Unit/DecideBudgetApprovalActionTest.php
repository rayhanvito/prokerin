<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Finance\DecideBudgetApprovalAction;
use App\Domain\Finance\BudgetApprovalDecision;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use App\Support\ValueObjects\Money;
use DomainException;
use PHPUnit\Framework\TestCase;

final class DecideBudgetApprovalActionTest extends TestCase
{
    public function test_it_approves_budget_line_in_review(): void
    {
        $line = (new DecideBudgetApprovalAction)->execute(
            $this->budgetLine(BudgetStatus::Review),
            BudgetApprovalDecision::Approve,
        );

        $this->assertSame(BudgetStatus::Approved, $line->status);
    }

    public function test_it_rejects_budget_line_in_review(): void
    {
        $line = (new DecideBudgetApprovalAction)->execute(
            $this->budgetLine(BudgetStatus::Review),
            BudgetApprovalDecision::Reject,
        );

        $this->assertSame(BudgetStatus::Rejected, $line->status);
    }

    public function test_it_rejects_decision_for_non_review_budget_line(): void
    {
        $this->expectException(DomainException::class);

        (new DecideBudgetApprovalAction)->execute(
            $this->budgetLine(BudgetStatus::Draft),
            BudgetApprovalDecision::Approve,
        );
    }

    private function budgetLine(BudgetStatus $status): BudgetLineData
    {
        return new BudgetLineData(
            name: 'Konsumsi peserta',
            category: 'Konsumsi',
            plannedAmount: Money::rupiah(6500000),
            realizedAmount: Money::rupiah(0),
            status: $status,
        );
    }
}
