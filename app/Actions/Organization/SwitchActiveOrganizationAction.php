<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class SwitchActiveOrganizationAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $organizationId): void
    {
        $isMember = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->exists();

        if (! $isMember) {
            throw new AuthorizationException('You are not a member of this organization.');
        }

        session(['active_organization_id' => $organizationId]);
    }
}
