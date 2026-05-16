<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetFinanceApprovalPayloadAction
{
    /**
     * @return array{approvals: array<int, array{id: int, title: string, projectName: string, category: string, amount: int, requester: string, status: string, canDecide: bool}>}
     */
    public function execute(int $actorUserId): array
    {
        $rows = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->leftJoin('users', 'users.id', '=', 'projects.project_lead_id')
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('budget_lines.status', ['review', 'approved', 'rejected'])
            ->select([
                'budget_lines.id',
                'budget_lines.name',
                'budget_lines.category',
                'budget_lines.planned_amount',
                'budget_lines.status',
                'projects.name as project_name',
                'organization_members.role',
                'users.name as requester_name',
            ])
            ->orderByRaw("case budget_lines.status when 'review' then 0 when 'rejected' then 1 else 2 end")
            ->orderBy('budget_lines.id')
            ->get();

        return [
            'approvals' => $rows
                ->map(static fn (object $row): array => [
                    'id' => (int) $row->id,
                    'title' => (string) $row->name,
                    'projectName' => (string) $row->project_name,
                    'category' => (string) $row->category,
                    'amount' => (int) $row->planned_amount,
                    'requester' => (string) ($row->requester_name ?? 'Project Lead'),
                    'status' => (string) $row->status,
                    'canDecide' => (string) $row->status === 'review'
                        && in_array((string) $row->role, ['organization_owner', 'organization_admin', 'treasurer'], true),
                ])
                ->all(),
        ];
    }
}
