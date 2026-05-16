<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\OAuthProviderReadinessData;

final class CheckGoogleOAuthReadinessAction
{
    /**
     * @param  array{client_id?: string|null, client_secret?: string|null, redirect?: string|null}  $config
     */
    public function execute(array $config): OAuthProviderReadinessData
    {
        $requiredKeys = ['client_id', 'client_secret', 'redirect'];
        $missingKeys = array_values(array_filter(
            $requiredKeys,
            static fn (string $key): bool => blank($config[$key] ?? null),
        ));

        return new OAuthProviderReadinessData(
            provider: 'google',
            isConfigured: $missingKeys === [],
            missingKeys: $missingKeys,
        );
    }
}
