<?php

declare(strict_types=1);

namespace App\Actions\Calendar;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegenerateCalendarSyncTokenAction
{
    public function execute(int $userId): string
    {
        do {
            $token = Str::random(64);
        } while (DB::table('users')->where('calendar_sync_token', $token)->exists());

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'calendar_sync_token' => $token,
                'updated_at' => now(),
            ]);

        return route('calendar.feed', ['token' => $token], true);
    }
}
