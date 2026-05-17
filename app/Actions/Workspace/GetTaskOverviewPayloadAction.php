<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Task\TaskStatus;
use Illuminate\Support\Facades\DB;

final class GetTaskOverviewPayloadAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string, tone: string}>,
     *     urgentTasks: array<int, array{id: int, title: string, project: string, pic: string, dueAt: string|null, status: string, isOverdue: bool}>,
     *     projects: array<int, array{id: int, name: string}>
     * }
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $now = now();
        $weekEnd = $now->copy()->addDays(7);

        $baseQuery = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('projects.organization_id', $context->organizationId)
            ->where('projects.status', '!=', 'archived');

        $openTasks = (clone $baseQuery)
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->count();
        $overdueTasks = (clone $baseQuery)
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->whereNotNull('project_tasks.due_at')
            ->whereDate('project_tasks.due_at', '<', $now->toDateString())
            ->count();
        $dueThisWeek = (clone $baseQuery)
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->whereNotNull('project_tasks.due_at')
            ->whereDate('project_tasks.due_at', '>=', $now->toDateString())
            ->whereDate('project_tasks.due_at', '<=', $weekEnd->toDateString())
            ->count();
        $doneSevenDays = (clone $baseQuery)
            ->where('project_tasks.status', TaskStatus::Done->value)
            ->where('project_tasks.updated_at', '>=', $now->copy()->subDays(7))
            ->count();

        $urgentTasks = (clone $baseQuery)
            ->leftJoin('users as pics', 'pics.id', '=', 'project_tasks.pic_user_id')
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->orderByRaw('project_tasks.due_at is null')
            ->orderBy('project_tasks.due_at')
            ->orderBy('project_tasks.id')
            ->limit(5)
            ->get([
                'project_tasks.id',
                'project_tasks.title',
                'project_tasks.status',
                'project_tasks.due_at',
                'projects.name as project_name',
                'pics.name as pic_name',
            ])
            ->map(static fn (object $task): array => [
                'id' => (int) $task->id,
                'title' => (string) $task->title,
                'project' => (string) $task->project_name,
                'pic' => is_string($task->pic_name) ? $task->pic_name : '-',
                'dueAt' => is_string($task->due_at) ? $task->due_at : null,
                'status' => (string) $task->status,
                'isOverdue' => is_string($task->due_at) && $task->due_at < $now->toDateString(),
            ])
            ->all();

        return [
            'metrics' => [
                ['label' => 'Open Task', 'value' => (string) $openTasks, 'note' => $dueThisWeek.' deadline minggu ini', 'tone' => 'primary'],
                ['label' => 'Overdue', 'value' => (string) $overdueTasks, 'note' => 'Butuh eskalasi PIC', 'tone' => $overdueTasks > 0 ? 'danger' : 'success'],
                ['label' => 'Selesai 7 Hari', 'value' => (string) $doneSevenDays, 'note' => 'Task selesai terbaru', 'tone' => 'success'],
            ],
            'urgentTasks' => $urgentTasks,
            'projects' => DB::table('projects')
                ->where('organization_id', $context->organizationId)
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                ])
                ->all(),
        ];
    }
}
