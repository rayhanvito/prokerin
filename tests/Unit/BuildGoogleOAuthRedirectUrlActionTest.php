<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Auth\BuildGoogleOAuthRedirectUrlAction;
use RuntimeException;
use Tests\TestCase;

final class BuildGoogleOAuthRedirectUrlActionTest extends TestCase
{
    public function test_it_builds_a_google_authorization_url_and_stores_state(): void
    {
        $storedState = null;

        $url = (new BuildGoogleOAuthRedirectUrlAction)->execute(
            [
                'client_id' => 'google-client-id',
                'client_secret' => 'google-client-secret',
                'redirect' => 'https://prokerin.test/auth/google/callback',
            ],
            static function (string $state) use (&$storedState): void {
                $storedState = $state;
            },
        );

        $this->assertIsString($storedState);
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $url);
        $this->assertStringContainsString('client_id=google-client-id', $url);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fprokerin.test%2Fauth%2Fgoogle%2Fcallback', $url);
        $this->assertStringContainsString('scope=openid%20profile%20email', $url);
        $this->assertStringContainsString('state='.$storedState, $url);
    }

    public function test_it_rejects_incomplete_google_configuration(): void
    {
        $this->expectException(RuntimeException::class);

        (new BuildGoogleOAuthRedirectUrlAction)->execute(
            [
                'client_id' => '',
                'client_secret' => 'google-client-secret',
                'redirect' => 'https://prokerin.test/auth/google/callback',
            ],
            static fn (): null => null,
        );
    }
}
