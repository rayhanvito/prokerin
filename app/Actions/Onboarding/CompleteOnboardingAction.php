<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CompleteOnboardingAction
{
    public function execute(int $userId): void
    {
        $organizationId = DB::table('organization_members')
            ->where('user_id', $userId)
            ->where('role', 'organization_owner')
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId === null) {
            throw new AuthorizationException('Hanya owner organisasi yang dapat menyelesaikan onboarding.');
        }

        DB::table('organizations')
            ->where('id', $organizationId)
            ->update([
                'onboarding_completed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
