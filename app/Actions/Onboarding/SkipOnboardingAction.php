<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class SkipOnboardingAction
{
    public function execute(int $actorUserId): void
    {
        $organizationId = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('role', 'organization_owner')
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId === null) {
            throw new AuthorizationException('Hanya owner organisasi yang dapat melewati onboarding.');
        }

        DB::table('organizations')
            ->where('id', $organizationId)
            ->update([
                'onboarding_skipped' => true,
                'onboarding_completed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
