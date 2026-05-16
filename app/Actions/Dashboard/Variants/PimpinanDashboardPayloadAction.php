<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Variants;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PimpinanDashboardPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $actorUserId, int $organizationId): array
    {
        return Cache::remember("dashboard:pimpinan:{$organizationId}:{$actorUserId}", 300, function () use ($organizationId): array {
            $rabTotal = (int) DB::table('budget_lines')
                ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                ->where('projects.organization_id', $organizationId)
                ->sum('budget_lines.planned_amount');
            $realizedTotal = (int) DB::table('budget_lines')
                ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                ->where('projects.organization_id', $organizationId)
                ->sum('budget_lines.realized_amount');

            return [
                'kpiMetrics' => [
                    $this->metric('Proker Aktif', DB::table('projects')->where('organization_id', $organizationId)->whereNotIn('status', ['completed', 'archived'])->count()),
                    $this->metric('Rata-rata Progress', (int) DB::table('projects')->where('organization_id', $organizationId)->avg('progress').'%'),
                    $this->metric('Total Anggota', DB::table('organization_members')->where('organization_id', $organizationId)->count()),
                    $this->metric('Sisa Anggaran', 'Rp '.number_format(max(0, $rabTotal - $realizedTotal), 0, ',', '.')),
                ],
                'approvalQueue' => $this->approvalQueue($organizationId),
                'priorityProjects' => $this->priorityProjects($organizationId),
                'financeSummary' => $this->financeSummary($organizationId),
                'upcomingMeetings' => $this->upcomingMeetings($organizationId),
                'memberActivity' => $this->memberActivity($organizationId),
            ];
        });
    }

    /**
     * @return array{label: string, value: string|int}
     */
    private function metric(string $label, string|int $value): array
    {
        return ['label' => $label, 'value' => $value];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function approvalQueue(int $organizationId): array
    {
        return DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->leftJoin('users', 'users.id', '=', 'approval_instances.submitted_by_user_id')
            ->where('approval_workflow_definitions.organization_id', $organizationId)
            ->where('approval_instances.status', 'pending')
            ->orderByDesc('approval_instances.created_at')
            ->limit(8)
            ->get(['approval_instances.id', 'approval_instances.subject_type', 'approval_instances.created_at', 'approval_workflow_definitions.workflow_type', 'users.name as submitter_name'])
            ->map(static fn (object $row): array => [
                'id' => (int) $row->id,
                'type' => (string) $row->workflow_type,
                'prokerName' => (string) $row->subject_type,
                'submittedBy' => (string) ($row->submitter_name ?? 'Sistem'),
                'submittedAt' => (string) $row->created_at,
                'approvalInstanceId' => (int) $row->id,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function priorityProjects(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('users', 'users.id', '=', 'projects.project_lead_id')
            ->where('projects.organization_id', $organizationId)
            ->orderBy('projects.ends_at')
            ->limit(3)
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function financeSummary(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('budget_lines', 'budget_lines.project_id', '=', 'projects.id')
            ->where('projects.organization_id', $organizationId)
            ->groupBy('projects.id', 'projects.name')
            ->get([
                'projects.name',
                DB::raw('coalesce(sum(budget_lines.planned_amount), 0) as rab_total'),
                DB::raw('coalesce(sum(budget_lines.realized_amount), 0) as realisasi_total'),
            ])
            ->map(static function (object $row): array {
                $rab = (int) $row->rab_total;
                $realized = (int) $row->realisasi_total;

                return [
                    'prokerName' => (string) $row->name,
                    'rabTotal' => $rab,
                    'realisasiTotal' => $realized,
                    'usagePercentage' => $rab === 0 ? 0 : (int) round(($realized / $rab) * 100),
                    'isOverBudget' => $rab > 0 && ($realized / $rab) >= 0.9,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function upcomingMeetings(int $organizationId): array
    {
        return DB::table('meetings')
            ->where('organization_id', $organizationId)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get(['id', 'title', 'starts_at', 'location'])
            ->map(static fn (object $meeting): array => [
                'id' => (int) $meeting->id,
                'title' => (string) $meeting->title,
                'startsAt' => (string) $meeting->starts_at,
                'location' => (string) ($meeting->location ?? '-'),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function memberActivity(int $organizationId): array
    {
        return DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $organizationId)
            ->orderBy('users.name')
            ->limit(5)
            ->get(['users.name', 'organization_members.role', 'organization_members.updated_at'])
            ->map(static fn (object $member): array => [
                'name' => (string) $member->name,
                'role' => (string) $member->role,
                'lastActivityAt' => (string) $member->updated_at,
            ])
            ->all();
    }
}
