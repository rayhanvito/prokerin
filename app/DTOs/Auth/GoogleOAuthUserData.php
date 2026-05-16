<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class GoogleOAuthUserData
{
    public function __construct(
        public string $googleId,
        public string $name,
        public string $email,
        public bool $emailVerified,
        public ?string $avatarUrl = null,
    ) {}
}
