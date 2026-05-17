<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UploadMicrositeAssetAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @throws AuthorizationException
     */
    public function uploadBanner(int $actorUserId, string $projectSlug, UploadedFile $file): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);
        $path = $file->store("microsites/{$project->id}/banner", 'public');

        DB::table('proker_microsites')
            ->where('id', $micrositeId)
            ->update([
                'banner_image_path' => $path,
                'updated_by_user_id' => $actorUserId,
                'updated_at' => now(),
            ]);

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }

    /**
     * @throws AuthorizationException
     */
    public function uploadGalleryItem(int $actorUserId, string $projectSlug, UploadedFile $file, ?string $caption = null): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);
        $path = $file->store("microsites/{$project->id}/gallery", 'public');
        $nextSortOrder = ((int) DB::table('proker_microsite_gallery')->where('microsite_id', $micrositeId)->max('sort_order')) + 1;

        DB::table('proker_microsite_gallery')->insert([
            'microsite_id' => $micrositeId,
            'image_path' => $path,
            'caption' => $caption,
            'sort_order' => $nextSortOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteGalleryItem(int $actorUserId, string $projectSlug, int $itemId): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);
        $item = DB::table('proker_microsite_gallery')
            ->where('id', $itemId)
            ->where('microsite_id', $micrositeId)
            ->first(['id', 'image_path']);

        if ($item === null) {
            return;
        }

        Storage::disk('public')->delete((string) $item->image_path);
        DB::table('proker_microsite_gallery')->where('id', $itemId)->delete();
        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }
}
