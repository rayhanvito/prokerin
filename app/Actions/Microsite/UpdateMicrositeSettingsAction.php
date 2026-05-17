<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UpdateMicrositeSettingsAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @param  array{description_md?: string|null, location_text?: string|null, location_maps_url?: string|null, contact_name?: string|null, contact_whatsapp?: string|null, contact_email?: string|null, show_countdown: bool, show_committee: bool, show_gallery: bool, meta_title?: string|null, meta_description?: string|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, string $projectSlug, array $data): void
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);

        DB::table('proker_microsites')
            ->where('id', $micrositeId)
            ->update([
                'description_md' => $data['description_md'] ?? null,
                'location_text' => $data['location_text'] ?? null,
                'location_maps_url' => $data['location_maps_url'] ?? null,
                'contact_name' => $data['contact_name'] ?? null,
                'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'show_countdown' => $data['show_countdown'],
                'show_committee' => $data['show_committee'],
                'show_gallery' => $data['show_gallery'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'updated_by_user_id' => $actorUserId,
                'updated_at' => now(),
            ]);

        Cache::forget("microsite:{$project->organization_slug}:{$project->slug}");
    }
}
