<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class KepanitiaanDashboardPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $organizationId): array
    {
        $organization = DB::table('organizations')
            ->where('id', $organizationId)
            ->first(['name', 'description', 'event_date', 'auto_archive_at', 'status']);

        abort_if($organization === null, 404);

        $projectIds = DB::table('projects')
            ->where('organization_id', $organizationId)
            ->pluck('id');

        $totalTasks = DB::table('project_tasks')
            ->whereIn('project_id', $projectIds)
            ->count();
        $doneTasks = DB::table('project_tasks')
            ->whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->count();
        $plannedBudget = (int) DB::table('budget_lines')
            ->whereIn('project_id', $projectIds)
            ->sum('planned_amount');
        $realizedBudget = (int) DB::table('budget_lines')
            ->whereIn('project_id', $projectIds)
            ->sum('realized_amount');
        $attendanceCount = DB::table('attendance_records')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
            ->where('attendance_sessions.organization_id', $organizationId)
            ->where('attendance_records.status', 'present')
            ->count();

        $eventDate = $organization->event_date === null
            ? null
            : CarbonImmutable::parse((string) $organization->event_date);

        return [
            'organization' => [
                'name' => (string) $organization->name,
                'description' => (string) ($organization->description ?? ''),
                'status' => (string) $organization->status,
                'eventDate' => $eventDate?->toDateString(),
                'autoArchiveAt' => $organization->auto_archive_at === null ? null : (string) $organization->auto_archive_at,
                'daysToEvent' => $eventDate === null ? null : now()->startOfDay()->diffInDays($eventDate, false),
            ],
            'metrics' => [
                'projectCount' => $projectIds->count(),
                'taskCompletion' => $totalTasks === 0 ? 0 : (int) round(($doneTasks / $totalTasks) * 100),
                'pendingTasks' => max(0, $totalTasks - $doneTasks),
                'plannedBudget' => $plannedBudget,
                'realizedBudget' => $realizedBudget,
                'budgetRealization' => $plannedBudget === 0 ? 0 : (int) round(($realizedBudget / $plannedBudget) * 100),
                'attendanceCount' => $attendanceCount,
                'documentCount' => DB::table('documents')->where('organization_id', $organizationId)->count(),
            ],
            'focusTasks' => DB::table('project_tasks')
                ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('project_tasks.status', '!=', 'done')
                ->orderByRaw('project_tasks.due_at is null')
                ->orderBy('project_tasks.due_at')
                ->limit(5)
                ->get([
                    'project_tasks.title',
                    'project_tasks.status',
                    'project_tasks.due_at as dueAt',
                    'projects.name as projectName',
                ])
                ->map(static fn (object $task): array => [
                    'title' => (string) $task->title,
                    'status' => (string) $task->status,
                    'dueAt' => $task->dueAt === null ? null : (string) $task->dueAt,
                    'projectName' => (string) $task->projectName,
                ])
                ->all(),
        ];
    }
}
