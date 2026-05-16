<?php

declare(strict_types=1);

namespace App\Support\WhatsApp;

final class FakeWhatsAppProvider implements WhatsAppProvider
{
    /**
     * @var array<int, WhatsAppMessageData>
     */
    public array $messages = [];

    /**
     * @return array{fake: bool, count: int}
     */
    public function send(WhatsAppMessageData $message): array
    {
        $this->messages[] = $message;

        return [
            'fake' => true,
            'count' => count($this->messages),
        ];
    }
}
