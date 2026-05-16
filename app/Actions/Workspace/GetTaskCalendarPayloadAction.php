<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetTaskCalendarPayloadAction
{
    /**
     * @return array{days: array<int, array{id: int, date: string, title: string, project: string, status: string}>}
     */
    public function execute(int $userId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        return [
            'days' => DB::table('project_tasks')
                ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                ->whereIn('projects.organization_id', $organizationIds)
                ->where('projects.status', '!=', 'archived')
                ->whereNotNull('project_tasks.due_at')
                ->orderBy('project_tasks.due_at')
                ->limit(12)
                ->get([
                    'project_tasks.id',
                    'project_tasks.title',
                    'project_tasks.status',
                    'project_tasks.due_at',
                    'projects.name as project_name',
                ])
                ->map(static fn (object $task): array => [
                    'id' => (int) $task->id,
                    'date' => substr((string) $task->due_at, 8, 2),
                    'title' => (string) $task->title,
                    'project' => (string) $task->project_name,
                    'status' => (string) $task->status,
                ])
                ->all(),
        ];
    }
}
