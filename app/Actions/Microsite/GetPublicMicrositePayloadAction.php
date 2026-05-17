<?php

declare(strict_types=1);

namespace App\Actions\Microsite;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetPublicMicrositePayloadAction
{
    /**
     * @return array{project: array{id: int, name: string, slug: string, description: string, organizationName: string, organizationSlug: string, startsAt: string|null, endsAt: string|null}, microsite: array{bannerImageUrl: string|null, descriptionHtml: string, locationText: string|null, locationMapsUrl: string|null, contactName: string|null, contactWhatsapp: string|null, contactEmail: string|null, showCountdown: bool, showCommittee: bool, showGallery: bool, metaTitle: string, metaDescription: string}, gallery: array<int, array{id: int, imageUrl: string, caption: string}>, committee: array<int, array{name: string, role: string}>, registration: array{isAvailable: bool, url: string|null, remainingQuota: int|null}, seo: array{title: string, description: string, image: string|null, canonical: string}}
     */
    public function execute(string $orgSlug, string $prokerSlug): array
    {
        return Cache::remember(
            "microsite:{$orgSlug}:{$prokerSlug}",
            now()->addMinutes(5),
            fn (): array => $this->buildPayload($orgSlug, $prokerSlug),
        );
    }

    /**
     * @return array{project: array{id: int, name: string, slug: string, description: string, organizationName: string, organizationSlug: string, startsAt: string|null, endsAt: string|null}, microsite: array{bannerImageUrl: string|null, descriptionHtml: string, locationText: string|null, locationMapsUrl: string|null, contactName: string|null, contactWhatsapp: string|null, contactEmail: string|null, showCountdown: bool, showCommittee: bool, showGallery: bool, metaTitle: string, metaDescription: string}, gallery: array<int, array{id: int, imageUrl: string, caption: string}>, committee: array<int, array{name: string, role: string}>, registration: array{isAvailable: bool, url: string|null, remainingQuota: int|null}, seo: array{title: string, description: string, image: string|null, canonical: string}}
     */
    private function buildPayload(string $orgSlug, string $prokerSlug): array
    {
        $row = DB::table('proker_microsites')
            ->join('projects', 'projects.id', '=', 'proker_microsites.project_id')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->where('organizations.slug', $orgSlug)
            ->where('projects.slug', $prokerSlug)
            ->where('proker_microsites.is_published', true)
            ->first([
                'proker_microsites.id as microsite_id',
                'proker_microsites.banner_image_path',
                'proker_microsites.description_md',
                'proker_microsites.location_text',
                'proker_microsites.location_maps_url',
                'proker_microsites.contact_name',
                'proker_microsites.contact_whatsapp',
                'proker_microsites.contact_email',
                'proker_microsites.show_countdown',
                'proker_microsites.show_committee',
                'proker_microsites.show_gallery',
                'proker_microsites.meta_title',
                'proker_microsites.meta_description',
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.description',
                'projects.starts_at',
                'projects.ends_at',
                'organizations.name as organization_name',
                'organizations.slug as organization_slug',
            ]);

        if ($row === null) {
            throw new NotFoundHttpException('Microsite proker tidak ditemukan.');
        }

        $bannerUrl = $this->assetUrl($row->banner_image_path);
        $description = (string) ($row->description_md ?? $row->description ?? '');
        $metaTitle = (string) ($row->meta_title ?? $row->name);
        $metaDescription = (string) ($row->meta_description ?? str($description)->stripTags()->limit(155));

        return [
            'project' => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
                'description' => (string) ($row->description ?? ''),
                'organizationName' => (string) $row->organization_name,
                'organizationSlug' => (string) $row->organization_slug,
                'startsAt' => is_string($row->starts_at) ? $row->starts_at : null,
                'endsAt' => is_string($row->ends_at) ? $row->ends_at : null,
            ],
            'microsite' => [
                'bannerImageUrl' => $bannerUrl,
                'descriptionHtml' => $this->descriptionHtml($description),
                'locationText' => is_string($row->location_text) ? $row->location_text : null,
                'locationMapsUrl' => is_string($row->location_maps_url) ? $row->location_maps_url : null,
                'contactName' => is_string($row->contact_name) ? $row->contact_name : null,
                'contactWhatsapp' => is_string($row->contact_whatsapp) ? $row->contact_whatsapp : null,
                'contactEmail' => is_string($row->contact_email) ? $row->contact_email : null,
                'showCountdown' => (bool) $row->show_countdown,
                'showCommittee' => (bool) $row->show_committee,
                'showGallery' => (bool) $row->show_gallery,
                'metaTitle' => $metaTitle,
                'metaDescription' => $metaDescription,
            ],
            'gallery' => $this->gallery((int) $row->microsite_id),
            'committee' => $this->committee((int) $row->id, (bool) $row->show_committee),
            'registration' => $this->registration((int) $row->id, (string) $row->slug),
            'seo' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $bannerUrl,
                'canonical' => route('microsites.show', ['orgSlug' => $orgSlug, 'prokerSlug' => $prokerSlug]),
            ],
        ];
    }

    /**
     * @return array<int, array{id: int, imageUrl: string, caption: string}>
     */
    private function gallery(int $micrositeId): array
    {
        return DB::table('proker_microsite_gallery')
            ->where('microsite_id', $micrositeId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'image_path', 'caption'])
            ->map(fn (object $item): array => [
                'id' => (int) $item->id,
                'imageUrl' => $this->assetUrl($item->image_path) ?? '',
                'caption' => (string) ($item->caption ?? ''),
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, role: string}>
     */
    private function committee(int $projectId, bool $showCommittee): array
    {
        if (! $showCommittee) {
            return [];
        }

        return DB::table('project_members')
            ->join('users', 'users.id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $projectId)
            ->orderByRaw("case project_members.role when 'project_lead' then 0 when 'division_coordinator' then 1 else 2 end")
            ->orderBy('users.name')
            ->limit(12)
            ->get(['users.name', 'project_members.role'])
            ->map(static fn (object $member): array => [
                'name' => (string) $member->name,
                'role' => (string) $member->role,
            ])
            ->all();
    }

    /**
     * @return array{isAvailable: bool, url: string|null, remainingQuota: int|null}
     */
    private function registration(int $projectId, string $projectSlug): array
    {
        $settings = DB::table('event_registration_settings')
            ->where('project_id', $projectId)
            ->first(['is_open', 'capacity', 'opens_at', 'closes_at']);

        if ($settings === null || ! (bool) $settings->is_open) {
            return ['isAvailable' => false, 'url' => null, 'remainingQuota' => null];
        }

        $now = now();
        if ($settings->opens_at !== null && Carbon::parse((string) $settings->opens_at)->isFuture()) {
            return ['isAvailable' => false, 'url' => null, 'remainingQuota' => null];
        }

        if ($settings->closes_at !== null && Carbon::parse((string) $settings->closes_at)->isPast()) {
            return ['isAvailable' => false, 'url' => null, 'remainingQuota' => null];
        }

        $registeredCount = DB::table('event_registrations')
            ->where('project_id', $projectId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        $capacity = $settings->capacity === null ? null : (int) $settings->capacity;
        $remainingQuota = $capacity === null ? null : max(0, $capacity - $registeredCount);

        return [
            'isAvailable' => $remainingQuota === null || $remainingQuota > 0,
            'url' => route('events.register.show', ['project' => $projectSlug]),
            'remainingQuota' => $remainingQuota,
        ];
    }

    private function descriptionHtml(string $description): string
    {
        $escaped = e($description);
        $withParagraphs = collect(preg_split('/\R{2,}/', $escaped) ?: [])
            ->map(static fn (string $paragraph): string => '<p>'.nl2br(trim($paragraph)).'</p>')
            ->implode('');

        return $withParagraphs === '' ? '<p>Informasi proker akan segera dilengkapi.</p>' : $withParagraphs;
    }

    private function assetUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
