<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Letter\LetterType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class LetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $templates = [
            [LetterType::RoomReservation, 'Surat Permohonan Peminjaman Ruang', '<p>Nomor: {{letter_number}}</p><p>Yth. {{recipient_name}}<br>{{recipient_organization}}</p><p>Dengan hormat, kami dari {{org_name}} mengajukan peminjaman ruang untuk kegiatan {{project_name}} pada {{event_date}} di {{event_location}}.</p><p>Kontak panitia: {{contact_person}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
            [LetterType::ActivityPermit, 'Surat Permohonan Izin Kegiatan', '<p>Nomor: {{letter_number}}</p><p>Perihal: {{letter_subject}}</p><p>Kepada {{recipient_name}}, kami memohon izin pelaksanaan kegiatan {{project_name}} pada {{event_date}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
            [LetterType::CommitteeAssignment, 'Surat Tugas Panitia', '<p>Nomor: {{letter_number}}</p><p>Yang bertanda tangan di bawah ini menugaskan {{recipient_name}} sebagai panitia kegiatan {{project_name}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
            [LetterType::ParticipationCertificate, 'Surat Keterangan Partisipasi', '<p>Nomor: {{letter_number}}</p><p>Dengan ini menerangkan bahwa {{recipient_name}} telah berpartisipasi dalam kegiatan {{project_name}} pada {{event_date}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
            [LetterType::GuestInvitation, 'Surat Undangan Narasumber', '<p>Nomor: {{letter_number}}</p><p>Yth. {{recipient_name}}</p><p>Kami mengundang Bapak/Ibu untuk hadir sebagai narasumber pada kegiatan {{project_name}} pada {{event_date}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
            [LetterType::SponsorshipRequest, 'Surat Permohonan Sponsorship', '<p>Nomor: {{letter_number}}</p><p>Kepada {{recipient_organization}}</p><p>Kami mengajukan kerja sama sponsorship untuk kegiatan {{project_name}}. Detail koordinasi dapat menghubungi {{contact_person}}.</p><div class="signature"><p>{{signatory_role}}</p><br><br><p>{{signatory_name}}</p></div>'],
        ];

        foreach (DB::table('organizations')->get(['id', 'slug']) as $organization) {
            $signatoryUserId = DB::table('organization_members')
                ->where('organization_id', $organization->id)
                ->where('role', 'organization_owner')
                ->orderBy('id')
                ->value('user_id');

            foreach ($templates as [$type, $name, $html]) {
                DB::table('letter_templates')->updateOrInsert(
                    [
                        'organization_id' => $organization->id,
                        'letter_type' => $type->value,
                        'name' => $name,
                    ],
                    [
                        'template_html' => $html,
                        'numbering_pattern' => 'B.{seq}/'.strtoupper((string) $organization->slug).'/{type_code}/{roman_month}/{year}',
                        'signatory_user_id' => $signatoryUserId,
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    }
}
