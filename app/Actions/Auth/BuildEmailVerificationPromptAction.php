<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\EmailVerificationPromptData;

final class BuildEmailVerificationPromptAction
{
    public function execute(bool $isVerified, ?string $status): EmailVerificationPromptData
    {
        $wasLinkSent = $status === 'verification-link-sent';

        return new EmailVerificationPromptData(
            isVerified: $isVerified,
            wasVerificationLinkSent: $wasLinkSent,
            message: $wasLinkSent
                ? 'A new verification link has been sent to your email address.'
                : 'Verify your email address before continuing to your Prokerin workspace.',
        );
    }
}
