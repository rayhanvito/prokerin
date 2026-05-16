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
     * @param  array{participant_name: string, participant_email: string, phone?: string|null, institution?: string|null, ticket_tier_id?: int|null}  $data
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

            $ticketTierId = filled($data['ticket_tier_id'] ?? null) ? (int) $data['ticket_tier_id'] : null;
            $tier = $this->resolveTicketTier((int) $event->id, (bool) $event->require_payment, $ticketTierId);
            $this->ensureTierCapacity($tier);

            $status = $tier !== null && (int) $tier->price > 0 ? 'pending' : 'confirmed';
            $registrationId = (int) DB::table('event_registrations')->insertGetId([
                'project_id' => (int) $event->id,
                'ticket_tier_id' => $tier?->id,
                'participant_name' => $data['participant_name'],
                'participant_email' => $email,
                'phone' => $data['phone'] ?? null,
                'institution' => $data['institution'] ?? null,
                'status' => $status,
                'registered_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($tier !== null && (int) $tier->price > 0) {
                DB::table('payment_orders')->insert([
                    'registration_id' => $registrationId,
                    'tier_id' => (int) $tier->id,
                    'amount' => (int) $tier->price,
                    'status' => 'pending',
                    'provider_order_id' => $this->providerOrderId($registrationId),
                    'paid_at' => null,
                    'expires_at' => $now->copy()->addDay(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

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
    private function resolveTicketTier(int $projectId, bool $requirePayment, ?int $ticketTierId): ?object
    {
        if ($ticketTierId === null) {
            if ($requirePayment) {
                throw ValidationException::withMessages([
                    'ticket_tier_id' => 'Pilih kategori tiket untuk event ini.',
                ]);
            }

            return null;
        }

        $tier = DB::table('ticket_tiers')
            ->where('id', $ticketTierId)
            ->where('project_id', $projectId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first(['id', 'price', 'capacity']);

        if ($tier === null) {
            throw ValidationException::withMessages([
                'ticket_tier_id' => 'Kategori tiket tidak tersedia untuk event ini.',
            ]);
        }

        return $tier;
    }

    /**
     * @throws ValidationException
     */
    private function ensureTierCapacity(?object $tier): void
    {
        if ($tier === null || $tier->capacity === null) {
            return;
        }

        $registeredCount = DB::table('event_registrations')
            ->where('ticket_tier_id', $tier->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($registeredCount >= (int) $tier->capacity) {
            throw ValidationException::withMessages([
                'ticket_tier_id' => 'Kuota kategori tiket ini sudah penuh.',
            ]);
        }
    }

    private function providerOrderId(int $registrationId): string
    {
        return 'PRK-M22-'.$registrationId.'-'.now()->format('YmdHis');
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
