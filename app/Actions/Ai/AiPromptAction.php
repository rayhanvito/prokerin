<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\DTOs\Ai\AiPromptResult;
use App\Support\Ai\AiProvider;
use App\Support\Ai\FakeAiProvider;
use App\Support\Ai\UnsupportedAiProvider;
use Illuminate\Support\Facades\DB;

final readonly class AiPromptAction
{
    public function __construct(private SanitizeAiPayloadAction $sanitizePayload) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(
        int $organizationId,
        int $userId,
        string $actionType,
        array $payload,
    ): AiPromptResult {
        $sanitizedPayload = $this->sanitizePayload->execute($payload);
        $providerName = (string) config('services.ai.provider', 'fake');
        $model = (string) config('services.ai.model', 'prokerin-local');
        $provider = $this->provider($providerName);
        $response = $provider->complete($actionType, $sanitizedPayload, $model);
        $promptHash = self::promptHashForPayload($actionType, $sanitizedPayload);

        DB::table('ai_usage_logs')->insert([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'provider' => $providerName,
            'model' => $model,
            'prompt_hash' => $promptHash,
            'prompt_tokens' => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return new AiPromptResult(
            content: $response->content,
            promptHash: $promptHash,
            promptTokens: $response->promptTokens,
            completionTokens: $response->completionTokens,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function promptHashForPayload(string $actionType, array $payload): string
    {
        return hash('sha256', $actionType.'|'.json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function provider(string $providerName): AiProvider
    {
        return match ($providerName) {
            'fake' => new FakeAiProvider,
            default => new UnsupportedAiProvider($providerName),
        };
    }
}
