<?php

declare(strict_types=1);

namespace App\DTOs\Ai;

final readonly class AiProviderResponse
{
    /**
     * @param  array<string, mixed>  $content
     */
    public function __construct(
        public array $content,
        public int $promptTokens,
        public int $completionTokens,
    ) {}
}
