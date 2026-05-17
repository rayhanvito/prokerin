<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\GetRecentNotificationsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationRecentController extends Controller
{
    public function show(Request $request, GetRecentNotificationsAction $getRecentNotifications): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $getRecentNotifications->execute((int) $request->user()->id),
            'message' => 'Recent notifications loaded.',
        ]);
    }
}
