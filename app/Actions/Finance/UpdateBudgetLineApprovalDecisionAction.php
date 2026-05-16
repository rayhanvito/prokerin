<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetApprovalDecision;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Finance\BudgetLineData;
use App\Support\ValueObjects\Money;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UpdateBudgetLineApprovalDecisionAction
{
    public function __construct(
        private DecideBudgetApprovalAction $decideBudgetApproval,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $budgetLineId, BudgetApprovalDecision $decision): void
    {
        DB::transaction(function () use ($actorUserId, $budgetLineId, $decision): void {
            $line = DB::table('budget_lines')
                ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
                ->where('budget_lines.id', $budgetLineId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'treasurer'])
                ->select([
                    'budget_lines.id',
                    'budget_lines.name',
                    'budget_lines.category',
                    'budget_lines.planned_amount',
                    'budget_lines.realized_amount',
                    'budget_lines.status',
                ])
                ->lockForUpdate()
                ->first();

            if ($line === null) {
                throw new NotFoundHttpException('Budget line was not found for the active workspace.');
            }

            try {
                $updatedLine = $this->decideBudgetApproval->execute(
                    new BudgetLineData(
                        name: (string) $line->name,
                        category: (string) $line->category,
                        plannedAmount: Money::rupiah((int) $line->planned_amount),
                        realizedAmount: Money::rupiah((int) $line->realized_amount),
                        status: BudgetStatus::from((string) $line->status),
                    ),
                    $decision,
                );
            } catch (DomainException $exception) {
                throw ValidationException::withMessages([
                    'budget_line' => $exception->getMessage(),
                ]);
            }

            DB::table('budget_lines')
                ->where('id', $budgetLineId)
                ->update([
                    'status' => $updatedLine->status->value,
                    'updated_at' => now(),
                ]);
        });
    }
}
