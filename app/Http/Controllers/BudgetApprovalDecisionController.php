<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Finance\UpdateBudgetLineApprovalDecisionAction;
use App\Domain\Finance\BudgetApprovalDecision;
use App\Http\Requests\DecideBudgetLineApprovalRequest;
use Illuminate\Http\RedirectResponse;

final class BudgetApprovalDecisionController extends Controller
{
    public function update(
        DecideBudgetLineApprovalRequest $request,
        int $budgetLine,
        UpdateBudgetLineApprovalDecisionAction $updateBudgetLineApprovalDecision,
    ): RedirectResponse {
        $decision = BudgetApprovalDecision::from((string) $request->validated('decision'));

        $updateBudgetLineApprovalDecision->execute(
            actorUserId: (int) $request->user()->id,
            budgetLineId: $budgetLine,
            decision: $decision,
        );

        return back()->with(
            'success',
            $decision === BudgetApprovalDecision::Approve
                ? 'RAB disetujui dan siap direalisasikan.'
                : 'RAB ditolak dan dikembalikan untuk revisi.',
        );
    }
}
