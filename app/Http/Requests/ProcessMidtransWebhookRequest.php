<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProcessMidtransWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'fraud_status' => ['nullable', 'string'],
            'transaction_time' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array{order_id: string, status_code: string, gross_amount: string, signature_key: string, transaction_status: string, fraud_status?: string|null, transaction_time?: string|null}
     */
    public function payload(): array
    {
        /** @var array{order_id: string, status_code: string, gross_amount: string, signature_key: string, transaction_status: string, fraud_status?: string|null, transaction_time?: string|null} $payload */
        $payload = $this->validated();

        return $payload;
    }
}
