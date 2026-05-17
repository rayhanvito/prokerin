<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

final class CompleteOnboardingAction
{
    public function __construct(
        private readonly CompleteOnboardingStepAction $completeStep,
    ) {}

    public function execute(int $userId): void
    {
        $this->completeStep->execute($userId, 5);
    }
}
