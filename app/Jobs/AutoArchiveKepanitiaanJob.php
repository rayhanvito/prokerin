<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

final class AutoArchiveKepanitiaanJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        DB::table('organizations')
            ->where('mode', 'kepanitiaan')
            ->where('status', 'active')
            ->whereNotNull('auto_archive_at')
            ->where('auto_archive_at', '<=', now())
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);
    }
}
