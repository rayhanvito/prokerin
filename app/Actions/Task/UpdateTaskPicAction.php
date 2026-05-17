<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Domain\Project\ProjectRole;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateTaskPicAction
{
    public function execute(int $actorUserId, int $taskId, int $picUserId): void
    {
        $task = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('project_tasks.id', $taskId)
            ->where('projects.status', '!=', 'archived')
            ->first([
                'project_tasks.id',
                'project_tasks.project_id',
                'projects.organization_id',
            ]);

        if ($task === null) {
            throw new NotFoundHttpException('Task was not found for PIC assignment.');
        }

        $canAssignAsOrganizationRole = DB::table('organization_members')
            ->where('organization_id', (int) $task->organization_id)
            ->where('user_id', $actorUserId)
            ->whereIn('role', Roles::SECRETARY_AND_UP)
            ->exists();
        $canAssignAsProjectRole = DB::table('project_members')
            ->where('project_id', (int) $task->project_id)
            ->where('user_id', $actorUserId)
            ->whereIn('role', [ProjectRole::ProjectLead->value, ProjectRole::DivisionCoordinator->value])
            ->exists();

        $targetIsOrganizationMember = DB::table('organization_members')
            ->where('organization_id', (int) $task->organization_id)
            ->where('user_id', $picUserId)
            ->where('role', '!=', 'viewer')
            ->exists();

        if ((! $canAssignAsOrganizationRole && ! $canAssignAsProjectRole) || ! $targetIsOrganizationMember) {
            throw new NotFoundHttpException('Task was not found for PIC assignment.');
        }

        DB::table('project_tasks')
            ->where('id', $taskId)
            ->update([
                'pic_user_id' => $picUserId,
                'updated_at' => now(),
            ]);
    }
}
