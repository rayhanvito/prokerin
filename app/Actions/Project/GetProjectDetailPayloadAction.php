<?php

declare(strict_types=1);

namespace App\Actions\Project;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetProjectDetailPayloadAction
{
    /**
     * @return array{project: array{id: int, name: string, slug: string, description: string|null, status: string, progress: int, startsAt: string|null, endsAt: string|null, templateType: string|null, organization: string, lead: string|null}, metrics: array<int, array{label: string, value: string}>, tasks: array<int, array{task: string, pic: string, due: string, status: string}>}
     */
    public function execute(int $userId, ?string $slug = null): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        $projectQuery = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->leftJoin('project_templates', 'project_templates.id', '=', 'projects.project_template_id')
            ->leftJoin('users as leads', 'leads.id', '=', 'projects.project_lead_id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->select([
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.description',
                'projects.status',
                'projects.progress',
                'projects.starts_at',
                'projects.ends_at',
                'project_templates.type as template_type',
                'organizations.name as organization_name',
                'leads.name as lead_name',
            ])
            ->orderByDesc('projects.created_at');

        if (filled($slug) && $slug !== 'sample') {
            $projectQuery->where('projects.slug', $slug);
        }

        $project = $projectQuery->first();

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found for the active workspace.');
        }

        $projectId = (int) $project->id;

        return [
            'project' => [
                'id' => $projectId,
                'name' => (string) $project->name,
                'slug' => (string) $project->slug,
                'description' => is_string($project->description) ? $project->description : null,
                'status' => (string) $project->status,
                'progress' => (int) $project->progress,
                'startsAt' => is_string($project->starts_at) ? $project->starts_at : null,
                'endsAt' => is_string($project->ends_at) ? $project->ends_at : null,
                'templateType' => is_string($project->template_type) ? $project->template_type : null,
                'organization' => (string) $project->organization_name,
                'lead' => is_string($project->lead_name) ? $project->lead_name : null,
            ],
            'metrics' => [
                [
                    'label' => 'Timeline',
                    'value' => $this->dateRange($project->starts_at, $project->ends_at),
                ],
                [
                    'label' => 'Committee',
                    'value' => DB::table('project_members')->where('project_id', $projectId)->count().' PIC',
                ],
                [
                    'label' => 'RAB',
                    'value' => 'Rp'.number_format((int) DB::table('budget_lines')->where('project_id', $projectId)->sum('planned_amount'), 0, ',', '.'),
                ],
                [
                    'label' => 'Documents',
                    'value' => DB::table('documents')->where('project_id', $projectId)->count().' files',
                ],
            ],
            'tasks' => DB::table('project_tasks')
                ->leftJoin('users as pics', 'pics.id', '=', 'project_tasks.pic_user_id')
                ->where('project_tasks.project_id', $projectId)
                ->orderBy('project_tasks.due_at')
                ->limit(5)
                ->get([
                    'project_tasks.title',
                    'project_tasks.due_at',
                    'project_tasks.status',
                    'pics.name as pic_name',
                ])
                ->map(static fn (object $task): array => [
                    'task' => (string) $task->title,
                    'pic' => is_string($task->pic_name) ? $task->pic_name : '-',
                    'due' => is_string($task->due_at) ? $task->due_at : '-',
                    'status' => (string) $task->status,
                ])
                ->all(),
        ];
    }

    private function dateRange(mixed $startsAt, mixed $endsAt): string
    {
        if (! is_string($startsAt)) {
            return '-';
        }

        if (! is_string($endsAt) || $endsAt === $startsAt) {
            return $startsAt;
        }

        return "{$startsAt} - {$endsAt}";
    }
}
