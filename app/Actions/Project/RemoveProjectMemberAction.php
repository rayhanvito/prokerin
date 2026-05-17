<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectRole;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RemoveProjectMemberAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, string $projectSlug, int $projectMemberId): void
    {
        $member = DB::table('project_members')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->where('project_members.id', $projectMemberId)
            ->where('projects.slug', $projectSlug)
            ->first([
                'project_members.id',
                'project_members.user_id',
                'project_members.role',
                'projects.id as project_id',
                'projects.organization_id',
                'projects.project_lead_id',
            ]);

        if ($member === null) {
            throw new NotFoundHttpException('Project member was not found.');
        }

        $this->authorize($actorUserId, (int) $member->project_id, (int) $member->organization_id);

        if ((int) $member->user_id === (int) $member->project_lead_id || (string) $member->role === ProjectRole::ProjectLead->value) {
            throw new AuthorizationException('Project lead cannot be removed from the project members list.');
        }

        DB::table('project_members')
            ->where('id', $projectMemberId)
            ->delete();
    }

    /**
     * @throws AuthorizationException
     */
    private function authorize(int $actorUserId, int $projectId, int $organizationId): void
    {
        $organizationRole = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $actorUserId)
            ->value('role');

        if (in_array($organizationRole, Roles::ORGANIZATION_MANAGERS, true)) {
            return;
        }

        $projectRole = DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $actorUserId)
            ->value('role');

        if ($projectRole === ProjectRole::ProjectLead->value) {
            return;
        }

        throw new AuthorizationException('You are not allowed to manage project members.');
    }
}
