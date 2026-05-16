<?php

declare(strict_types=1);

namespace App\Actions\Handover;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class InitiateHandoverPackageAction
{
    public function execute(int $actorUserId): int
    {
        $organization = $this->manageableOrganization($actorUserId);

        abort_if($organization === null, 403);

        $period = DB::table('organization_periods')
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderByDesc('starts_at')
            ->first(['id', 'name']);

        return DB::transaction(function () use ($actorUserId, $organization, $period): int {
            $existingPackageId = DB::table('handover_packages')
                ->where('organization_id', $organization->id)
                ->where('from_period_id', $period?->id)
                ->whereIn('status', ['draft', 'submitted'])
                ->orderByDesc('id')
                ->value('id');

            if ($existingPackageId !== null) {
                return (int) $existingPackageId;
            }

            $now = now();
            $snapshot = $this->snapshot((int) $organization->id);
            $packageId = (int) DB::table('handover_packages')->insertGetId([
                'organization_id' => $organization->id,
                'from_period_id' => $period?->id,
                'to_period_id' => null,
                'created_by' => $actorUserId,
                'status' => 'draft',
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'submitted_at' => null,
                'accepted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('handover_items')->insert($this->defaultItems($packageId, $snapshot, $now));

            return $packageId;
        });
    }

    private function manageableOrganization(int $actorUserId): ?object
    {
        return DB::table('organizations')
            ->join('organization_members', 'organization_members.organization_id', '=', 'organizations.id')
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->orderBy('organization_members.id')
            ->first(['organizations.id', 'organizations.name']);
    }

    /**
     * @return array<string, int|array<string, int>>
     */
    private function snapshot(int $organizationId): array
    {
        $projectStatuses = DB::table('projects')
            ->where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(static fn (mixed $total): int => (int) $total)
            ->all();

        $openTaskCount = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_tasks.status', '!=', 'done')
            ->count();

        $documentCount = DB::table('documents')
            ->where('organization_id', $organizationId)
            ->count();

        $budget = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->selectRaw('coalesce(sum(planned_amount), 0) as planned_total, coalesce(sum(realized_amount), 0) as realized_total')
            ->first();

        $outstandingLpjCount = DB::table('lpj_checklist_items')
            ->join('projects', 'projects.id', '=', 'lpj_checklist_items.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('lpj_checklist_items.is_required', true)
            ->where('lpj_checklist_items.is_complete', false)
            ->count();

        return [
            'project_statuses' => $projectStatuses,
            'open_tasks' => (int) $openTaskCount,
            'documents' => (int) $documentCount,
            'planned_budget' => (int) ($budget->planned_total ?? 0),
            'realized_budget' => (int) ($budget->realized_total ?? 0),
            'outstanding_lpj_items' => (int) $outstandingLpjCount,
        ];
    }

    /**
     * @param  array<string, int|array<string, int>>  $snapshot
     * @return array<int, array<string, mixed>>
     */
    private function defaultItems(int $packageId, array $snapshot, Carbon $now): array
    {
        return [
            [
                'package_id' => $packageId,
                'category' => 'document',
                'label' => 'Audit dokumen proker dan arsip organisasi',
                'description' => sprintf('%d dokumen tercatat sebagai bahan serah terima.', (int) $snapshot['documents']),
                'status' => 'pending',
                'assignee_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'package_id' => $packageId,
                'category' => 'role',
                'label' => 'Review role pengurus dan PIC aktif',
                'description' => sprintf('%d task masih terbuka dan perlu owner yang jelas.', (int) $snapshot['open_tasks']),
                'status' => 'pending',
                'assignee_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'package_id' => $packageId,
                'category' => 'finance',
                'label' => 'Rekonsiliasi RAB dan realisasi akhir periode',
                'description' => sprintf('Rencana Rp%s; realisasi Rp%s.', number_format((int) $snapshot['planned_budget'], 0, ',', '.'), number_format((int) $snapshot['realized_budget'], 0, ',', '.')),
                'status' => 'pending',
                'assignee_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'package_id' => $packageId,
                'category' => 'asset',
                'label' => 'Finalisasi paket arsip dan catatan LPJ',
                'description' => sprintf('%d item LPJ wajib masih belum lengkap.', (int) $snapshot['outstanding_lpj_items']),
                'status' => 'pending',
                'assignee_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
}
