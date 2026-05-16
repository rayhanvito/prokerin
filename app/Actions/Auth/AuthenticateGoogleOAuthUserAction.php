<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\GoogleOAuthAuthenticationData;
use App\DTOs\Auth\GoogleOAuthUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AuthenticateGoogleOAuthUserAction
{
    public function execute(GoogleOAuthUserData $googleUser): GoogleOAuthAuthenticationData
    {
        $user = User::query()
            ->where('google_id', $googleUser->googleId)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'email_verified_at' => $googleUser->emailVerified ? now() : null,
                'password' => Hash::make(Str::random(48)),
                'google_id' => $googleUser->googleId,
                'avatar_url' => $googleUser->avatarUrl,
            ]);

            return new GoogleOAuthAuthenticationData($user, wasCreated: true);
        }

        $user->forceFill([
            'name' => filled($user->name) ? $user->name : $googleUser->name,
            'google_id' => $user->google_id ?? $googleUser->googleId,
            'avatar_url' => $googleUser->avatarUrl ?? $user->avatar_url,
            'email_verified_at' => $user->email_verified_at ?? ($googleUser->emailVerified ? now() : null),
        ])->save();

        return new GoogleOAuthAuthenticationData($user, wasCreated: false);
    }
}
