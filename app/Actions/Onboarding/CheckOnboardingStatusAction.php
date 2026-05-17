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
     *     currentStep: int,
     *     completedSteps: array<int, int>,
     *     steps: array<int, array{key: string, label: string, complete: bool, href: string|null}>,
     *     completeUrl: string,
     *     stepCompleteUrl: string,
     *     skipUrl: string,
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
                'organizations.onboarding_step',
                'organizations.onboarding_skipped',
            ]);

        if ($owner === null) {
            return null;
        }

        if ($owner->onboarding_completed_at !== null || (bool) $owner->onboarding_skipped) {
            return [
                'show' => false,
                'organizationId' => (int) $owner->id,
                'organizationName' => (string) $owner->name,
                'currentStep' => 5,
                'completedSteps' => [1, 2, 3, 4, 5],
                'steps' => [],
                'completeUrl' => route('onboarding.complete', absolute: false),
                'stepCompleteUrl' => route('onboarding.steps.complete', ['step' => 5], false),
                'skipUrl' => route('onboarding.skip', absolute: false),
            ];
        }

        $organizationId = (int) $owner->id;
        $savedStep = max(1, min(5, (int) ($owner->onboarding_step ?? 1)));

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
                'complete' => $hasPeriod || $savedStep > 1,
                'href' => route('organization.periods', absolute: false),
            ],
            [
                'key' => 'invite',
                'label' => 'Undang anggota inti',
                'complete' => $hasInvite || $savedStep > 2,
                'href' => route('organization.setup', absolute: false),
            ],
            [
                'key' => 'project',
                'label' => 'Buat proker pertama',
                'complete' => $hasProject || $savedStep > 3,
                'href' => route('proker.create', absolute: false),
            ],
            [
                'key' => 'budget',
                'label' => 'Setup RAB awal',
                'complete' => $hasBudgetLine || $savedStep > 4,
                'href' => route('finance.budget-draft', absolute: false),
            ],
            [
                'key' => 'preview',
                'label' => 'Lihat dashboard',
                'complete' => $savedStep > 5,
                'href' => route('dashboard', absolute: false),
            ],
        ];
        $completedSteps = collect($steps)
            ->map(fn (array $step, int $index): ?int => $step['complete'] ? $index + 1 : null)
            ->filter()
            ->values()
            ->all();
        $currentStep = collect($steps)
            ->search(fn (array $step): bool => ! $step['complete']);

        $currentStep = $currentStep === false ? 5 : ((int) $currentStep) + 1;

        return [
            'show' => true,
            'organizationId' => $organizationId,
            'organizationName' => (string) $owner->name,
            'currentStep' => max($currentStep, $savedStep),
            'completedSteps' => $completedSteps,
            'steps' => $steps,
            'completeUrl' => route('onboarding.complete', absolute: false),
            'stepCompleteUrl' => route('onboarding.steps.complete', ['step' => '__STEP__'], false),
            'skipUrl' => route('onboarding.skip', absolute: false),
        ];
    }
}
