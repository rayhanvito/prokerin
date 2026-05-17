<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Membership\RemoveOrganizationMemberAction;
use App\Http\Requests\RemoveOrganizationMemberRequest;
use Illuminate\Http\RedirectResponse;

final class OrganizationMemberController extends Controller
{
    public function destroy(
        RemoveOrganizationMemberRequest $request,
        int $member,
        RemoveOrganizationMemberAction $removeMember,
    ): RedirectResponse {
        $removeMember->execute((int) $request->user()->id, $member);

        return back()->with('success', 'Anggota berhasil dihapus dari organisasi.');
    }
}
