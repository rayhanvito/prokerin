<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Campus\CampusDashboardPayloadAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CampusDashboardController extends Controller
{
    public function show(Request $request, CampusDashboardPayloadAction $campusDashboard): Response
    {
        return Inertia::render(
            'Campus/Dashboard',
            $campusDashboard->execute((int) $request->user()->id),
        );
    }
}
