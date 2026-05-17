<?php

declare(strict_types=1);

namespace App\Actions\Search;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use Illuminate\Support\Facades\DB;

final class GlobalSearchAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @return array{query: string, projects: array<int, array<string, mixed>>, tasks: array<int, array<string, mixed>>, documents: array<int, array<string, mixed>>, meetings: array<int, array<string, mixed>>, members: array<int, array<string, mixed>>}
     */
    public function execute(int $actorUserId, string $query): array
    {
        $normalizedQuery = trim($query);

        if (mb_strlen($normalizedQuery) < 2) {
            return $this->emptyResult($normalizedQuery);
        }

        $context = $this->activeOrganizationContext->execute($actorUserId);
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $normalizedQuery).'%';

        return [
            'query' => $normalizedQuery,
            'projects' => $this->projects($context->organizationId, $like),
            'tasks' => $this->tasks($context->organizationId, $like),
            'documents' => $this->documents($actorUserId, $context->organizationId, $like),
            'meetings' => $this->meetings($context->organizationId, $like),
            'members' => $this->members($context->organizationId, $like),
        ];
    }

    /**
     * @return array{query: string, projects: array<int, mixed>, tasks: array<int, mixed>, documents: array<int, mixed>, meetings: array<int, mixed>, members: array<int, mixed>}
     */
    private function emptyResult(string $query): array
    {
        return [
            'query' => $query,
            'projects' => [],
            'tasks' => [],
            'documents' => [],
            'meetings' => [],
            'members' => [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function projects(int $organizationId, string $like): array
    {
        return DB::table('projects')
            ->leftJoin('organization_periods', 'organization_periods.id', '=', 'projects.organization_period_id')
            ->where('projects.organization_id', $organizationId)
            ->whereNull('projects.deleted_at')
            ->where(function ($query) use ($like): void {
                $query->where('projects.name', 'like', $like)
                    ->orWhere('projects.description', 'like', $like)
                    ->orWhere('projects.status', 'like', $like)
                    ->orWhere('organization_periods.name', 'like', $like);
            })
            ->orderByDesc('projects.updated_at')
            ->limit(5)
            ->get(['projects.id', 'projects.name', 'projects.status', 'organization_periods.name as period_name'])
            ->map(fn (object $project): array => $this->result(
                type: 'project',
                id: (int) $project->id,
                title: (string) $project->name,
                subtitle: trim((string) $project->status.' · '.($project->period_name ?? 'Tanpa periode'), ' ·'),
                href: route('proker.detail', ['project' => (int) $project->id], false),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tasks(int $organizationId, string $like): array
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->leftJoin('users', 'users.id', '=', 'project_tasks.pic_user_id')
            ->where('projects.organization_id', $organizationId)
            ->whereNull('projects.deleted_at')
            ->where(function ($query) use ($like): void {
                $query->where('project_tasks.title', 'like', $like)
                    ->orWhere('project_tasks.division', 'like', $like)
                    ->orWhere('projects.name', 'like', $like)
                    ->orWhere('users.name', 'like', $like);
            })
            ->orderByRaw('project_tasks.completed_at is null desc')
            ->orderBy('project_tasks.due_at')
            ->limit(5)
            ->get(['project_tasks.id', 'project_tasks.title', 'projects.name as project_name', 'users.name as assignee_name'])
            ->map(fn (object $task): array => $this->result(
                type: 'task',
                id: (int) $task->id,
                title: (string) $task->title,
                subtitle: trim((string) ($task->project_name ?? 'Tanpa proker').' · '.($task->assignee_name ?? 'Belum ada PIC'), ' ·'),
                href: route('tasks.kanban', absolute: false),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function documents(int $actorUserId, int $organizationId, string $like): array
    {
        return DB::table('documents')
            ->leftJoin('projects', 'projects.id', '=', 'documents.project_id')
            ->where('documents.organization_id', $organizationId)
            ->where(function ($query) use ($actorUserId): void {
                $query->whereIn('documents.visibility', ['organization', 'project', 'public'])
                    ->orWhere('documents.owner_user_id', $actorUserId);
            })
            ->where(function ($query) use ($like): void {
                $query->where('documents.name', 'like', $like)
                    ->orWhere('documents.folder', 'like', $like)
                    ->orWhere('documents.visibility', 'like', $like)
                    ->orWhere('projects.name', 'like', $like);
            })
            ->orderByDesc('documents.updated_at')
            ->limit(5)
            ->get(['documents.id', 'documents.name', 'documents.folder', 'documents.visibility', 'projects.name as project_name'])
            ->map(fn (object $document): array => $this->result(
                type: 'document',
                id: (int) $document->id,
                title: (string) $document->name,
                subtitle: trim((string) $document->folder.' · '.($document->project_name ?? $document->visibility), ' ·'),
                href: route('documents.download', ['document' => (int) $document->id], false),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function meetings(int $organizationId, string $like): array
    {
        return DB::table('meetings')
            ->leftJoin('projects', 'projects.id', '=', 'meetings.project_id')
            ->where('meetings.organization_id', $organizationId)
            ->where(function ($query) use ($like): void {
                $query->where('meetings.title', 'like', $like)
                    ->orWhere('meetings.agenda', 'like', $like)
                    ->orWhere('meetings.location', 'like', $like)
                    ->orWhere('projects.name', 'like', $like);
            })
            ->orderByDesc('meetings.starts_at')
            ->limit(5)
            ->get(['meetings.id', 'meetings.title', 'meetings.starts_at', 'projects.name as project_name'])
            ->map(fn (object $meeting): array => $this->result(
                type: 'meeting',
                id: (int) $meeting->id,
                title: (string) $meeting->title,
                subtitle: trim((string) ($meeting->project_name ?? 'Rapat organisasi').' · '.(string) $meeting->starts_at, ' ·'),
                href: route('meetings.index', absolute: false),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function members(int $organizationId, string $like): array
    {
        return DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $organizationId)
            ->where(function ($query) use ($like): void {
                $query->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('organization_members.role', 'like', $like);
            })
            ->orderBy('users.name')
            ->limit(5)
            ->get(['users.id', 'users.name', 'users.email', 'organization_members.role'])
            ->map(fn (object $member): array => $this->result(
                type: 'member',
                id: (int) $member->id,
                title: (string) $member->name,
                subtitle: (string) $member->role.' · '.(string) $member->email,
                href: route('organization.setup', absolute: false),
            ))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function result(string $type, int $id, string $title, string $subtitle, string $href): array
    {
        return compact('type', 'id', 'title', 'subtitle', 'href');
    }
}
