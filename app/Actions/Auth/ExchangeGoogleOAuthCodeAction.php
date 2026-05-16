<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\GoogleOAuthUserData;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;

final readonly class ExchangeGoogleOAuthCodeAction
{
    public function __construct(
        private HttpFactory $http,
    ) {}

    /**
     * @param  array{client_id?: string|null, client_secret?: string|null, redirect?: string|null}  $config
     *
     * @throws RequestException
     * @throws ValidationException
     */
    public function execute(string $code, array $config): GoogleOAuthUserData
    {
        $tokenPayload = $this->http
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $config['redirect'],
            ])
            ->throw()
            ->json();

        $accessToken = $tokenPayload['access_token'] ?? null;

        if (! is_string($accessToken) || blank($accessToken)) {
            throw ValidationException::withMessages([
                'google' => 'Google tidak mengembalikan access token yang valid.',
            ]);
        }

        $profile = $this->http
            ->withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo')
            ->throw()
            ->json();

        $googleId = $profile['sub'] ?? null;
        $name = $profile['name'] ?? null;
        $email = $profile['email'] ?? null;

        if (! is_string($googleId) || ! is_string($name) || ! is_string($email)) {
            throw ValidationException::withMessages([
                'google' => 'Profil Google tidak lengkap untuk login.',
            ]);
        }

        return new GoogleOAuthUserData(
            googleId: $googleId,
            name: $name,
            email: strtolower($email),
            emailVerified: (bool) ($profile['email_verified'] ?? false),
            avatarUrl: is_string($profile['picture'] ?? null) ? $profile['picture'] : null,
        );
    }
}
