<?php

declare(strict_types=1);

namespace App\Actions\Handover;

use Illuminate\Support\Facades\DB;

final class UpdateHandoverPackageStatusAction
{
    public function execute(int $actorUserId, int $handoverPackageId, string $status): void
    {
        abort_unless(in_array($status, ['submitted', 'accepted'], true), 422);

        $package = DB::table('handover_packages')
            ->join('organization_members', 'organization_members.organization_id', '=', 'handover_packages.organization_id')
            ->where('handover_packages.id', $handoverPackageId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->first([
                'handover_packages.id',
                'handover_packages.status',
            ]);

        abort_if($package === null, 403);

        if ($status === 'submitted') {
            abort_unless((string) $package->status === 'draft', 422);
            abort_unless($this->allItemsComplete($handoverPackageId), 422);

            DB::table('handover_packages')
                ->where('id', $handoverPackageId)
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);

            return;
        }

        abort_unless((string) $package->status === 'submitted', 422);

        DB::table('handover_packages')
            ->where('id', $handoverPackageId)
            ->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function allItemsComplete(int $handoverPackageId): bool
    {
        return DB::table('handover_items')
            ->where('package_id', $handoverPackageId)
            ->where('status', '!=', 'done')
            ->doesntExist();
    }
}
