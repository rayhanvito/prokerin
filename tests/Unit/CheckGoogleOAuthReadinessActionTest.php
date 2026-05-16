<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Auth\CheckGoogleOAuthReadinessAction;
use PHPUnit\Framework\TestCase;

final class CheckGoogleOAuthReadinessActionTest extends TestCase
{
    public function test_it_marks_google_oauth_configured_when_required_keys_exist(): void
    {
        $readiness = (new CheckGoogleOAuthReadinessAction)->execute([
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect' => 'https://prokerin.test/auth/google/callback',
        ]);

        $this->assertTrue($readiness->isConfigured);
        $this->assertSame([], $readiness->missingKeys);
    }

    public function test_it_reports_missing_google_oauth_keys(): void
    {
        $readiness = (new CheckGoogleOAuthReadinessAction)->execute([
            'client_id' => 'client-id',
            'client_secret' => null,
            'redirect' => '',
        ]);

        $this->assertFalse($readiness->isConfigured);
        $this->assertSame(['client_secret', 'redirect'], $readiness->missingKeys);
    }
}
