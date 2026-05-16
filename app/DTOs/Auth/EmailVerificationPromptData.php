<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class EmailVerificationPromptData
{
    public function __construct(
        public bool $isVerified,
        public bool $wasVerificationLinkSent,
        public string $message,
    ) {}

    /**
     * @return array{isVerified: bool, wasVerificationLinkSent: bool, message: string}
     */
    public function toArray(): array
    {
        return [
            'isVerified' => $this->isVerified,
            'wasVerificationLinkSent' => $this->wasVerificationLinkSent,
            'message' => $this->message,
        ];
    }
}
