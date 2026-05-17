<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetProkerIndexPayloadAction
{
    /**
     * @return array{filters: array{search: string, status: string, period: string}, metrics: array<int, array{label: string, value: string, note: string}>, projects: array<int, array{id: int, name: string, slug: string, description: string|null, status: string, progress: int, startsAt: string|null, endsAt: string|null, lead: string, memberCount: int}>}
     */
    public function execute(
        int $actorUserId,
        ?int $preferredOrganizationId = null,
        ?string $search = null,
        ?string $status = null,
        ?string $period = null,
    ): array {
        $organizationId = $this->activeOrganizationId($actorUserId, $preferredOrganizationId);
        $normalizedSearch = trim((string) $search);
        $normalizedStatus = filled($status) ? (string) $status : 'all';
        $normalizedPeriod = filled($period) ? (string) $period : 'all';

        if ($organizationId === null) {
            return [
                'filters' => [
                    'search' => $normalizedSearch,
                    'status' => $normalizedStatus,
                    'period' => $normalizedPeriod,
                ],
                'metrics' => [
                    ['label' => 'Total Proker', 'value' => '0', 'note' => 'Belum ada organisasi aktif'],
                    ['label' => 'Review', 'value' => '0', 'note' => 'Menunggu approval'],
                    ['label' => 'Ready', 'value' => '0', 'note' => 'Progress 50%+'],
                ],
                'projects' => [],
            ];
        }

        $baseQuery = DB::table('projects')
            ->where('projects.organization_id', $organizationId);

        $projects = (clone $baseQuery)
            ->leftJoin('users as leads', 'leads.id', '=', 'projects.project_lead_id')
            ->leftJoin('organization_periods', 'organization_periods.id', '=', 'projects.organization_period_id')
            ->when($normalizedStatus !== 'all', static function ($query) use ($normalizedStatus): void {
                $query->where('projects.status', $normalizedStatus);
            })
            ->when($normalizedPeriod !== 'all', static function ($query) use ($normalizedPeriod): void {
                $query->where('organization_periods.name', $normalizedPeriod);
            })
            ->when($normalizedSearch !== '', static function ($query) use ($normalizedSearch): void {
                $query->where(function ($nested) use ($normalizedSearch): void {
                    $nested->where('projects.name', 'like', "%{$normalizedSearch}%")
                        ->orWhere('projects.description', 'like', "%{$normalizedSearch}%");
                });
            })
            ->orderByDesc('projects.created_at')
            ->get([
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.description',
                'projects.status',
                'projects.progress',
                'projects.starts_at',
                'projects.ends_at',
                'leads.name as lead_name',
            ]);

        $memberCounts = DB::table('project_members')
            ->whereIn('project_id', $projects->pluck('id'))
            ->select('project_id', DB::raw('count(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        return [
            'filters' => [
                'search' => $normalizedSearch,
                'status' => $normalizedStatus,
                'period' => $normalizedPeriod,
            ],
            'metrics' => [
                ['label' => 'Total Proker', 'value' => (string) (clone $baseQuery)->count(), 'note' => 'Organisasi aktif'],
                ['label' => 'Review', 'value' => (string) (clone $baseQuery)->whereIn('status', ['proposal_review', 'rab_approval', 'lpj_review'])->count(), 'note' => 'Menunggu approval'],
                ['label' => 'Ready', 'value' => (string) (clone $baseQuery)->where('progress', '>=', 50)->count(), 'note' => 'Progress 50%+'],
            ],
            'projects' => $projects
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                    'slug' => (string) $project->slug,
                    'description' => is_string($project->description) ? $project->description : null,
                    'status' => (string) $project->status,
                    'progress' => (int) $project->progress,
                    'startsAt' => is_string($project->starts_at) ? $project->starts_at : null,
                    'endsAt' => is_string($project->ends_at) ? $project->ends_at : null,
                    'lead' => is_string($project->lead_name) ? $project->lead_name : '-',
                    'memberCount' => (int) ($memberCounts[(int) $project->id] ?? 0),
                ])
                ->all(),
        ];
    }

    private function activeOrganizationId(int $actorUserId, ?int $preferredOrganizationId): ?int
    {
        $membership = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->when($preferredOrganizationId !== null, static function ($query) use ($preferredOrganizationId): void {
                $query->where('organization_id', $preferredOrganizationId);
            })
            ->orderBy('id')
            ->first(['organization_id']);

        if ($membership !== null) {
            return (int) $membership->organization_id;
        }

        if ($preferredOrganizationId === null) {
            return null;
        }

        $fallback = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->orderBy('id')
            ->value('organization_id');

        return $fallback === null ? null : (int) $fallback;
    }
}
