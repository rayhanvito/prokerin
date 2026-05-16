<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Domain\Organization\OrganizationRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateOrganizationMemberRoleAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $membershipId, OrganizationRole $newRole): void
    {
        $membership = DB::table('organization_members')
            ->where('id', $membershipId)
            ->first();

        if ($membership === null) {
            throw new NotFoundHttpException('Organization member was not found.');
        }

        $actorMembership = DB::table('organization_members')
            ->where('organization_id', $membership->organization_id)
            ->where('user_id', $actorUserId)
            ->first();

        if ($actorMembership === null || ! $this->canManageMembers((string) $actorMembership->role)) {
            throw new AuthorizationException('You are not allowed to update organization roles.');
        }

        if ($actorMembership->role === OrganizationRole::Admin->value && $newRole === OrganizationRole::Owner) {
            throw new AuthorizationException('Only organization owners can assign owner role.');
        }

        if ($actorMembership->role === OrganizationRole::Admin->value && $membership->role === OrganizationRole::Owner->value) {
            throw new AuthorizationException('Organization admins cannot change owner role.');
        }

        if ($membership->role === OrganizationRole::Owner->value && $newRole !== OrganizationRole::Owner) {
            $ownerCount = DB::table('organization_members')
                ->where('organization_id', $membership->organization_id)
                ->where('role', OrganizationRole::Owner->value)
                ->count();

            if ($ownerCount <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Organization must keep at least one owner.',
                ]);
            }
        }

        DB::table('organization_members')
            ->where('id', $membershipId)
            ->update([
                'role' => $newRole->value,
                'updated_at' => now(),
            ]);
    }

    private function canManageMembers(string $role): bool
    {
        return in_array($role, [
            OrganizationRole::Owner->value,
            OrganizationRole::Admin->value,
        ], true);
    }
}
