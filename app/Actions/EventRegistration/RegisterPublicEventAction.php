<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use App\Notifications\EventRegistrationConfirmedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final class RegisterPublicEventAction
{
    /**
     * @param  array{participant_name: string, participant_email: string, phone?: string|null, institution?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function execute(string $projectSlug, array $data): int
    {
        return DB::transaction(function () use ($projectSlug, $data): int {
            $event = DB::table('projects')
                ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
                ->join('event_registration_settings', 'event_registration_settings.project_id', '=', 'projects.id')
                ->where('projects.slug', $projectSlug)
                ->lockForUpdate()
                ->first([
                    'projects.id',
                    'projects.name',
                    'organizations.name as organization_name',
                    'event_registration_settings.is_open',
                    'event_registration_settings.capacity',
                    'event_registration_settings.opens_at',
                    'event_registration_settings.closes_at',
                    'event_registration_settings.require_payment',
                ]);

            if ($event === null || ! (bool) $event->is_open) {
                throw ValidationException::withMessages([
                    'participant_email' => 'Registrasi event belum dibuka.',
                ]);
            }

            $now = now();
            $this->ensureRegistrationWindow($event, $now);

            $registeredCount = DB::table('event_registrations')
                ->where('project_id', $event->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($event->capacity !== null && $registeredCount >= (int) $event->capacity) {
                throw ValidationException::withMessages([
                    'participant_email' => 'Kuota registrasi event sudah penuh.',
                ]);
            }

            $email = strtolower($data['participant_email']);
            $duplicateExists = DB::table('event_registrations')
                ->where('project_id', $event->id)
                ->where('participant_email', $email)
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'participant_email' => 'Email ini sudah terdaftar untuk event ini.',
                ]);
            }

            $status = (bool) $event->require_payment ? 'pending' : 'confirmed';
            $registrationId = (int) DB::table('event_registrations')->insertGetId([
                'project_id' => (int) $event->id,
                'participant_name' => $data['participant_name'],
                'participant_email' => $email,
                'phone' => $data['phone'] ?? null,
                'institution' => $data['institution'] ?? null,
                'status' => $status,
                'registered_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Notification::route('mail', $email)->notify(new EventRegistrationConfirmedNotification(
                participantName: $data['participant_name'],
                projectName: (string) $event->name,
                organizationName: (string) $event->organization_name,
                status: $status,
            ));

            return $registrationId;
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureRegistrationWindow(object $event, Carbon $now): void
    {
        if ($event->opens_at !== null && Carbon::parse((string) $event->opens_at)->gt($now)) {
            throw ValidationException::withMessages([
                'participant_email' => 'Periode registrasi event belum dimulai.',
            ]);
        }

        if ($event->closes_at !== null && Carbon::parse((string) $event->closes_at)->lt($now)) {
            throw ValidationException::withMessages([
                'participant_email' => 'Periode registrasi event sudah ditutup.',
            ]);
        }
    }
}
