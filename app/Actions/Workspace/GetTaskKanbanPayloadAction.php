<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Task\TaskStatus;
use Illuminate\Support\Facades\DB;

final class GetTaskKanbanPayloadAction
{
    /**
     * @return array{columns: array<int, array{status: string, title: string, tasks: array<int, array{id: int, title: string, project: string, pic: string, dueAt: string|null, status: string}>}>}
     */
    public function execute(int $userId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        $tasks = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->leftJoin('users as pics', 'pics.id', '=', 'project_tasks.pic_user_id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->where('projects.status', '!=', 'archived')
            ->orderBy('project_tasks.due_at')
            ->get([
                'project_tasks.id',
                'project_tasks.title',
                'project_tasks.status',
                'project_tasks.due_at',
                'projects.name as project_name',
                'pics.name as pic_name',
            ]);

        return [
            'columns' => collect(TaskStatus::cases())
                ->map(fn (TaskStatus $status): array => [
                    'status' => $status->value,
                    'title' => $status->label(),
                    'tasks' => $tasks
                        ->where('status', $status->value)
                        ->map(static fn (object $task): array => [
                            'id' => (int) $task->id,
                            'title' => (string) $task->title,
                            'project' => (string) $task->project_name,
                            'pic' => is_string($task->pic_name) ? $task->pic_name : '-',
                            'dueAt' => is_string($task->due_at) ? $task->due_at : null,
                            'status' => (string) $task->status,
                        ])
                        ->values()
                        ->all(),
                ])
                ->all(),
        ];
    }
}
