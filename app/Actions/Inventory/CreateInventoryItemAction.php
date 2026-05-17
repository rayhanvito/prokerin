<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Inventory\InventoryStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateInventoryItemAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @param  array{name: string, category: string, description?: string|null, location?: string|null, condition: string, purchased_at?: string|null, purchase_amount?: int|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, array $data): int
    {
        $activeOrganizationId = session('active_organization_id');
        $context = $this->activeOrganizationContext->execute(
            $actorUserId,
            is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        );

        if (! in_array($context->role, Roles::SECRETARY_AND_UP, true)) {
            throw new AuthorizationException('You are not allowed to manage inventory.');
        }

        $now = now();

        return (int) DB::table('inventory_items')->insertGetId([
            'organization_id' => $context->organizationId,
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'photo_path' => null,
            'location' => $data['location'] ?? null,
            'condition' => $data['condition'],
            'status' => InventoryStatus::Available->value,
            'qr_token' => $this->uniqueQrToken(),
            'purchased_at' => $data['purchased_at'] ?? null,
            'purchase_amount' => $data['purchase_amount'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function uniqueQrToken(): string
    {
        do {
            $token = Str::random(20);
        } while (DB::table('inventory_items')->where('qr_token', $token)->exists());

        return $token;
    }
}
