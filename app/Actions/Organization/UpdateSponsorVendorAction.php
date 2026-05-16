<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateSponsorVendorAction
{
    /**
     * @param  array{type: string, name: string, category: string, contact_person?: string|null, phone?: string|null, email?: string|null, address?: string|null, status: string, notes?: string|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $sponsorVendorId, array $data): void
    {
        $contact = DB::table('sponsors_vendors')
            ->join('organization_members', 'organization_members.organization_id', '=', 'sponsors_vendors.organization_id')
            ->where('sponsors_vendors.id', $sponsorVendorId)
            ->where('organization_members.user_id', $actorUserId)
            ->select(['sponsors_vendors.id', 'organization_members.role'])
            ->first();

        if ($contact === null) {
            throw new NotFoundHttpException('Sponsor/vendor contact was not found for this workspace.');
        }

        if (! in_array((string) $contact->role, ['organization_owner', 'organization_admin'], true)) {
            throw new AuthorizationException('You are not allowed to manage sponsor/vendor contacts.');
        }

        DB::table('sponsors_vendors')
            ->where('id', $sponsorVendorId)
            ->update([
                'type' => $data['type'],
                'name' => $data['name'],
                'category' => $data['category'],
                'contact_person' => $data['contact_person'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'updated_at' => now(),
            ]);
    }
}
