<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Membership\UpdateOrganizationMemberRoleAction;
use App\Domain\Organization\OrganizationRole;
use App\Http\Requests\UpdateOrganizationMemberRoleRequest;
use Illuminate\Http\RedirectResponse;

final class OrganizationMemberRoleController extends Controller
{
    public function update(
        UpdateOrganizationMemberRoleRequest $request,
        int $member,
        UpdateOrganizationMemberRoleAction $updateRole,
    ): RedirectResponse {
        $updateRole->execute(
            actorUserId: (int) $request->user()->id,
            membershipId: $member,
            newRole: OrganizationRole::from((string) $request->validated('role')),
        );

        return back()->with('success', 'Role anggota berhasil diperbarui.');
    }
}
