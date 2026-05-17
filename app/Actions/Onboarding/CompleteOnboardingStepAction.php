<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CompleteOnboardingStepAction
{
    public function execute(int $actorUserId, int $step): void
    {
        if ($step < 1 || $step > 5) {
            throw ValidationException::withMessages([
                'step' => 'Step onboarding tidak valid.',
            ]);
        }

        $organizationId = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('role', 'organization_owner')
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId === null) {
            throw new AuthorizationException('Hanya owner organisasi yang dapat menyelesaikan onboarding.');
        }

        $nextStep = min(5, $step + 1);
        $payload = [
            'onboarding_step' => $nextStep,
            'updated_at' => now(),
        ];

        if ($step === 5) {
            $payload['onboarding_completed_at'] = now();
        }

        DB::table('organizations')
            ->where('id', $organizationId)
            ->update($payload);
    }
}
