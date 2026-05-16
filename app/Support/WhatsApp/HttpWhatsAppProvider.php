<?php

declare(strict_types=1);

namespace App\Support\WhatsApp;

use Illuminate\Support\Facades\Http;

final class HttpWhatsAppProvider implements WhatsAppProvider
{
    /**
     * @return array<string, mixed>
     */
    public function send(WhatsAppMessageData $message): array
    {
        $response = Http::withToken((string) config('services.whatsapp.token'))
            ->timeout((int) config('services.whatsapp.timeout', 10))
            ->post((string) config('services.whatsapp.url'), $message->toArray());

        $response->throw();

        return $response->json() ?? ['status' => $response->status()];
    }
}
