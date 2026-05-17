<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Actions\Project\CalculateProjectProgressAction;
use App\Domain\Task\TaskStatus;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateTaskStatusAction
{
    public function __construct(
        private readonly CalculateProjectProgressAction $calculateProjectProgress,
    ) {}

    public function execute(int $actorUserId, int $taskId, TaskStatus $status): void
    {
        $task = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('project_tasks.id', $taskId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary'])
            ->select('project_tasks.id', 'project_tasks.project_id')
            ->first();

        if ($task === null) {
            throw new NotFoundHttpException('Task was not found for the active workspace.');
        }

        DB::table('project_tasks')
            ->where('id', $taskId)
            ->update([
                'status' => $status->value,
                'completed_at' => $status === TaskStatus::Done ? now() : null,
                'updated_at' => now(),
            ]);

        $completionFlags = DB::table('project_tasks')
            ->where('project_id', (int) $task->project_id)
            ->pluck('status')
            ->map(static fn (mixed $taskStatus): bool => $taskStatus === TaskStatus::Done->value)
            ->all();
        $progress = $this->calculateProjectProgress->execute($completionFlags);

        DB::table('projects')
            ->where('id', (int) $task->project_id)
            ->update([
                'progress' => $progress->percentage,
                'updated_at' => now(),
            ]);
    }
}
