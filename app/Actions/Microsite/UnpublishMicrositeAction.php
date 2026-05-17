<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UnpublishMicrositeAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, string $projectSlug): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);

        DB::table('proker_microsites')
            ->where('id', $micrositeId)
            ->update([
                'is_published' => false,
                'updated_by_user_id' => $actorUserId,
                'updated_at' => now(),
            ]);

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }
}
