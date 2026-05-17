<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Domain\Project\ProjectRole;
use App\Domain\Task\TaskStatus;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CreateTaskAction
{
    public function execute(int $actorUserId, int $projectId, string $title, ?string $dueAt = null): int
    {
        $project = DB::table('projects')
            ->where('id', $projectId)
            ->where('status', '!=', 'archived')
            ->first(['id', 'organization_id']);

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found for task creation.');
        }

        $canCreateAsOrganizationRole = DB::table('organization_members')
            ->where('organization_id', (int) $project->organization_id)
            ->where('user_id', $actorUserId)
            ->whereIn('role', Roles::SECRETARY_AND_UP)
            ->exists();
        $canCreateAsProjectRole = DB::table('project_members')
            ->where('project_id', (int) $project->id)
            ->where('user_id', $actorUserId)
            ->whereIn('role', [ProjectRole::ProjectLead->value, ProjectRole::DivisionCoordinator->value])
            ->exists();

        if (! $canCreateAsOrganizationRole && ! $canCreateAsProjectRole) {
            throw new NotFoundHttpException('Project was not found for task creation.');
        }

        return (int) DB::table('project_tasks')->insertGetId([
            'project_id' => (int) $project->id,
            'pic_user_id' => null,
            'title' => $title,
            'division' => null,
            'status' => TaskStatus::Backlog->value,
            'due_at' => $dueAt,
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
