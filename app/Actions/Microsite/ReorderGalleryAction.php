<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ReorderGalleryAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @param  array<int, int>  $orderedIds
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, string $projectSlug, array $orderedIds): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);

        foreach (array_values($orderedIds) as $index => $itemId) {
            DB::table('proker_microsite_gallery')
                ->where('id', $itemId)
                ->where('microsite_id', $micrositeId)
                ->update([
                    'sort_order' => $index,
                    'updated_at' => now(),
                ]);
        }

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }
}
