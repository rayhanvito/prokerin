<?php

declare(strict_types=1);

namespace App\Actions\Handover;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignHandoverTransitionAction
{
    /**
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $handoverPackageId, ?int $toPeriodId, ?int $incomingOwnerId): void
    {
        $package = DB::table('handover_packages')
            ->join('organization_members', 'organization_members.organization_id', '=', 'handover_packages.organization_id')
            ->where('handover_packages.id', $handoverPackageId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->first([
                'handover_packages.id',
                'handover_packages.organization_id',
                'handover_packages.from_period_id',
                'handover_packages.status',
            ]);

        abort_if($package === null, 403);
        abort_unless(in_array((string) $package->status, ['draft', 'submitted'], true), 422);

        if ($toPeriodId !== null) {
            $periodExists = DB::table('organization_periods')
                ->where('id', $toPeriodId)
                ->where('organization_id', $package->organization_id)
                ->where('id', '!=', $package->from_period_id)
                ->exists();

            if (! $periodExists) {
                throw ValidationException::withMessages([
                    'to_period_id' => 'Periode tujuan handover tidak valid untuk organisasi ini.',
                ]);
            }
        }

        if ($incomingOwnerId !== null) {
            $ownerExists = DB::table('organization_members')
                ->where('organization_id', $package->organization_id)
                ->where('user_id', $incomingOwnerId)
                ->where('role', 'organization_owner')
                ->exists();

            if (! $ownerExists) {
                throw ValidationException::withMessages([
                    'incoming_owner_id' => 'Penerima handover harus berperan organization_owner di organisasi ini.',
                ]);
            }
        }

        DB::table('handover_packages')
            ->where('id', $handoverPackageId)
            ->update([
                'to_period_id' => $toPeriodId,
                'incoming_owner_id' => $incomingOwnerId,
                'updated_at' => now(),
            ]);
    }
}
