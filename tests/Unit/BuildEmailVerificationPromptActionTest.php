<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Auth\BuildEmailVerificationPromptAction;
use PHPUnit\Framework\TestCase;

final class BuildEmailVerificationPromptActionTest extends TestCase
{
    public function test_it_builds_default_email_verification_prompt(): void
    {
        $prompt = (new BuildEmailVerificationPromptAction)->execute(false, null);

        $this->assertFalse($prompt->isVerified);
        $this->assertFalse($prompt->wasVerificationLinkSent);
        $this->assertSame(
            'Verify your email address before continuing to your Prokerin workspace.',
            $prompt->message,
        );
    }

    public function test_it_builds_link_sent_prompt(): void
    {
        $prompt = (new BuildEmailVerificationPromptAction)->execute(false, 'verification-link-sent');

        $this->assertTrue($prompt->wasVerificationLinkSent);
        $this->assertSame('A new verification link has been sent to your email address.', $prompt->message);
    }
}
