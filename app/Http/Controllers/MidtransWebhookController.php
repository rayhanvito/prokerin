<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EventRegistration\ProcessMidtransWebhookAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MidtransWebhookController extends Controller
{
    public function store(Request $request, ProcessMidtransWebhookAction $processWebhook): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'fraud_status' => ['nullable', 'string'],
            'transaction_time' => ['nullable', 'string'],
        ]);

        $result = $processWebhook->execute($data);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Webhook pembayaran diproses.',
        ]);
    }
}
