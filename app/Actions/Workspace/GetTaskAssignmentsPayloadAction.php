<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetTaskAssignmentsPayloadAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{
     *     tasks: array<int, array{id: int, title: string, project: string, status: string, dueAt: string|null, picUserId: int|null, picName: string}>,
     *     members: array<int, array{id: int, name: string, email: string, role: string}>
     * }
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);

        return [
            'tasks' => DB::table('project_tasks')
                ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                ->leftJoin('users as pics', 'pics.id', '=', 'project_tasks.pic_user_id')
                ->where('projects.organization_id', $context->organizationId)
                ->where('projects.status', '!=', 'archived')
                ->orderByRaw('project_tasks.due_at is null')
                ->orderBy('project_tasks.due_at')
                ->orderBy('project_tasks.id')
                ->get([
                    'project_tasks.id',
                    'project_tasks.title',
                    'project_tasks.status',
                    'project_tasks.due_at',
                    'project_tasks.pic_user_id',
                    'projects.name as project_name',
                    'pics.name as pic_name',
                ])
                ->map(static fn (object $task): array => [
                    'id' => (int) $task->id,
                    'title' => (string) $task->title,
                    'project' => (string) $task->project_name,
                    'status' => (string) $task->status,
                    'dueAt' => is_string($task->due_at) ? $task->due_at : null,
                    'picUserId' => $task->pic_user_id === null ? null : (int) $task->pic_user_id,
                    'picName' => is_string($task->pic_name) ? $task->pic_name : '-',
                ])
                ->all(),
            'members' => DB::table('organization_members')
                ->join('users', 'users.id', '=', 'organization_members.user_id')
                ->where('organization_members.organization_id', $context->organizationId)
                ->where('organization_members.role', '!=', 'viewer')
                ->orderBy('users.name')
                ->get([
                    'users.id',
                    'users.name',
                    'users.email',
                    'organization_members.role',
                ])
                ->map(static fn (object $member): array => [
                    'id' => (int) $member->id,
                    'name' => (string) $member->name,
                    'email' => (string) $member->email,
                    'role' => (string) $member->role,
                ])
                ->all(),
        ];
    }
}
