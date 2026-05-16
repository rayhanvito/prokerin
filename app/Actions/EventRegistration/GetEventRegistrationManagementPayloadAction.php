<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use Illuminate\Support\Facades\DB;

final class GetEventRegistrationManagementPayloadAction
{
    /**
     * @return array{canManageSettings: bool, metrics: array{eventsOpen: int, totalRegistrations: int, confirmedRegistrations: int, pendingRegistrations: int}, events: array<int, array{id: int, name: string, slug: string, organizationName: string, isOpen: bool, capacity: int|null, registeredCount: int, remainingQuota: int|null, opensAt: string|null, closesAt: string|null, requirePayment: bool, publicUrl: string}>, registrations: array<int, array{id: int, participantName: string, participantEmail: string, phone: string, institution: string, status: string, registeredAt: string, projectName: string}>}
     */
    public function execute(int $actorUserId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->pluck('organization_id');
        $canManageSettings = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary'])
            ->exists();

        $events = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->leftJoin('event_registration_settings', 'event_registration_settings.project_id', '=', 'projects.id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->leftJoin('event_registrations', function ($join): void {
                $join->on('event_registrations.project_id', '=', 'projects.id')
                    ->whereIn('event_registrations.status', ['pending', 'confirmed']);
            })
            ->select([
                'projects.id',
                'projects.name',
                'projects.slug',
                'organizations.name as organization_name',
                'event_registration_settings.is_open',
                'event_registration_settings.capacity',
                'event_registration_settings.opens_at',
                'event_registration_settings.closes_at',
                'event_registration_settings.require_payment',
                DB::raw('count(event_registrations.id) as registered_count'),
            ])
            ->groupBy([
                'projects.id',
                'projects.name',
                'projects.slug',
                'organizations.name',
                'event_registration_settings.is_open',
                'event_registration_settings.capacity',
                'event_registration_settings.opens_at',
                'event_registration_settings.closes_at',
                'event_registration_settings.require_payment',
            ])
            ->orderByRaw('coalesce(event_registration_settings.is_open, 0) desc')
            ->orderBy('projects.name')
            ->get()
            ->map(static function (object $event): array {
                $capacity = $event->capacity === null ? null : (int) $event->capacity;
                $registeredCount = (int) $event->registered_count;

                return [
                    'id' => (int) $event->id,
                    'name' => (string) $event->name,
                    'slug' => (string) $event->slug,
                    'organizationName' => (string) $event->organization_name,
                    'isOpen' => (bool) $event->is_open,
                    'capacity' => $capacity,
                    'registeredCount' => $registeredCount,
                    'remainingQuota' => $capacity === null ? null : max(0, $capacity - $registeredCount),
                    'opensAt' => $event->opens_at === null ? null : (string) $event->opens_at,
                    'closesAt' => $event->closes_at === null ? null : (string) $event->closes_at,
                    'requirePayment' => (bool) $event->require_payment,
                    'publicUrl' => route('events.register.show', ['project' => $event->slug], false),
                ];
            })
            ->all();

        $registrations = DB::table('event_registrations')
            ->join('projects', 'projects.id', '=', 'event_registrations.project_id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->orderByDesc('event_registrations.registered_at')
            ->limit(50)
            ->get([
                'event_registrations.id',
                'event_registrations.participant_name',
                'event_registrations.participant_email',
                'event_registrations.phone',
                'event_registrations.institution',
                'event_registrations.status',
                'event_registrations.registered_at',
                'projects.name as project_name',
            ])
            ->map(static fn (object $registration): array => [
                'id' => (int) $registration->id,
                'participantName' => (string) $registration->participant_name,
                'participantEmail' => (string) $registration->participant_email,
                'phone' => (string) ($registration->phone ?? '-'),
                'institution' => (string) ($registration->institution ?? '-'),
                'status' => (string) $registration->status,
                'registeredAt' => (string) $registration->registered_at,
                'projectName' => (string) $registration->project_name,
            ])
            ->all();

        return [
            'canManageSettings' => $canManageSettings,
            'metrics' => [
                'eventsOpen' => collect($events)->where('isOpen', true)->count(),
                'totalRegistrations' => count($registrations),
                'confirmedRegistrations' => collect($registrations)->where('status', 'confirmed')->count(),
                'pendingRegistrations' => collect($registrations)->where('status', 'pending')->count(),
            ],
            'events' => $events,
            'registrations' => $registrations,
        ];
    }
}
