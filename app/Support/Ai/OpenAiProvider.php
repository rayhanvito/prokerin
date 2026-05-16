<?php

declare(strict_types=1);

namespace App\Support\Ai;

use App\DTOs\Ai\AiProviderResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class OpenAiProvider implements AiProvider
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private int $timeout,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function complete(string $actionType, array $payload, string $model): AiProviderResponse
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('AI_API_KEY must be configured when AI_PROVIDER=openai.');
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->post('/responses', [
                'model' => $model,
                'instructions' => $this->instructions($actionType),
                'input' => json_encode([
                    'action_type' => $actionType,
                    'payload' => $payload,
                ], JSON_THROW_ON_ERROR),
                'max_output_tokens' => 900,
                'text' => [
                    'format' => $this->responseFormat($actionType),
                ],
            ])
            ->throw()
            ->json();

        if (! is_array($response)) {
            throw new RuntimeException('OpenAI response payload is invalid.');
        }

        return new AiProviderResponse(
            content: $this->decodeContent($response),
            promptTokens: (int) data_get($response, 'usage.input_tokens', 0),
            completionTokens: (int) data_get($response, 'usage.output_tokens', 0),
        );
    }

    private function instructions(string $actionType): string
    {
        $base = 'You are Prokerin AI Assistant. Respond in Bahasa Indonesia. Use only the supplied minimized tenant payload. Do not invent member personal data, contacts, file paths, payment identifiers, or secrets.';

        return match ($actionType) {
            'proposal_draft' => $base.' Draft concise, usable proposal section suggestions for Indonesian student organization programs.',
            'lpj_summary' => $base.' Draft a concise LPJ accountability summary and practical recommendations from checklist readiness.',
            default => $base,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function responseFormat(string $actionType): array
    {
        return [
            'type' => 'json_schema',
            'name' => $actionType,
            'strict' => true,
            'schema' => match ($actionType) {
                'proposal_draft' => $this->proposalDraftSchema(),
                'lpj_summary' => $this->lpjSummarySchema(),
                default => $this->fallbackSchema(),
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proposalDraftSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['sections'],
            'properties' => [
                'sections' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['heading', 'body'],
                        'properties' => [
                            'heading' => ['type' => 'string'],
                            'body' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lpjSummarySchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['summary', 'recommendations'],
            'properties' => [
                'summary' => ['type' => 'string'],
                'recommendations' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['summary'],
            'properties' => [
                'summary' => ['type' => 'string'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function decodeContent(array $response): array
    {
        $text = data_get($response, 'output_text');

        if (! is_string($text)) {
            $text = $this->extractOutputText($response);
        }

        if ($text === '') {
            throw new RuntimeException('OpenAI response did not contain output text.');
        }

        $content = json_decode($text, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($content)) {
            throw new RuntimeException('OpenAI structured output was not a JSON object.');
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function extractOutputText(array $response): string
    {
        $output = data_get($response, 'output', []);

        if (! is_array($output)) {
            return '';
        }

        foreach ($output as $item) {
            if (! is_array($item)) {
                continue;
            }

            $content = $item['content'] ?? [];

            if (! is_array($content)) {
                continue;
            }

            foreach ($content as $part) {
                if (is_array($part) && ($part['type'] ?? null) === 'output_text' && is_string($part['text'] ?? null)) {
                    return $part['text'];
                }
            }
        }

        return '';
    }
}
