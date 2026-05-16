<?php

declare(strict_types=1);

namespace App\Support\WhatsApp;

interface WhatsAppProvider
{
    /**
     * @return array<string, mixed>
     */
    public function send(WhatsAppMessageData $message): array;
}
