<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RemoveOrganizationMemberAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $membershipId): void
    {
        $target = DB::table('organization_members')
            ->where('id', $membershipId)
            ->first(['id', 'organization_id', 'user_id', 'role']);

        if ($target === null) {
            throw new NotFoundHttpException('Organization member was not found.');
        }

        $actorRole = DB::table('organization_members')
            ->where('organization_id', (int) $target->organization_id)
            ->where('user_id', $actorUserId)
            ->value('role');

        if ($actorRole !== Roles::ORGANIZATION_OWNER) {
            throw new AuthorizationException('Only organization owners can remove members.');
        }

        if ((string) $target->role === Roles::ORGANIZATION_OWNER) {
            $ownerCount = DB::table('organization_members')
                ->where('organization_id', (int) $target->organization_id)
                ->where('role', Roles::ORGANIZATION_OWNER)
                ->count();

            if ($ownerCount <= 1) {
                throw new AuthorizationException('The last organization owner cannot be removed.');
            }
        }

        DB::table('organization_members')
            ->where('id', $membershipId)
            ->delete();
    }
}
