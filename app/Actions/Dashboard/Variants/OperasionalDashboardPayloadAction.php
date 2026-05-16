<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Variants;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class OperasionalDashboardPayloadAction
{
    public function execute(int $actorUserId, int $organizationId): array
    {
        return Cache::remember("dashboard:operasional:{$organizationId}:{$actorUserId}", 300, function () use ($actorUserId, $organizationId): array {
            $projectIds = $this->projectIds($actorUserId, $organizationId);
            $taskTotal = DB::table('project_tasks')->whereIn('project_id', $projectIds)->count();
            $taskDone = DB::table('project_tasks')->whereIn('project_id', $projectIds)->where('status', 'done')->count();
            $rabTotal = (int) DB::table('budget_lines')->whereIn('project_id', $projectIds)->sum('planned_amount');
            $realizedTotal = (int) DB::table('budget_lines')->whereIn('project_id', $projectIds)->sum('realized_amount');

            return [
                'kpiMetrics' => [
                    ['label' => 'Proker yang Dipimpin', 'value' => count($projectIds)],
                    ['label' => 'Task Selesai / Total', 'value' => "{$taskDone}/{$taskTotal}"],
                    ['label' => 'Task Overdue', 'value' => DB::table('project_tasks')->whereIn('project_id', $projectIds)->where('status', '!=', 'done')->whereDate('due_at', '<', now())->count()],
                    ['label' => 'Sisa Anggaran Proker', 'value' => 'Rp '.number_format(max(0, $rabTotal - $realizedTotal), 0, ',', '.')],
                ],
                'myProjects' => $this->myProjects($projectIds),
                'urgentTasks' => $this->urgentTasks($projectIds),
                'upcomingMilestones' => $this->upcomingMilestones($projectIds),
                'teamSummary' => $this->teamSummary($projectIds),
            ];
        });
    }

    /**
     * @return array<int, int>
     */
    private function projectIds(int $actorUserId, int $organizationId): array
    {
        $projectIds = DB::table('project_members')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_members.user_id', $actorUserId)
            ->whereIn('project_members.role', ['project_lead', 'division_coordinator'])
            ->pluck('projects.id');

        if ($projectIds->isEmpty()) {
            $projectIds = DB::table('project_members')
                ->join('projects', 'projects.id', '=', 'project_members.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('project_members.user_id', $actorUserId)
                ->pluck('projects.id');
        }

        return $projectIds->map(static fn (int|string $id): int => (int) $id)->all();
    }

    private function myProjects(array $projectIds): array
    {
        return DB::table('projects')
            ->leftJoin('users', 'users.id', '=', 'projects.project_lead_id')
            ->whereIn('projects.id', $projectIds)
            ->orderBy('projects.ends_at')
            ->get(['projects.id', 'projects.slug', 'projects.name', 'projects.status', 'projects.progress', 'projects.ends_at', 'users.name as lead_name'])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'slug' => (string) $project->slug,
                'name' => (string) $project->name,
                'status' => (string) $project->status,
                'progressPercentage' => (int) $project->progress,
                'deadline' => $project->ends_at === null ? null : (string) $project->ends_at,
                'projectLead' => (string) ($project->lead_name ?? 'Belum ditentukan'),
            ])
            ->all();
    }

    private function urgentTasks(array $projectIds): array
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->leftJoin('users', 'users.id', '=', 'project_tasks.pic_user_id')
            ->whereIn('project_tasks.project_id', $projectIds)
            ->where('project_tasks.status', '!=', 'done')
            ->whereDate('project_tasks.due_at', '<=', now()->addDays(3))
            ->orderBy('project_tasks.due_at')
            ->limit(8)
            ->get(['project_tasks.id', 'project_tasks.title', 'project_tasks.status', 'project_tasks.due_at', 'projects.name as project_name', 'users.name as pic_name'])
            ->map(static fn (object $task): array => [
                'id' => (int) $task->id,
                'title' => (string) $task->title,
                'projectName' => (string) $task->project_name,
                'picName' => (string) ($task->pic_name ?? 'Belum ada PIC'),
                'status' => (string) $task->status,
                'dueAt' => $task->due_at === null ? null : (string) $task->due_at,
            ])
            ->all();
    }

    private function upcomingMilestones(array $projectIds): array
    {
        return DB::table('projects')
            ->whereIn('id', $projectIds)
            ->orderBy('ends_at')
            ->limit(6)
            ->get(['id', 'name', 'status', 'starts_at', 'ends_at'])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'title' => (string) $project->name,
                'status' => (string) $project->status,
                'date' => (string) ($project->ends_at ?? $project->starts_at ?? '-'),
            ])
            ->all();
    }

    private function teamSummary(array $projectIds): array
    {
        return DB::table('project_members')
            ->join('users', 'users.id', '=', 'project_members.user_id')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->leftJoin('project_tasks', function ($join): void {
                $join->on('project_tasks.project_id', '=', 'projects.id')
                    ->on('project_tasks.pic_user_id', '=', 'users.id');
            })
            ->whereIn('project_members.project_id', $projectIds)
            ->groupBy('projects.name', 'users.name')
            ->orderBy('projects.name')
            ->get([
                'projects.name as project_name',
                'users.name as member_name',
                DB::raw("sum(case when project_tasks.status = 'done' then 1 else 0 end) as done_tasks"),
                DB::raw("sum(case when project_tasks.status is not null and project_tasks.status != 'done' then 1 else 0 end) as open_tasks"),
            ])
            ->map(static fn (object $row): array => [
                'projectName' => (string) $row->project_name,
                'memberName' => (string) $row->member_name,
                'doneTasks' => (int) $row->done_tasks,
                'openTasks' => (int) $row->open_tasks,
            ])
            ->all();
    }
}
