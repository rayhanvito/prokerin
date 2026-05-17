<?php

declare(strict_types=1);

namespace App\Domain\Letter;

enum LetterType: string
{
    case RoomReservation = 'room_reservation';
    case ActivityPermit = 'activity_permit';
    case CommitteeAssignment = 'committee_assignment';
    case ParticipationCertificate = 'participation_certificate';
    case GuestInvitation = 'guest_invitation';
    case SponsorshipRequest = 'sponsorship_request';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::RoomReservation => 'Peminjaman Ruang',
            self::ActivityPermit => 'Perizinan Kegiatan',
            self::CommitteeAssignment => 'Surat Tugas Panitia',
            self::ParticipationCertificate => 'Surat Keterangan Partisipasi',
            self::GuestInvitation => 'Undangan Narasumber',
            self::SponsorshipRequest => 'Permohonan Sponsorship',
            self::Custom => 'Surat Custom',
        };
    }

    public function typeCode(): string
    {
        return match ($this) {
            self::RoomReservation => 'PR',
            self::ActivityPermit => 'PK',
            self::CommitteeAssignment => 'ST',
            self::ParticipationCertificate => 'SK',
            self::GuestInvitation => 'UN',
            self::SponsorshipRequest => 'SP',
            self::Custom => 'CS',
        };
    }
}
