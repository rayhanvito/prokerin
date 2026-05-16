<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

final class BuildGoogleOAuthRedirectUrlAction
{
    /**
     * @param  array{client_id?: string|null, client_secret?: string|null, redirect?: string|null}  $config
     */
    public function execute(array $config, callable $storeState): string
    {
        $readiness = (new CheckGoogleOAuthReadinessAction)->execute($config);

        if (! $readiness->isConfigured) {
            throw new RuntimeException('Google OAuth belum dikonfigurasi lengkap.');
        }

        $state = Str::random(48);
        $storeState($state);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.Arr::query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);
    }
}
