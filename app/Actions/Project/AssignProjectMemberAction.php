<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectRole;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AssignProjectMemberAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, string $projectSlug, int $userId, ProjectRole $role): void
    {
        $project = DB::table('projects')
            ->where('slug', $projectSlug)
            ->first(['id', 'organization_id']);

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found.');
        }

        $this->authorize($actorUserId, (int) $project->id, (int) $project->organization_id);

        $belongsToOrganization = DB::table('organization_members')
            ->where('organization_id', (int) $project->organization_id)
            ->where('user_id', $userId)
            ->exists();

        if (! $belongsToOrganization) {
            throw ValidationException::withMessages([
                'user_id' => 'Project member must belong to the same organization.',
            ]);
        }

        $now = now();

        DB::table('project_members')->updateOrInsert(
            [
                'project_id' => (int) $project->id,
                'user_id' => $userId,
            ],
            [
                'role' => $role->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
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
