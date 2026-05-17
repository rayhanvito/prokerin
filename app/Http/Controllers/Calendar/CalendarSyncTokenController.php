<?php

declare(strict_types=1);

namespace App\Http\Controllers\Calendar;

use App\Actions\Calendar\RegenerateCalendarSyncTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CalendarSyncTokenController extends Controller
{
    public function store(Request $request, RegenerateCalendarSyncTokenAction $regenerateToken): RedirectResponse
    {
        $regenerateToken->execute((int) $request->user()->id);

        return back()->with('success', 'Calendar sync URL berhasil dibuat.');
    }
}
