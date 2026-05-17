<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EventRegistration\ProcessMidtransWebhookAction;
use App\Http\Requests\ProcessMidtransWebhookRequest;
use Illuminate\Http\JsonResponse;

final class MidtransWebhookController extends Controller
{
    public function store(ProcessMidtransWebhookRequest $request, ProcessMidtransWebhookAction $processWebhook): JsonResponse
    {
        $result = $processWebhook->execute($request->payload());

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Webhook pembayaran diproses.',
        ]);
    }
}
