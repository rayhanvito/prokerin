<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\DTOs\Workspace\ActiveOrganizationContextData;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class GetActiveOrganizationContextAction
{
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): ActiveOrganizationContextData
    {
        $query = DB::table('organization_members')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organization_members.organization_id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $actorUserId)
            ->orderBy('organization_members.id');

        if ($preferredOrganizationId !== null) {
            $query->where('organization_members.organization_id', $preferredOrganizationId);
        }

        $context = $query->first([
            'organization_members.organization_id',
            'organization_members.role',
            'organization_periods.id as active_period_id',
        ]);

        if ($context === null && $preferredOrganizationId !== null) {
            $context = DB::table('organization_members')
                ->leftJoin('organization_periods', function ($join): void {
                    $join->on('organization_periods.organization_id', '=', 'organization_members.organization_id')
                        ->where('organization_periods.is_active', true);
                })
                ->where('organization_members.user_id', $actorUserId)
                ->orderBy('organization_members.id')
                ->first([
                    'organization_members.organization_id',
                    'organization_members.role',
                    'organization_periods.id as active_period_id',
                ]);
        }

        if ($context === null) {
            throw new HttpException(409, 'No active organization for user');
        }

        return new ActiveOrganizationContextData(
            organizationId: (int) $context->organization_id,
            role: (string) $context->role,
            activePeriodId: $context->active_period_id === null ? null : (int) $context->active_period_id,
        );
    }
}
