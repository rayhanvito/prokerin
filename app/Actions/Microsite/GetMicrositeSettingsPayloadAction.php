<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use App\Actions\Microsite\Concerns\AuthorizesMicrositeManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class GetMicrositeSettingsPayloadAction
{
    use AuthorizesMicrositeManagement;

    /**
     * @return array{project: array{id: int, name: string, slug: string, organizationSlug: string, publicUrl: string}, microsite: array{id: int, isPublished: bool, bannerImageUrl: string|null, descriptionMd: string, locationText: string, locationMapsUrl: string, contactName: string, contactWhatsapp: string, contactEmail: string, showCountdown: bool, showCommittee: bool, showGallery: bool, metaTitle: string, metaDescription: string, publishedAt: string|null}, gallery: array<int, array{id: int, imageUrl: string, caption: string, sortOrder: int}>}
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, string $projectSlug): array
    {
        $project = $this->authorizeProjectManager($actorUserId, $projectSlug);
        $micrositeId = $this->ensureMicrosite((int) $project->id, $actorUserId);

        $microsite = DB::table('proker_microsites')
            ->where('id', $micrositeId)
            ->first();

        return [
            'project' => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
                'slug' => (string) $project->slug,
                'organizationSlug' => (string) $project->organization_slug,
                'publicUrl' => route('microsites.show', [
                    'orgSlug' => (string) $project->organization_slug,
                    'prokerSlug' => (string) $project->slug,
                ]),
            ],
            'microsite' => [
                'id' => $micrositeId,
                'isPublished' => (bool) $microsite->is_published,
                'bannerImageUrl' => $this->assetUrl($microsite->banner_image_path),
                'descriptionMd' => (string) ($microsite->description_md ?? ''),
                'locationText' => (string) ($microsite->location_text ?? ''),
                'locationMapsUrl' => (string) ($microsite->location_maps_url ?? ''),
                'contactName' => (string) ($microsite->contact_name ?? ''),
                'contactWhatsapp' => (string) ($microsite->contact_whatsapp ?? ''),
                'contactEmail' => (string) ($microsite->contact_email ?? ''),
                'showCountdown' => (bool) $microsite->show_countdown,
                'showCommittee' => (bool) $microsite->show_committee,
                'showGallery' => (bool) $microsite->show_gallery,
                'metaTitle' => (string) ($microsite->meta_title ?? ''),
                'metaDescription' => (string) ($microsite->meta_description ?? ''),
                'publishedAt' => is_string($microsite->published_at) ? $microsite->published_at : null,
            ],
            'gallery' => DB::table('proker_microsite_gallery')
                ->where('microsite_id', $micrositeId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'image_path', 'caption', 'sort_order'])
                ->map(fn (object $item): array => [
                    'id' => (int) $item->id,
                    'imageUrl' => $this->assetUrl($item->image_path) ?? '',
                    'caption' => (string) ($item->caption ?? ''),
                    'sortOrder' => (int) $item->sort_order,
                ])
                ->all(),
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
