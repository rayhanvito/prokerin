<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class StoreSponsorVendorAction
{
    /**
     * @param  array{type: string, name: string, category: string, contact_person?: string|null, phone?: string|null, email?: string|null, address?: string|null, status: string, notes?: string|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, array $data): int
    {
        $organizationId = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId === null) {
            throw new AuthorizationException('You are not allowed to manage sponsor/vendor contacts.');
        }

        $now = now();

        return (int) DB::table('sponsors_vendors')->insertGetId([
            'organization_id' => (int) $organizationId,
            'type' => $data['type'],
            'name' => $data['name'],
            'category' => $data['category'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
