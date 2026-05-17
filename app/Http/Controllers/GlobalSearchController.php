<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Search\GlobalSearchAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearchAction $globalSearch): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $globalSearch->execute((int) $request->user()->id, (string) $request->query('q', '')),
            'message' => 'Search results loaded.',
        ]);
    }
}
