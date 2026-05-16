<?php

declare(strict_types=1);

namespace App\Actions\Handover;

use Illuminate\Support\Facades\DB;

final class UpdateHandoverItemStatusAction
{
    public function execute(int $actorUserId, int $handoverItemId, string $status): void
    {
        abort_unless(in_array($status, ['pending', 'done'], true), 422);

        $item = DB::table('handover_items')
            ->join('handover_packages', 'handover_packages.id', '=', 'handover_items.package_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'handover_packages.organization_id')
            ->where('handover_items.id', $handoverItemId)
            ->where('organization_members.user_id', $actorUserId)
            ->where(function ($query) use ($actorUserId): void {
                $query->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
                    ->orWhere('handover_items.assignee_id', $actorUserId);
            })
            ->where('handover_packages.status', 'draft')
            ->first(['handover_items.id']);

        abort_if($item === null, 403);

        DB::table('handover_items')
            ->where('id', $handoverItemId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }
}
