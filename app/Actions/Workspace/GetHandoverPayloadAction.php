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
     *     package: array{id: int, status: string, createdAt: string, submittedAt: string|null, acceptedAt: string|null, acceptedByName: string|null, toPeriodId: int|null, toPeriodName: string|null, incomingOwnerId: int|null, incomingOwnerName: string|null, canAccept: bool, snapshot: array<string, mixed>}|null,
     *     items: array<int, array{id: int, category: string, label: string, description: string|null, status: string, assignee: string|null}>,
     *     transitionOptions: array{periods: array<int, array{id: int, name: string}>, incomingOwners: array<int, array{id: int, name: string}>},
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
                'organization_members.user_id',
            ]);

        if ($membership === null) {
            return [
                'organization' => null,
                'metrics' => [],
                'package' => null,
                'items' => [],
                'transitionOptions' => [
                    'periods' => [],
                    'incomingOwners' => [],
                ],
                'canManage' => false,
            ];
        }

        $canManage = in_array((string) $membership->role, ['organization_owner', 'organization_admin'], true);
        $package = DB::table('handover_packages')
            ->leftJoin('organization_periods as to_periods', 'to_periods.id', '=', 'handover_packages.to_period_id')
            ->leftJoin('users as incoming_owners', 'incoming_owners.id', '=', 'handover_packages.incoming_owner_id')
            ->leftJoin('users as accepted_by_users', 'accepted_by_users.id', '=', 'handover_packages.accepted_by_user_id')
            ->where('handover_packages.organization_id', $membership->organization_id)
            ->where('handover_packages.from_period_id', $membership->period_id)
            ->orderByDesc('handover_packages.created_at')
            ->first([
                'handover_packages.*',
                'to_periods.name as to_period_name',
                'incoming_owners.name as incoming_owner_name',
                'accepted_by_users.name as accepted_by_name',
            ]);

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
                'acceptedByName' => $package->accepted_by_name === null ? null : (string) $package->accepted_by_name,
                'toPeriodId' => $package->to_period_id === null ? null : (int) $package->to_period_id,
                'toPeriodName' => $package->to_period_name === null ? null : (string) $package->to_period_name,
                'incomingOwnerId' => $package->incoming_owner_id === null ? null : (int) $package->incoming_owner_id,
                'incomingOwnerName' => $package->incoming_owner_name === null ? null : (string) $package->incoming_owner_name,
                'canAccept' => $this->canAcceptPackage((int) $membership->user_id, (string) $membership->role, $package),
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
            'transitionOptions' => [
                'periods' => $this->periodOptions((int) $membership->organization_id, $membership->period_id === null ? null : (int) $membership->period_id),
                'incomingOwners' => $this->incomingOwnerOptions((int) $membership->organization_id),
            ],
            'canManage' => $canManage,
        ];
    }

    private function canAcceptPackage(int $actorUserId, string $actorRole, object $package): bool
    {
        if ((string) $package->status !== 'submitted') {
            return false;
        }

        if ($package->incoming_owner_id === null) {
            return in_array($actorRole, ['organization_owner', 'organization_admin'], true);
        }

        return (int) $package->incoming_owner_id === $actorUserId && $actorRole === 'organization_owner';
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function periodOptions(int $organizationId, ?int $currentPeriodId): array
    {
        return DB::table('organization_periods')
            ->where('organization_id', $organizationId)
            ->when($currentPeriodId !== null, fn ($query) => $query->where('id', '!=', $currentPeriodId))
            ->orderByDesc('starts_at')
            ->get(['id', 'name'])
            ->map(static fn (object $period): array => [
                'id' => (int) $period->id,
                'name' => (string) $period->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function incomingOwnerOptions(int $organizationId): array
    {
        return DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $organizationId)
            ->where('organization_members.role', 'organization_owner')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name'])
            ->map(static fn (object $user): array => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
            ])
            ->all();
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
