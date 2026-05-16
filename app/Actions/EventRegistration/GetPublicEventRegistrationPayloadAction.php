<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetPublicEventRegistrationPayloadAction
{
    /**
     * @return array{event: array{projectId: int, name: string, slug: string, description: string, organizationName: string, startsAt: string, endsAt: string, registrationStatus: string}, settings: array{isOpen: bool, capacity: int|null, registeredCount: int, remainingQuota: int|null, opensAt: string|null, closesAt: string|null, requirePayment: bool}}
     */
    public function execute(string $projectSlug): array
    {
        $event = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->join('event_registration_settings', 'event_registration_settings.project_id', '=', 'projects.id')
            ->where('projects.slug', $projectSlug)
            ->first([
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.description',
                'projects.starts_at',
                'projects.ends_at',
                'organizations.name as organization_name',
                'event_registration_settings.is_open',
                'event_registration_settings.capacity',
                'event_registration_settings.opens_at',
                'event_registration_settings.closes_at',
                'event_registration_settings.require_payment',
            ]);

        if ($event === null) {
            throw new NotFoundHttpException('Event registration page was not found.');
        }

        $registeredCount = DB::table('event_registrations')
            ->where('project_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        $capacity = $event->capacity === null ? null : (int) $event->capacity;
        $remainingQuota = $capacity === null ? null : max(0, $capacity - $registeredCount);

        return [
            'event' => [
                'projectId' => (int) $event->id,
                'name' => (string) $event->name,
                'slug' => (string) $event->slug,
                'description' => (string) ($event->description ?? ''),
                'organizationName' => (string) $event->organization_name,
                'startsAt' => (string) ($event->starts_at ?? '-'),
                'endsAt' => (string) ($event->ends_at ?? '-'),
                'registrationStatus' => $this->registrationStatus($event),
            ],
            'settings' => [
                'isOpen' => (bool) $event->is_open,
                'capacity' => $capacity,
                'registeredCount' => $registeredCount,
                'remainingQuota' => $remainingQuota,
                'opensAt' => $event->opens_at === null ? null : (string) $event->opens_at,
                'closesAt' => $event->closes_at === null ? null : (string) $event->closes_at,
                'requirePayment' => (bool) $event->require_payment,
            ],
        ];
    }

    private function registrationStatus(object $event): string
    {
        if (! (bool) $event->is_open) {
            return 'closed';
        }

        if ($event->opens_at !== null && Carbon::parse((string) $event->opens_at)->isFuture()) {
            return 'not_open_yet';
        }

        if ($event->closes_at !== null && Carbon::parse((string) $event->closes_at)->isPast()) {
            return 'closed';
        }

        return 'open';
    }
}
