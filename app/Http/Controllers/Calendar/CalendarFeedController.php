<?php

declare(strict_types=1);

namespace App\Http\Controllers\Calendar;

use App\Actions\Calendar\BuildIcsFeedAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

final class CalendarFeedController extends Controller
{
    public function show(string $token, BuildIcsFeedAction $buildIcsFeed): Response
    {
        return response($buildIcsFeed->execute($token), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="prokerin-calendar.ics"',
        ]);
    }
}
