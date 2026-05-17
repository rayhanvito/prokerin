<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Domain\Inventory\InventoryCondition;
use App\Domain\Inventory\InventoryStatus;
use App\Domain\Inventory\LoanReturnCondition;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReturnInventoryLoanAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $loanId, string $returnCondition, ?string $notes): void
    {
        DB::transaction(function () use ($actorUserId, $loanId, $returnCondition, $notes): void {
            $loan = DB::table('inventory_loans')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_loans.item_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'inventory_items.organization_id')
                ->where('inventory_loans.id', $loanId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', Roles::SECRETARY_AND_UP)
                ->lockForUpdate()
                ->first(['inventory_loans.id', 'inventory_loans.item_id', 'inventory_loans.status']);

            if ($loan === null) {
                throw new AuthorizationException('You are not allowed to return this loan.');
            }

            if ((string) $loan->status !== 'approved') {
                throw ValidationException::withMessages([
                    'loan' => 'Peminjaman belum aktif atau sudah dikembalikan.',
                ]);
            }

            $itemStatus = InventoryStatus::Available->value;
            $itemCondition = null;

            if ($returnCondition === LoanReturnCondition::Damaged->value) {
                $itemCondition = InventoryCondition::NeedsRepair->value;
            }

            if ($returnCondition === LoanReturnCondition::Lost->value) {
                $itemStatus = InventoryStatus::Lost->value;
            }

            $now = now();

            DB::table('inventory_loans')
                ->where('id', $loanId)
                ->update([
                    'status' => 'returned',
                    'returned_at' => $now,
                    'return_condition' => $returnCondition,
                    'notes' => $notes,
                    'updated_at' => $now,
                ]);

            DB::table('inventory_items')
                ->where('id', $loan->item_id)
                ->update(array_filter([
                    'status' => $itemStatus,
                    'condition' => $itemCondition,
                    'updated_at' => $now,
                ], static fn (mixed $value): bool => $value !== null));
        });
    }
}
