<?php

declare(strict_types=1);

namespace App\Support\Ai;

use App\DTOs\Ai\AiProviderResponse;

interface AiProvider
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function complete(string $actionType, array $payload, string $model): AiProviderResponse;
}
