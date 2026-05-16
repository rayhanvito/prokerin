<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProcessMidtransWebhookAction
{
    /**
     * @param  array{order_id: string, status_code: string, gross_amount: string, signature_key: string, transaction_status: string, fraud_status?: string|null, transaction_time?: string|null}  $payload
     * @return array{paymentOrderId: int, status: string}
     *
     * @throws ValidationException
     */
    public function execute(array $payload): array
    {
        $this->ensureValidSignature($payload);

        return DB::transaction(function () use ($payload): array {
            $order = DB::table('payment_orders')
                ->join('event_registrations', 'event_registrations.id', '=', 'payment_orders.registration_id')
                ->where('payment_orders.provider_order_id', $payload['order_id'])
                ->lockForUpdate()
                ->first([
                    'payment_orders.id',
                    'payment_orders.registration_id',
                    'payment_orders.amount',
                    'payment_orders.status',
                    'event_registrations.status as registration_status',
                ]);

            if ($order === null) {
                throw new NotFoundHttpException('Payment order was not found.');
            }

            $status = $this->mapStatus(
                transactionStatus: $payload['transaction_status'],
                fraudStatus: $payload['fraud_status'] ?? null,
            );
            $now = now();

            DB::table('payment_orders')
                ->where('id', $order->id)
                ->update([
                    'status' => $status,
                    'paid_at' => $status === 'paid' ? $now : null,
                    'updated_at' => $now,
                ]);

            if ($status === 'paid') {
                DB::table('event_registrations')
                    ->where('id', $order->registration_id)
                    ->update([
                        'status' => 'confirmed',
                        'updated_at' => $now,
                    ]);
            }

            if (in_array($status, ['expired', 'cancelled', 'failed'], true)) {
                DB::table('event_registrations')
                    ->where('id', $order->registration_id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => $now,
                    ]);
            }

            return [
                'paymentOrderId' => (int) $order->id,
                'status' => $status,
            ];
        });
    }

    /**
     * @param  array{order_id: string, status_code: string, gross_amount: string, signature_key: string}  $payload
     *
     * @throws ValidationException
     */
    private function ensureValidSignature(array $payload): void
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $expected = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        if ($serverKey === '' || ! hash_equals($expected, $payload['signature_key'])) {
            throw ValidationException::withMessages([
                'signature_key' => 'Signature webhook pembayaran tidak valid.',
            ]);
        }
    }

    private function mapStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? 'challenge' : 'paid';
        }

        return match ($transactionStatus) {
            'settlement' => 'paid',
            'pending' => 'pending',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'deny', 'failure' => 'failed',
            default => 'pending',
        };
    }
}
