<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\DeleteWebPushSubscriptionAction;
use App\Actions\Notification\StoreWebPushSubscriptionAction;
use App\Http\Requests\DeleteWebPushSubscriptionRequest;
use App\Http\Requests\StoreWebPushSubscriptionRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class WebPushSubscriptionController extends Controller
{
    public function store(StoreWebPushSubscriptionRequest $request, StoreWebPushSubscriptionAction $storeSubscription): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $storeSubscription->execute($user, $request->validated());

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Web push subscription saved.',
        ]);
    }

    public function destroy(DeleteWebPushSubscriptionRequest $request, DeleteWebPushSubscriptionAction $deleteSubscription): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $deleteSubscription->execute($user, (string) $request->validated('endpoint'));

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Web push subscription removed.',
        ]);
    }
}
