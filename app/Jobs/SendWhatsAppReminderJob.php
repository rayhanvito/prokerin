<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

final class SendWhatsAppReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(
        public readonly ?int $organizationId,
        public readonly ?int $userId,
        public readonly string $recipientNumber,
        public readonly string $messageType,
        public readonly string $message,
    ) {}

    public function handle(): void
    {
        $logId = (int) DB::table('whatsapp_delivery_logs')->insertGetId([
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'message_type' => $this->messageType,
            'recipient_number' => $this->recipientNumber,
            'message' => $this->message,
            'status' => 'queued',
            'provider_response' => null,
            'sent_at' => null,
            'failed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = Http::withToken((string) config('services.whatsapp.token'))
                ->timeout((int) config('services.whatsapp.timeout', 10))
                ->post((string) config('services.whatsapp.url'), [
                    'from' => config('services.whatsapp.from_number'),
                    'to' => $this->recipientNumber,
                    'message' => $this->message,
                    'type' => $this->messageType,
                ]);

            $response->throw();

            DB::table('whatsapp_delivery_logs')
                ->where('id', $logId)
                ->update([
                    'status' => 'sent',
                    'provider_response' => json_encode($response->json() ?? ['status' => $response->status()]),
                    'sent_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            DB::table('whatsapp_delivery_logs')
                ->where('id', $logId)
                ->update([
                    'status' => 'failed',
                    'provider_response' => json_encode(['error' => $exception->getMessage()]),
                    'failed_at' => now(),
                    'updated_at' => now(),
                ]);

            throw $exception;
        }
    }
}
