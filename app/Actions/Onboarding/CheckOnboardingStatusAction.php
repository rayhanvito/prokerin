<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use Illuminate\Support\Facades\DB;

final class CheckOnboardingStatusAction
{
    /**
     * @return array{
     *     show: bool,
     *     organizationId: int|null,
     *     organizationName: string|null,
     *     steps: array<int, array{key: string, label: string, complete: bool, href: string|null}>,
     *     completeUrl: string,
     * }|null
     */
    public function execute(int $userId): ?array
    {
        $owner = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', $userId)
            ->where('organization_members.role', 'organization_owner')
            ->orderBy('organizations.id')
            ->first([
                'organizations.id',
                'organizations.name',
                'organizations.onboarding_completed_at',
            ]);

        if ($owner === null) {
            return null;
        }

        if ($owner->onboarding_completed_at !== null) {
            return [
                'show' => false,
                'organizationId' => (int) $owner->id,
                'organizationName' => (string) $owner->name,
                'steps' => [],
                'completeUrl' => route('onboarding.complete', absolute: false),
            ];
        }

        $organizationId = (int) $owner->id;

        $hasPeriod = DB::table('organization_periods')
            ->where('organization_id', $organizationId)
            ->exists();

        $hasInvite = DB::table('organization_invitations')
            ->where('organization_id', $organizationId)
            ->exists()
            || DB::table('organization_members')
                ->where('organization_id', $organizationId)
                ->where('user_id', '!=', $userId)
                ->exists();

        $hasProject = DB::table('projects')
            ->where('organization_id', $organizationId)
            ->exists();

        $hasBudgetLine = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->exists();

        $steps = [
            [
                'key' => 'period',
                'label' => 'Buat periode aktif',
                'complete' => $hasPeriod,
                'href' => route('organization.periods', absolute: false),
            ],
            [
                'key' => 'invite',
                'label' => 'Undang anggota inti',
                'complete' => $hasInvite,
                'href' => route('organization.setup', absolute: false),
            ],
            [
                'key' => 'project',
                'label' => 'Buat proker pertama',
                'complete' => $hasProject,
                'href' => route('proker.create', absolute: false),
            ],
            [
                'key' => 'budget',
                'label' => 'Setup RAB awal',
                'complete' => $hasBudgetLine,
                'href' => route('finance.budget-draft', absolute: false),
            ],
            [
                'key' => 'preview',
                'label' => 'Lihat dashboard',
                'complete' => false,
                'href' => route('dashboard', absolute: false),
            ],
        ];

        return [
            'show' => true,
            'organizationId' => $organizationId,
            'organizationName' => (string) $owner->name,
            'steps' => $steps,
            'completeUrl' => route('onboarding.complete', absolute: false),
        ];
    }
}
