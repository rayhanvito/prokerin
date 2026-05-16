<?php

declare(strict_types=1);

namespace App\Support\Ai;

use App\DTOs\Ai\AiProviderResponse;
use RuntimeException;

final class UnsupportedAiProvider implements AiProvider
{
    public function __construct(private readonly string $provider) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function complete(string $actionType, array $payload, string $model): AiProviderResponse
    {
        throw new RuntimeException(sprintf('AI provider [%s] is not configured yet.', $this->provider));
    }
}
