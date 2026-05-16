<?php

declare(strict_types=1);

namespace App\Actions\Campus;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CampusDashboardPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $actorUserId): array
    {
        $campus = DB::table('campuses')
            ->join('users', 'users.id', '=', 'campuses.admin_user_id')
            ->where('campuses.admin_user_id', $actorUserId)
            ->first([
                'campuses.id',
                'campuses.name',
                'campuses.domain',
                'users.name as admin_name',
            ]);

        if ($campus === null) {
            throw new AuthorizationException('Anda tidak memiliki akses dashboard kampus.');
        }

        $organizationIds = DB::table('campus_organization_links')
            ->where('campus_id', (int) $campus->id)
            ->pluck('organization_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values();

        return [
            'campus' => [
                'id' => (int) $campus->id,
                'name' => (string) $campus->name,
                'domain' => (string) $campus->domain,
                'adminName' => (string) $campus->admin_name,
            ],
            'metrics' => $this->metrics($organizationIds),
            'organizations' => $this->organizationRows($organizationIds),
            'projectStatusBreakdown' => $this->projectStatusBreakdown($organizationIds),
            'recentProjects' => $this->recentProjects($organizationIds),
        ];
    }

    /**
     * @param  Collection<int, int>  $organizationIds
     * @return array<int, array{label: string, value: string|int}>
     */
    private function metrics(Collection $organizationIds): array
    {
        if ($organizationIds->isEmpty()) {
            return [
                ['label' => 'Organisasi Terhubung', 'value' => 0],
                ['label' => 'Total Proker', 'value' => 0],
                ['label' => 'LPJ Selesai', 'value' => '0%'],
                ['label' => 'Total Anggota', 'value' => 0],
            ];
        }

        $projectCount = DB::table('projects')
            ->whereIn('organization_id', $organizationIds)
            ->count();
        $completedProjectCount = DB::table('projects')
            ->whereIn('organization_id', $organizationIds)
            ->where('status', 'completed')
            ->count();
        $lpjRate = $projectCount === 0 ? 0 : (int) round(($completedProjectCount / $projectCount) * 100);

        return [
            ['label' => 'Organisasi Terhubung', 'value' => $organizationIds->count()],
            ['label' => 'Total Proker', 'value' => $projectCount],
            ['label' => 'LPJ Selesai', 'value' => $lpjRate.'%'],
            [
                'label' => 'Total Anggota',
                'value' => DB::table('organization_members')
                    ->whereIn('organization_id', $organizationIds)
                    ->distinct('user_id')
                    ->count('user_id'),
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $organizationIds
     * @return array<int, array<string, mixed>>
     */
    private function organizationRows(Collection $organizationIds): array
    {
        if ($organizationIds->isEmpty()) {
            return [];
        }

        return DB::table('organizations')
            ->whereIn('organizations.id', $organizationIds)
            ->orderBy('organizations.name')
            ->get([
                'organizations.id',
                'organizations.name',
                'organizations.slug',
                'organizations.status',
                'organizations.plan_tier',
            ])
            ->map(fn (object $organization): array => [
                'id' => (int) $organization->id,
                'name' => (string) $organization->name,
                'slug' => (string) $organization->slug,
                'status' => (string) $organization->status,
                'planTier' => (string) $organization->plan_tier,
                'memberCount' => DB::table('organization_members')
                    ->where('organization_id', (int) $organization->id)
                    ->distinct('user_id')
                    ->count('user_id'),
                'projectCount' => DB::table('projects')
                    ->where('organization_id', (int) $organization->id)
                    ->count(),
                'activeProjectCount' => DB::table('projects')
                    ->where('organization_id', (int) $organization->id)
                    ->whereNotIn('status', ['completed', 'archived'])
                    ->count(),
                'completedProjectCount' => DB::table('projects')
                    ->where('organization_id', (int) $organization->id)
                    ->where('status', 'completed')
                    ->count(),
                'rabTotal' => (int) DB::table('budget_lines')
                    ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                    ->where('projects.organization_id', (int) $organization->id)
                    ->sum('budget_lines.planned_amount'),
                'realizationTotal' => (int) DB::table('budget_lines')
                    ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                    ->where('projects.organization_id', (int) $organization->id)
                    ->sum('budget_lines.realized_amount'),
                'documentCount' => DB::table('documents')
                    ->where('organization_id', (int) $organization->id)
                    ->count(),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, int>  $organizationIds
     * @return array<int, array{status: string, count: int}>
     */
    private function projectStatusBreakdown(Collection $organizationIds): array
    {
        if ($organizationIds->isEmpty()) {
            return [];
        }

        return DB::table('projects')
            ->whereIn('organization_id', $organizationIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(static fn (object $row): array => [
                'status' => (string) $row->status,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, int>  $organizationIds
     * @return array<int, array{id: int, name: string, organizationName: string, status: string, progress: int}>
     */
    private function recentProjects(Collection $organizationIds): array
    {
        if ($organizationIds->isEmpty()) {
            return [];
        }

        return DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->orderByDesc('projects.updated_at')
            ->limit(6)
            ->get([
                'projects.id',
                'projects.name',
                'projects.status',
                'projects.progress',
                'organizations.name as organization_name',
            ])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
                'organizationName' => (string) $project->organization_name,
                'status' => (string) $project->status,
                'progress' => (int) $project->progress,
            ])
            ->all();
    }
}
