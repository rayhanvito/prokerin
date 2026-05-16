<?php

declare(strict_types=1);

namespace App\Support\WhatsApp;

final readonly class WhatsAppMessageData
{
    public function __construct(
        public string $from,
        public string $to,
        public string $message,
        public string $type,
    ) {}

    /**
     * @return array{from: string, to: string, message: string, type: string}
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'message' => $this->message,
            'type' => $this->type,
        ];
    }
}
