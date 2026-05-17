<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Domain\Inventory\InventoryStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RequestInventoryLoanAction
{
    /**
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $itemId, ?int $projectId, string $expectedReturnAt, ?string $notes): int
    {
        return DB::transaction(function () use ($actorUserId, $itemId, $projectId, $expectedReturnAt, $notes): int {
            $item = DB::table('inventory_items')
                ->join('organization_members', 'organization_members.organization_id', '=', 'inventory_items.organization_id')
                ->where('inventory_items.id', $itemId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereNull('inventory_items.deleted_at')
                ->lockForUpdate()
                ->first(['inventory_items.id', 'inventory_items.organization_id', 'inventory_items.status']);

            abort_if($item === null, 403);

            if ((string) $item->status !== InventoryStatus::Available->value) {
                throw ValidationException::withMessages([
                    'item_id' => 'Inventaris tidak tersedia untuk dipinjam.',
                ]);
            }

            if ($projectId !== null) {
                $projectExists = DB::table('projects')
                    ->where('id', $projectId)
                    ->where('organization_id', $item->organization_id)
                    ->exists();

                if (! $projectExists) {
                    throw ValidationException::withMessages([
                        'project_id' => 'Proker tidak valid untuk organisasi inventaris ini.',
                    ]);
                }
            }

            $now = now();
            $loanId = (int) DB::table('inventory_loans')->insertGetId([
                'item_id' => $itemId,
                'borrower_user_id' => $actorUserId,
                'project_id' => $projectId,
                'status' => 'pending',
                'loaned_at' => null,
                'expected_return_at' => $expectedReturnAt,
                'returned_at' => null,
                'return_condition' => null,
                'notes' => $notes,
                'approved_by_user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $loanId;
        });
    }
}
