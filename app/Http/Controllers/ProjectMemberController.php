<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Project\AssignProjectMemberAction;
use App\Actions\Project\RemoveProjectMemberAction;
use App\Domain\Project\ProjectRole;
use App\Http\Requests\AssignProjectMemberRequest;
use App\Http\Requests\RemoveProjectMemberRequest;
use Illuminate\Http\RedirectResponse;

final class ProjectMemberController extends Controller
{
    public function store(
        AssignProjectMemberRequest $request,
        string $project,
        AssignProjectMemberAction $assignProjectMember,
    ): RedirectResponse {
        $assignProjectMember->execute(
            actorUserId: (int) $request->user()->id,
            projectSlug: $project,
            userId: (int) $request->validated('user_id'),
            role: ProjectRole::from((string) $request->validated('role')),
        );

        return back()->with('success', 'Anggota tim proker berhasil ditambahkan.');
    }

    public function destroy(
        RemoveProjectMemberRequest $request,
        string $project,
        int $member,
        RemoveProjectMemberAction $removeProjectMember,
    ): RedirectResponse {
        $removeProjectMember->execute(
            actorUserId: (int) $request->user()->id,
            projectSlug: $project,
            projectMemberId: $member,
        );

        return back()->with('success', 'Anggota tim proker berhasil dihapus.');
    }
}
