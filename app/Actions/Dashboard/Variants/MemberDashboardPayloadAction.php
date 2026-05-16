<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Variants;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class MemberDashboardPayloadAction
{
    public function execute(int $actorUserId, int $organizationId): array
    {
        return Cache::remember("dashboard:member:{$organizationId}:{$actorUserId}", 300, function () use ($actorUserId, $organizationId): array {
            $nearestDeadline = DB::table('project_tasks')
                ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('project_tasks.pic_user_id', $actorUserId)
                ->where('project_tasks.status', '!=', 'done')
                ->orderBy('project_tasks.due_at')
                ->value('project_tasks.due_at');

            return [
                'kpiMetrics' => [
                    ['label' => 'Task Aktif Saya', 'value' => DB::table('project_tasks')->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.organization_id', $organizationId)->where('project_tasks.pic_user_id', $actorUserId)->where('project_tasks.status', '!=', 'done')->count()],
                    ['label' => 'Task Selesai Bulan Ini', 'value' => DB::table('project_tasks')->join('projects', 'projects.id', '=', 'project_tasks.project_id')->where('projects.organization_id', $organizationId)->where('project_tasks.pic_user_id', $actorUserId)->where('project_tasks.status', 'done')->whereMonth('project_tasks.updated_at', now()->month)->count()],
                    ['label' => 'Proker yang Aku Ikuti', 'value' => DB::table('project_members')->join('projects', 'projects.id', '=', 'project_members.project_id')->where('projects.organization_id', $organizationId)->where('project_members.user_id', $actorUserId)->count()],
                    ['label' => 'Deadline Terdekat', 'value' => $nearestDeadline === null ? '-' : (string) $nearestDeadline],
                ],
                'myTasks' => $this->myTasks($actorUserId, $organizationId),
                'myProjects' => $this->myProjects($actorUserId, $organizationId),
                'recentNotifications' => $this->recentNotifications($actorUserId),
            ];
        });
    }

    private function myTasks(int $actorUserId, int $organizationId): array
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_tasks.pic_user_id', $actorUserId)
            ->orderBy('project_tasks.due_at')
            ->limit(10)
            ->get(['project_tasks.id', 'project_tasks.title', 'project_tasks.status', 'project_tasks.due_at', 'projects.name as project_name'])
            ->map(static fn (object $task): array => [
                'id' => (int) $task->id,
                'title' => (string) $task->title,
                'projectName' => (string) $task->project_name,
                'status' => (string) $task->status,
                'dueAt' => $task->due_at === null ? null : (string) $task->due_at,
            ])
            ->all();
    }

    private function myProjects(int $actorUserId, int $organizationId): array
    {
        return DB::table('project_members')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->leftJoin('users', 'users.id', '=', 'projects.project_lead_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_members.user_id', $actorUserId)
            ->orderBy('projects.name')
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

    private function recentNotifications(int $actorUserId): array
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $actorUserId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'type', 'data', 'read_at', 'created_at'])
            ->map(static fn (object $notification): array => [
                'id' => (string) $notification->id,
                'type' => (string) $notification->type,
                'message' => (string) data_get(json_decode((string) $notification->data, true) ?: [], 'message', 'Notifikasi baru'),
                'readAt' => $notification->read_at === null ? null : (string) $notification->read_at,
                'createdAt' => (string) $notification->created_at,
            ])
            ->all();
    }
}
