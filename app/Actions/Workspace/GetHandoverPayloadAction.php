<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetHandoverPayloadAction
{
    /**
     * @return array{
     *     organization: array{id: int, name: string, periodName: string|null}|null,
     *     metrics: array<int, array{label: string, value: string, note: string}>,
     *     package: array{id: int, status: string, createdAt: string, submittedAt: string|null, acceptedAt: string|null, snapshot: array<string, mixed>}|null,
     *     items: array<int, array{id: int, category: string, label: string, description: string|null, status: string, assignee: string|null}>,
     *     canManage: bool
     * }
     */
    public function execute(int $actorUserId): array
    {
        $membership = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organizations.id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $actorUserId)
            ->orderBy('organization_members.id')
            ->first([
                'organizations.id as organization_id',
                'organizations.name as organization_name',
                'organization_periods.id as period_id',
                'organization_periods.name as period_name',
                'organization_members.role',
            ]);

        if ($membership === null) {
            return [
                'organization' => null,
                'metrics' => [],
                'package' => null,
                'items' => [],
                'canManage' => false,
            ];
        }

        $canManage = in_array((string) $membership->role, ['organization_owner', 'organization_admin'], true);
        $package = DB::table('handover_packages')
            ->where('organization_id', $membership->organization_id)
            ->where('from_period_id', $membership->period_id)
            ->orderByDesc('created_at')
            ->first();

        $items = $package === null
            ? collect()
            : DB::table('handover_items')
                ->leftJoin('users', 'users.id', '=', 'handover_items.assignee_id')
                ->where('handover_items.package_id', $package->id)
                ->orderBy('handover_items.id')
                ->get([
                    'handover_items.id',
                    'handover_items.category',
                    'handover_items.label',
                    'handover_items.description',
                    'handover_items.status',
                    'users.name as assignee_name',
                ]);

        $snapshot = $package === null
            ? $this->liveSnapshot((int) $membership->organization_id)
            : (json_decode((string) $package->snapshot, true) ?: []);

        return [
            'organization' => [
                'id' => (int) $membership->organization_id,
                'name' => (string) $membership->organization_name,
                'periodName' => $membership->period_name === null ? null : (string) $membership->period_name,
            ],
            'metrics' => $this->metrics($snapshot, $items->count()),
            'package' => $package === null ? null : [
                'id' => (int) $package->id,
                'status' => (string) $package->status,
                'createdAt' => (string) $package->created_at,
                'submittedAt' => $package->submitted_at === null ? null : (string) $package->submitted_at,
                'acceptedAt' => $package->accepted_at === null ? null : (string) $package->accepted_at,
                'snapshot' => $snapshot,
            ],
            'items' => $items
                ->map(static fn (object $item): array => [
                    'id' => (int) $item->id,
                    'category' => (string) $item->category,
                    'label' => (string) $item->label,
                    'description' => $item->description === null ? null : (string) $item->description,
                    'status' => (string) $item->status,
                    'assignee' => $item->assignee_name === null ? null : (string) $item->assignee_name,
                ])
                ->all(),
            'canManage' => $canManage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveSnapshot(int $organizationId): array
    {
        $budget = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->selectRaw('coalesce(sum(planned_amount), 0) as planned_total, coalesce(sum(realized_amount), 0) as realized_total')
            ->first();

        return [
            'open_tasks' => DB::table('project_tasks')
                ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('project_tasks.status', '!=', 'done')
                ->count(),
            'documents' => DB::table('documents')->where('organization_id', $organizationId)->count(),
            'planned_budget' => (int) ($budget->planned_total ?? 0),
            'realized_budget' => (int) ($budget->realized_total ?? 0),
            'outstanding_lpj_items' => DB::table('lpj_checklist_items')
                ->join('projects', 'projects.id', '=', 'lpj_checklist_items.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('lpj_checklist_items.is_required', true)
                ->where('lpj_checklist_items.is_complete', false)
                ->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<int, array{label: string, value: string, note: string}>
     */
    private function metrics(array $snapshot, int $itemCount): array
    {
        return [
            [
                'label' => 'Task terbuka',
                'value' => (string) ($snapshot['open_tasks'] ?? 0),
                'note' => 'Perlu owner sebelum serah terima',
            ],
            [
                'label' => 'Dokumen arsip',
                'value' => (string) ($snapshot['documents'] ?? 0),
                'note' => 'File organisasi dan proker',
            ],
            [
                'label' => 'Checklist handover',
                'value' => (string) $itemCount,
                'note' => 'Item paket yang sudah dibuat',
            ],
        ];
    }
}
