<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class UpdateInventoryItemAction
{
    /**
     * @param  array{name: string, category: string, description?: string|null, location?: string|null, condition: string, status: string, purchased_at?: string|null, purchase_amount?: int|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $itemId, array $data): void
    {
        $item = $this->manageableItem($actorUserId, $itemId);

        if ($item === null) {
            throw new AuthorizationException('You are not allowed to update this inventory item.');
        }

        DB::table('inventory_items')
            ->where('id', $itemId)
            ->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'condition' => $data['condition'],
                'status' => $data['status'],
                'purchased_at' => $data['purchased_at'] ?? null,
                'purchase_amount' => $data['purchase_amount'] ?? null,
                'updated_at' => now(),
            ]);
    }

    private function manageableItem(int $actorUserId, int $itemId): ?object
    {
        return DB::table('inventory_items')
            ->join('organization_members', 'organization_members.organization_id', '=', 'inventory_items.organization_id')
            ->where('inventory_items.id', $itemId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', Roles::SECRETARY_AND_UP)
            ->whereNull('inventory_items.deleted_at')
            ->first(['inventory_items.id']);
    }
}
