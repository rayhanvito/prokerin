<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class OAuthProviderReadinessData
{
    /**
     * @param  array<int, string>  $missingKeys
     */
    public function __construct(
        public string $provider,
        public bool $isConfigured,
        public array $missingKeys,
    ) {}

    /**
     * @return array{provider: string, isConfigured: bool, missingKeys: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'isConfigured' => $this->isConfigured,
            'missingKeys' => $this->missingKeys,
        ];
    }
}
