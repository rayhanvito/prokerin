<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Domain\Inventory\InventoryStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApproveInventoryLoanAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $loanId): void
    {
        DB::transaction(function () use ($actorUserId, $loanId): void {
            $loan = DB::table('inventory_loans')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_loans.item_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'inventory_items.organization_id')
                ->where('inventory_loans.id', $loanId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', Roles::SECRETARY_AND_UP)
                ->lockForUpdate()
                ->first([
                    'inventory_loans.id',
                    'inventory_loans.item_id',
                    'inventory_loans.status',
                    'inventory_items.status as item_status',
                ]);

            if ($loan === null) {
                throw new AuthorizationException('You are not allowed to approve this loan.');
            }

            if ((string) $loan->status !== 'pending' || (string) $loan->item_status !== InventoryStatus::Available->value) {
                throw ValidationException::withMessages([
                    'loan' => 'Peminjaman tidak bisa disetujui.',
                ]);
            }

            $now = now();

            DB::table('inventory_loans')
                ->where('id', $loanId)
                ->update([
                    'status' => 'approved',
                    'loaned_at' => $now,
                    'approved_by_user_id' => $actorUserId,
                    'updated_at' => $now,
                ]);

            DB::table('inventory_items')
                ->where('id', $loan->item_id)
                ->update([
                    'status' => InventoryStatus::Loaned->value,
                    'updated_at' => $now,
                ]);
        });
    }
}
