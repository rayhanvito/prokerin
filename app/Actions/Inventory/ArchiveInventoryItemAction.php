<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Domain\Inventory\InventoryStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ArchiveInventoryItemAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $itemId): void
    {
        $allowed = DB::table('inventory_items')
            ->join('organization_members', 'organization_members.organization_id', '=', 'inventory_items.organization_id')
            ->where('inventory_items.id', $itemId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', Roles::SECRETARY_AND_UP)
            ->whereNull('inventory_items.deleted_at')
            ->exists();

        if (! $allowed) {
            throw new AuthorizationException('You are not allowed to archive this inventory item.');
        }

        DB::table('inventory_items')
            ->where('id', $itemId)
            ->update([
                'status' => InventoryStatus::Archived->value,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
