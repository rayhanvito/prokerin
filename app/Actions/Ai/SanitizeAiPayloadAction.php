<?php

declare(strict_types=1);

namespace App\Actions\Ai;

final readonly class SanitizeAiPayloadAction
{
    /**
     * @var list<string>
     */
    private const BLOCKED_KEYS = [
        'email',
        'phone',
        'phone_number',
        'whatsapp',
        'whatsapp_number',
        'ktp',
        'nik',
        'avatar',
        'avatar_url',
        'logo_path',
        'storage_path',
        'path',
        'token',
        'provider_order_id',
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload): array
    {
        return $this->sanitizeArray($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizeArray(array $payload): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            if ($this->isBlockedKey((string) $key)) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = array_is_list($value)
                    ? array_map(fn (mixed $item): mixed => is_array($item) ? $this->sanitizeArray($item) : $item, $value)
                    : $this->sanitizeArray($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isBlockedKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::BLOCKED_KEYS as $blockedKey) {
            if ($normalized === $blockedKey || str_contains($normalized, $blockedKey)) {
                return true;
            }
        }

        return false;
    }
}
