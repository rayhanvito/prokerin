<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Document\DocumentVisibility;
use App\Domain\Finance\BudgetStatus;
use App\Domain\Membership\InvitationStatus;
use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Task\TaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DemoShowcaseSeeder extends Seeder
{
    private const PASSWORD = 'demo12345';

    private const ORGANIZATION_SLUG = 'bem-feb-nusantara-surabaya';

    /**
     * @var array<string, array{name: string, role: string, whatsapp: string}>
     */
    private const USERS = [
        'demo.owner@prokerin.test' => ['name' => 'Aurelia Putri', 'role' => 'organization_owner', 'whatsapp' => '+6281234567001'],
        'demo.admin@prokerin.test' => ['name' => 'Rizky Mahendra', 'role' => 'organization_admin', 'whatsapp' => '+6281234567002'],
        'demo.secretary@prokerin.test' => ['name' => 'Nabila Sekar', 'role' => 'secretary', 'whatsapp' => '+6281234567003'],
        'demo.treasurer@prokerin.test' => ['name' => 'Bagas Prakoso', 'role' => 'treasurer', 'whatsapp' => '+6281234567004'],
        'demo.lead@prokerin.test' => ['name' => 'Keisha Arum', 'role' => 'project_lead', 'whatsapp' => '+6281234567005'],
        'demo.coordinator@prokerin.test' => ['name' => 'Daffa Wicaksono', 'role' => 'division_coordinator', 'whatsapp' => '+6281234567006'],
        'demo.member@prokerin.test' => ['name' => 'Salma Kirana', 'role' => 'member', 'whatsapp' => '+6281234567007'],
        'demo.viewer@prokerin.test' => ['name' => 'Tara Lestari', 'role' => 'viewer', 'whatsapp' => '+6281234567008'],
    ];

    public function run(): void
    {
        $now = Carbon::parse('2026-05-17 09:00:00');

        $this->seedUsers($now);
        $this->seedOrganization($now);
        $this->seedPeriods($now);
        $this->seedMembers($now);
        $this->seedInvitations($now);
        $this->seedProjectTemplates($now);
        $this->seedProjects($now);
        $this->seedProjectMembers($now);
        $this->seedTasks($now);
        $this->seedDocuments($now);
        $this->seedBudgets($now);
        $this->seedProposalDrafts($now);
        $this->seedLpjChecklist($now);
        $this->seedMeetings($now);
        $this->seedAttendance($now);
        $this->seedCertificates($now);
        $this->seedSponsorsVendors($now);
        $this->seedTicketTiers($now);
        $this->seedEventRegistrations($now);
        $this->seedPaymentOrders($now);
        $this->seedNotificationRules($now);
        $this->seedHandover($now);
        $this->call(FeatureFlagSeeder::class);

        $this->command?->info('Demo showcase seed selesai.');
        $this->command?->table(
            ['Role', 'Email', 'Password'],
            collect(self::USERS)
                ->map(fn (array $user, string $email): array => [$user['role'], $email, self::PASSWORD])
                ->values()
                ->all(),
        );
    }

    private function seedUsers(Carbon $now): void
    {
        foreach (self::USERS as $email => $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $user['name'],
                    'whatsapp_number' => $user['whatsapp'],
                    'whatsapp_opt_in' => true,
                    'email_verified_at' => $now,
                    'last_login_at' => $now->copy()->subHours(random_int(1, 48)),
                    'password' => Hash::make(self::PASSWORD),
                    'remember_token' => Str::random(10),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOrganization(Carbon $now): void
    {
        DB::table('organizations')->updateOrInsert(
            ['slug' => self::ORGANIZATION_SLUG],
            [
                'name' => 'BEM FEB Nusantara Surabaya',
                'description' => 'Organisasi eksekutif mahasiswa yang mengelola program karier, kewirausahaan, advokasi, dan pengembangan minat bakat mahasiswa FEB.',
                'logo_path' => 'organizations/'.self::ORGANIZATION_SLUG.'/logo.png',
                'status' => 'active',
                'plan_tier' => 'pro',
                'internal_notes' => 'Tenant demo utama untuk showcase Prokerin. Data dibuat realistis untuk presentasi calon pengguna kampus.',
                'onboarding_completed_at' => $now->copy()->subDays(21),
                'updated_at' => $now,
                'created_at' => $now->copy()->subMonths(4),
            ],
        );
    }

    private function seedPeriods(Carbon $now): void
    {
        foreach ([
            ['name' => 'Kabinet Akselerasi 2025', 'starts_at' => '2025-01-01', 'ends_at' => '2025-12-31', 'is_active' => false],
            ['name' => 'Kabinet Harmoni Karya 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-12-31', 'is_active' => true],
        ] as $period) {
            DB::table('organization_periods')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'name' => $period['name'],
                ],
                [
                    'starts_at' => $period['starts_at'],
                    'ends_at' => $period['ends_at'],
                    'is_active' => $period['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subMonths(4),
                ],
            );
        }
    }

    private function seedMembers(Carbon $now): void
    {
        foreach (self::USERS as $email => $user) {
            DB::table('organization_members')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'user_id' => $this->userId($email),
                ],
                [
                    'role' => $user['role'],
                    'joined_at' => $now->copy()->subMonths(3)->addDays(array_search($email, array_keys(self::USERS), true) ?: 0),
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subMonths(3),
                ],
            );
        }
    }

    private function seedInvitations(Carbon $now): void
    {
        foreach ([
            ['email' => 'calon.media@bemfeb.test', 'role' => 'member', 'status' => InvitationStatus::Pending->value, 'days' => 5],
            ['email' => 'alumni.mentor@bemfeb.test', 'role' => 'viewer', 'status' => InvitationStatus::Accepted->value, 'days' => -2],
            ['email' => 'calon.sponsorship@bemfeb.test', 'role' => 'division_coordinator', 'status' => InvitationStatus::Expired->value, 'days' => -10],
        ] as $invite) {
            DB::table('organization_invitations')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'email' => $invite['email'],
                ],
                [
                    'role' => $invite['role'],
                    'status' => $invite['status'],
                    'token' => hash('sha256', 'demo-'.$invite['email']),
                    'expires_at' => $now->copy()->addDays($invite['days']),
                    'invited_by_user_id' => $this->userId('demo.admin@prokerin.test'),
                    'accepted_by_user_id' => null,
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subDays(3),
                ],
            );
        }
    }

    private function seedProjectTemplates(Carbon $now): void
    {
        foreach ([
            [
                'type' => 'seminar',
                'label' => 'Seminar',
                'proposal_outline' => 'Latar belakang, tujuan, tema, target peserta, rundown, RAB, publikasi, dan indikator keberhasilan.',
                'tasks' => ['Susun proposal', 'Konfirmasi narasumber', 'Publikasi peserta', 'Finalisasi venue', 'Dokumentasi dan LPJ'],
                'budget_lines' => ['Honor narasumber', 'Venue', 'Konsumsi', 'Publikasi', 'Dokumentasi'],
                'lpj_checklist' => ['Absensi peserta', 'Realisasi RAB', 'Dokumentasi', 'Evaluasi panitia', 'Lampiran sponsor'],
            ],
            [
                'type' => 'workshop',
                'label' => 'Workshop',
                'proposal_outline' => 'Tujuan praktik, modul belajar, kebutuhan mentor, kebutuhan alat, dan evaluasi hasil peserta.',
                'tasks' => ['Kunci mentor', 'Siapkan modul', 'Buka registrasi', 'Siapkan sertifikat', 'Kirim materi pasca acara'],
                'budget_lines' => ['Mentor', 'Toolkit', 'Konsumsi', 'Sertifikat', 'Dokumentasi'],
                'lpj_checklist' => ['Daftar hadir', 'Materi final', 'Rekap feedback', 'Realisasi biaya'],
            ],
            [
                'type' => 'competition',
                'label' => 'Lomba',
                'proposal_outline' => 'Tema lomba, kategori, juri, timeline seleksi, hadiah, sponsor, dan publikasi.',
                'tasks' => ['Buat guideline', 'Rekrut juri', 'Buka submission', 'Seleksi finalis', 'Awarding'],
                'budget_lines' => ['Hadiah', 'Juri', 'Platform', 'Publikasi', 'Trophy'],
                'lpj_checklist' => ['Rekap peserta', 'Berita acara juri', 'Dokumentasi awarding', 'Bukti hadiah'],
            ],
            [
                'type' => 'makrab',
                'label' => 'Makrab',
                'proposal_outline' => 'Tujuan internal, agenda bonding, pembagian fasilitator, keamanan, transportasi, dan konsumsi.',
                'tasks' => ['Survey lokasi', 'Susun rundown', 'Brief fasilitator', 'Konfirmasi transport', 'LPJ internal'],
                'budget_lines' => ['Akomodasi', 'Transportasi', 'Konsumsi', 'Perlengkapan', 'P3K'],
                'lpj_checklist' => ['Absensi panitia', 'Inventaris', 'Dokumentasi', 'Rekap evaluasi'],
            ],
        ] as $template) {
            DB::table('project_templates')->updateOrInsert(
                ['type' => $template['type']],
                [
                    'label' => $template['label'],
                    'proposal_outline' => $template['proposal_outline'],
                    'tasks' => json_encode($template['tasks']),
                    'budget_lines' => json_encode($template['budget_lines']),
                    'lpj_checklist' => json_encode($template['lpj_checklist']),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedProjects(Carbon $now): void
    {
        foreach ([
            [
                'template' => 'seminar',
                'lead' => 'demo.lead@prokerin.test',
                'name' => 'Surabaya Career Week 2026',
                'slug' => 'surabaya-career-week-2026',
                'description' => 'Rangkaian seminar, career mentoring, dan booth rekrutmen untuk membantu mahasiswa FEB masuk dunia kerja dengan lebih siap.',
                'status' => ProjectStatus::Running->value,
                'progress' => 68,
                'starts_at' => '2026-06-03',
                'ends_at' => '2026-06-05',
            ],
            [
                'template' => 'workshop',
                'lead' => 'demo.coordinator@prokerin.test',
                'name' => 'Bootcamp UMKM Muda: From Idea to Market',
                'slug' => 'bootcamp-umkm-muda-2026',
                'description' => 'Bootcamp tiga hari untuk mahasiswa yang ingin memvalidasi ide bisnis, membuat pitch deck, dan mencoba kanal penjualan digital.',
                'status' => ProjectStatus::RabApproval->value,
                'progress' => 44,
                'starts_at' => '2026-07-18',
                'ends_at' => '2026-07-20',
            ],
            [
                'template' => 'competition',
                'lead' => 'demo.lead@prokerin.test',
                'name' => 'National Business Case Challenge',
                'slug' => 'national-business-case-challenge',
                'description' => 'Kompetisi business case tingkat nasional dengan tema transformasi UMKM lokal dan ekonomi berkelanjutan.',
                'status' => ProjectStatus::ProposalReview->value,
                'progress' => 31,
                'starts_at' => '2026-09-12',
                'ends_at' => '2026-09-14',
            ],
            [
                'template' => 'seminar',
                'lead' => 'demo.admin@prokerin.test',
                'name' => 'Kelas CV dan LinkedIn Klinik',
                'slug' => 'kelas-cv-linkedin-klinik',
                'description' => 'Kelas kecil untuk review CV, simulasi wawancara, dan optimasi profil LinkedIn bersama alumni.',
                'status' => ProjectStatus::Completed->value,
                'progress' => 100,
                'starts_at' => '2026-03-08',
                'ends_at' => '2026-03-08',
            ],
        ] as $project) {
            DB::table('projects')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'slug' => $project['slug'],
                ],
                [
                    'organization_period_id' => $this->periodId('Kabinet Harmoni Karya 2026'),
                    'project_template_id' => $this->templateId($project['template']),
                    'project_lead_id' => $this->userId($project['lead']),
                    'name' => $project['name'],
                    'description' => $project['description'],
                    'status' => $project['status'],
                    'progress' => $project['progress'],
                    'starts_at' => $project['starts_at'],
                    'ends_at' => $project['ends_at'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(8),
                ],
            );
        }
    }

    private function seedProjectMembers(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.lead@prokerin.test', 'role' => ProjectRole::ProjectLead->value],
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.coordinator@prokerin.test', 'role' => ProjectRole::DivisionCoordinator->value],
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.secretary@prokerin.test', 'role' => ProjectRole::CommitteeMember->value],
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.treasurer@prokerin.test', 'role' => ProjectRole::CommitteeMember->value],
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.member@prokerin.test', 'role' => ProjectRole::CommitteeMember->value],
            ['project' => 'surabaya-career-week-2026', 'email' => 'demo.viewer@prokerin.test', 'role' => ProjectRole::Viewer->value],
            ['project' => 'bootcamp-umkm-muda-2026', 'email' => 'demo.coordinator@prokerin.test', 'role' => ProjectRole::ProjectLead->value],
            ['project' => 'bootcamp-umkm-muda-2026', 'email' => 'demo.member@prokerin.test', 'role' => ProjectRole::CommitteeMember->value],
            ['project' => 'national-business-case-challenge', 'email' => 'demo.lead@prokerin.test', 'role' => ProjectRole::ProjectLead->value],
            ['project' => 'kelas-cv-linkedin-klinik', 'email' => 'demo.admin@prokerin.test', 'role' => ProjectRole::ProjectLead->value],
        ] as $member) {
            DB::table('project_members')->updateOrInsert(
                [
                    'project_id' => $this->projectId($member['project']),
                    'user_id' => $this->userId($member['email']),
                ],
                [
                    'role' => $member['role'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(6),
                ],
            );
        }
    }

    private function seedTasks(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'title' => 'Lock venue Aula Garuda FEB', 'division' => 'Logistik', 'pic' => 'demo.coordinator@prokerin.test', 'status' => TaskStatus::Done->value, 'due' => '2026-05-08'],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Finalisasi MoU sponsor utama', 'division' => 'Sponsorship', 'pic' => 'demo.lead@prokerin.test', 'status' => TaskStatus::Review->value, 'due' => '2026-05-20'],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Publikasi batch kedua Instagram dan LinkedIn', 'division' => 'Media', 'pic' => 'demo.member@prokerin.test', 'status' => TaskStatus::InProgress->value, 'due' => '2026-05-21'],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Rekap registrasi peserta dan waiting list', 'division' => 'Kesekretariatan', 'pic' => 'demo.secretary@prokerin.test', 'status' => TaskStatus::Todo->value, 'due' => '2026-05-24'],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Upload invoice vendor photobooth', 'division' => 'Bendahara', 'pic' => 'demo.treasurer@prokerin.test', 'status' => TaskStatus::Blocked->value, 'due' => '2026-05-15'],
            ['project' => 'bootcamp-umkm-muda-2026', 'title' => 'Kirim RAB revisi ke bendahara umum', 'division' => 'Bendahara', 'pic' => 'demo.treasurer@prokerin.test', 'status' => TaskStatus::InProgress->value, 'due' => '2026-05-27'],
            ['project' => 'national-business-case-challenge', 'title' => 'Shortlist calon juri nasional', 'division' => 'Acara', 'pic' => 'demo.lead@prokerin.test', 'status' => TaskStatus::Backlog->value, 'due' => '2026-06-02'],
            ['project' => 'kelas-cv-linkedin-klinik', 'title' => 'Kirim sertifikat peserta batch 1', 'division' => 'Kesekretariatan', 'pic' => 'demo.secretary@prokerin.test', 'status' => TaskStatus::Done->value, 'due' => '2026-03-10'],
        ] as $task) {
            DB::table('project_tasks')->updateOrInsert(
                [
                    'project_id' => $this->projectId($task['project']),
                    'title' => $task['title'],
                ],
                [
                    'pic_user_id' => $this->userId($task['pic']),
                    'division' => $task['division'],
                    'status' => $task['status'],
                    'due_at' => $task['due'],
                    'completed_at' => $task['status'] === TaskStatus::Done->value ? $now->copy()->subDays(2) : null,
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(5),
                ],
            );
        }
    }

    private function seedDocuments(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'name' => 'proposal-career-week-final.pdf', 'folder' => 'Proposal', 'owner' => 'demo.secretary@prokerin.test', 'mime' => 'application/pdf', 'size' => 1840, 'visibility' => DocumentVisibility::Committee->value, 'status' => 'ready'],
            ['project' => 'surabaya-career-week-2026', 'name' => 'rab-career-week-approved.xlsx', 'folder' => 'Finance', 'owner' => 'demo.treasurer@prokerin.test', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size' => 728, 'visibility' => DocumentVisibility::Restricted->value, 'status' => 'approved'],
            ['project' => 'surabaya-career-week-2026', 'name' => 'invoice-venue-garuda.pdf', 'folder' => 'Finance Receipts', 'owner' => 'demo.treasurer@prokerin.test', 'mime' => 'application/pdf', 'size' => 512, 'visibility' => DocumentVisibility::Restricted->value, 'status' => 'verified'],
            ['project' => 'surabaya-career-week-2026', 'name' => 'media-kit-sponsor.zip', 'folder' => 'Sponsorship', 'owner' => 'demo.lead@prokerin.test', 'mime' => 'application/zip', 'size' => 4096, 'visibility' => DocumentVisibility::Private->value, 'status' => 'uploaded'],
            ['project' => 'kelas-cv-linkedin-klinik', 'name' => 'lpj-kelas-cv-linkedin.pdf', 'folder' => 'LPJ', 'owner' => 'demo.secretary@prokerin.test', 'mime' => 'application/pdf', 'size' => 2220, 'visibility' => DocumentVisibility::Committee->value, 'status' => 'ready'],
        ] as $document) {
            DB::table('documents')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'name' => $document['name'],
                ],
                [
                    'project_id' => $this->projectId($document['project']),
                    'owner_user_id' => $this->userId($document['owner']),
                    'folder' => $document['folder'],
                    'storage_path' => 'demo/'.self::ORGANIZATION_SLUG.'/'.$document['project'].'/'.$document['name'],
                    'mime_type' => $document['mime'],
                    'size_kb' => $document['size'],
                    'visibility' => $document['visibility'],
                    'status' => $document['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(4),
                ],
            );
        }
    }

    private function seedBudgets(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'name' => 'Venue Aula Garuda 3 hari', 'category' => 'Venue', 'planned' => 12000000, 'realized' => 6000000, 'status' => BudgetStatus::Realized->value],
            ['project' => 'surabaya-career-week-2026', 'name' => 'Honor narasumber dan moderator', 'category' => 'Program', 'planned' => 9000000, 'realized' => 0, 'status' => BudgetStatus::Approved->value],
            ['project' => 'surabaya-career-week-2026', 'name' => 'Konsumsi peserta dan panitia', 'category' => 'Konsumsi', 'planned' => 14500000, 'realized' => 5000000, 'status' => BudgetStatus::Review->value],
            ['project' => 'surabaya-career-week-2026', 'name' => 'Publikasi, ads, dan media kit', 'category' => 'Marketing', 'planned' => 4500000, 'realized' => 3250000, 'status' => BudgetStatus::Realized->value],
            ['project' => 'bootcamp-umkm-muda-2026', 'name' => 'Toolkit peserta bootcamp', 'category' => 'Program', 'planned' => 7500000, 'realized' => 0, 'status' => BudgetStatus::Draft->value],
            ['project' => 'kelas-cv-linkedin-klinik', 'name' => 'Mentor alumni dan snack', 'category' => 'Program', 'planned' => 3500000, 'realized' => 3425000, 'status' => BudgetStatus::Realized->value],
        ] as $line) {
            DB::table('budget_lines')->updateOrInsert(
                [
                    'project_id' => $this->projectId($line['project']),
                    'name' => $line['name'],
                ],
                [
                    'category' => $line['category'],
                    'planned_amount' => $line['planned'],
                    'realized_amount' => $line['realized'],
                    'status' => $line['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(4),
                ],
            );
        }

        foreach ([
            ['line' => 'Venue Aula Garuda 3 hari', 'name' => 'DP venue Aula Garuda', 'amount' => 6000000, 'document' => 'invoice-venue-garuda.pdf', 'status' => 'verified'],
            ['line' => 'Publikasi, ads, dan media kit', 'name' => 'Meta Ads early registration', 'amount' => 1250000, 'document' => 'rab-career-week-approved.xlsx', 'status' => 'verified'],
            ['line' => 'Konsumsi peserta dan panitia', 'name' => 'DP katering hari pertama', 'amount' => 5000000, 'document' => 'rab-career-week-approved.xlsx', 'status' => 'review'],
            ['line' => 'Mentor alumni dan snack', 'name' => 'Pelunasan klinik CV', 'amount' => 3425000, 'document' => 'lpj-kelas-cv-linkedin.pdf', 'status' => 'verified'],
        ] as $transaction) {
            DB::table('budget_transactions')->updateOrInsert(
                [
                    'budget_line_id' => $this->budgetLineId($transaction['line']),
                    'name' => $transaction['name'],
                ],
                [
                    'receipt_document_id' => $this->documentId($transaction['document']),
                    'amount' => $transaction['amount'],
                    'status' => $transaction['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(2),
                ],
            );
        }
    }

    private function seedProposalDrafts(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'title' => 'Proposal Surabaya Career Week 2026', 'subtitle' => 'Connecting Students, Alumni, and Industry', 'status' => 'submitted'],
            ['project' => 'bootcamp-umkm-muda-2026', 'title' => 'Proposal Bootcamp UMKM Muda 2026', 'subtitle' => 'From Idea to Market', 'status' => 'draft'],
            ['project' => 'national-business-case-challenge', 'title' => 'Proposal National Business Case Challenge', 'subtitle' => 'Solusi Mahasiswa untuk UMKM Berkelanjutan', 'status' => 'revision_requested'],
        ] as $draft) {
            DB::table('proposal_drafts')->updateOrInsert(
                [
                    'project_id' => $this->projectId($draft['project']),
                    'title' => $draft['title'],
                ],
                [
                    'subtitle' => $draft['subtitle'],
                    'sections' => json_encode([
                        ['heading' => 'Latar Belakang', 'body' => 'Kebutuhan mahasiswa untuk memperoleh pengalaman praktis dan jejaring profesional semakin tinggi. Program ini menjawab kebutuhan itu dengan format yang terukur.'],
                        ['heading' => 'Tujuan', 'body' => 'Membuka akses mentoring, validasi karya, dan koneksi industri untuk mahasiswa lintas angkatan.'],
                        ['heading' => 'Indikator Keberhasilan', 'body' => 'Minimal 250 pendaftar, 90 persen kehadiran, 30 mitra perusahaan/alumni, dan seluruh dokumen LPJ selesai H+7.'],
                    ]),
                    'status' => $draft['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(5),
                ],
            );
        }
    }

    private function seedLpjChecklist(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'title' => 'Absensi peserta dan panitia dari QR attendance', 'complete' => true],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Realisasi RAB diverifikasi bendahara', 'complete' => false],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Dokumentasi kegiatan terunggah ke folder LPJ', 'complete' => false],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Evaluasi narasumber dan peserta direkap', 'complete' => false],
            ['project' => 'kelas-cv-linkedin-klinik', 'title' => 'LPJ final disetujui ketua organisasi', 'complete' => true],
            ['project' => 'kelas-cv-linkedin-klinik', 'title' => 'Sertifikat peserta terkirim', 'complete' => true],
        ] as $item) {
            DB::table('lpj_checklist_items')->updateOrInsert(
                [
                    'project_id' => $this->projectId($item['project']),
                    'title' => $item['title'],
                ],
                [
                    'is_required' => true,
                    'is_complete' => $item['complete'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(2),
                ],
            );
        }
    }

    private function seedMeetings(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'title' => 'Weekly Sync Career Week #5', 'agenda' => 'Cek sponsor, update registrasi, status venue, dan risiko vendor photobooth.', 'location' => 'Ruang BEM FEB Lt. 2', 'starts_at' => '2026-05-18 15:30:00', 'ends_at' => '2026-05-18 17:00:00', 'status' => 'planned', 'creator' => 'demo.secretary@prokerin.test'],
            ['project' => 'surabaya-career-week-2026', 'title' => 'Review RAB dan Sponsor Career Week', 'agenda' => 'Validasi realisasi sementara, paket sponsor, dan cashflow H-14.', 'location' => 'Google Meet', 'starts_at' => '2026-05-12 19:00:00', 'ends_at' => '2026-05-12 20:10:00', 'status' => 'completed', 'creator' => 'demo.treasurer@prokerin.test'],
            ['project' => 'bootcamp-umkm-muda-2026', 'title' => 'Kickoff Bootcamp UMKM Muda', 'agenda' => 'Pembagian divisi, timeline mentor, dan rencana publikasi.', 'location' => 'Cafe Co-Working MERR', 'starts_at' => '2026-05-23 10:00:00', 'ends_at' => '2026-05-23 12:00:00', 'status' => 'planned', 'creator' => 'demo.coordinator@prokerin.test'],
        ] as $meeting) {
            DB::table('meetings')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'title' => $meeting['title'],
                ],
                [
                    'project_id' => $this->projectId($meeting['project']),
                    'created_by_user_id' => $this->userId($meeting['creator']),
                    'agenda' => $meeting['agenda'],
                    'location' => $meeting['location'],
                    'starts_at' => $meeting['starts_at'],
                    'ends_at' => $meeting['ends_at'],
                    'status' => $meeting['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(3),
                ],
            );
        }

        foreach ([
            ['meeting' => 'Weekly Sync Career Week #5', 'email' => 'demo.lead@prokerin.test', 'role' => 'Ketua Pelaksana', 'status' => 'invited'],
            ['meeting' => 'Weekly Sync Career Week #5', 'email' => 'demo.secretary@prokerin.test', 'role' => 'Notulis', 'status' => 'invited'],
            ['meeting' => 'Weekly Sync Career Week #5', 'email' => 'demo.treasurer@prokerin.test', 'role' => 'Bendahara', 'status' => 'invited'],
            ['meeting' => 'Weekly Sync Career Week #5', 'email' => 'demo.coordinator@prokerin.test', 'role' => 'Koordinator Logistik', 'status' => 'invited'],
            ['meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.owner@prokerin.test', 'role' => 'Ketua BEM', 'status' => 'present'],
            ['meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.treasurer@prokerin.test', 'role' => 'Bendahara', 'status' => 'present'],
            ['meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.lead@prokerin.test', 'role' => 'Ketua Pelaksana', 'status' => 'present'],
        ] as $attendee) {
            DB::table('meeting_attendees')->updateOrInsert(
                [
                    'meeting_id' => $this->meetingId($attendee['meeting']),
                    'user_id' => $this->userId($attendee['email']),
                ],
                [
                    'name' => $this->userName($attendee['email']),
                    'role' => $attendee['role'],
                    'attendance_status' => $attendee['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(3),
                ],
            );
        }

        DB::table('meeting_minutes')->updateOrInsert(
            ['meeting_id' => $this->meetingId('Review RAB dan Sponsor Career Week')],
            [
                'created_by_user_id' => $this->userId('demo.secretary@prokerin.test'),
                'summary' => 'RAB Career Week dinilai aman dengan catatan sponsor silver harus closing sebelum H-10. Venue sudah dibayar DP dan paket konsumsi menunggu final count peserta.',
                'decisions' => json_encode([
                    'Paket sponsor silver diberi deadline final 22 Mei 2026.',
                    'Publikasi batch kedua tetap jalan dengan budget ads Rp1.250.000.',
                    'Konsumsi dikunci setelah peserta confirmed mencapai 250 orang.',
                ]),
                'action_items' => json_encode([
                    ['task' => 'Follow up sponsor silver Telkom Campus', 'owner' => 'Keisha Arum', 'due' => '2026-05-22', 'status' => 'open'],
                    ['task' => 'Upload invoice venue dan bukti DP', 'owner' => 'Bagas Prakoso', 'due' => '2026-05-18', 'status' => 'done'],
                ]),
                'published_at' => '2026-05-12 21:00:00',
                'updated_at' => $now,
                'created_at' => $now->copy()->subDays(5),
            ],
        );
    }

    private function seedAttendance(Carbon $now): void
    {
        foreach ([
            ['project' => 'surabaya-career-week-2026', 'meeting' => 'Weekly Sync Career Week #5', 'title' => 'Absensi Weekly Sync Career Week #5', 'starts_at' => '2026-05-18 15:15:00', 'ends_at' => '2026-05-18 17:15:00', 'status' => 'open', 'creator' => 'demo.secretary@prokerin.test'],
            ['project' => 'surabaya-career-week-2026', 'meeting' => 'Review RAB dan Sponsor Career Week', 'title' => 'Absensi Review RAB Career Week', 'starts_at' => '2026-05-12 18:45:00', 'ends_at' => '2026-05-12 20:20:00', 'status' => 'closed', 'creator' => 'demo.treasurer@prokerin.test'],
        ] as $session) {
            DB::table('attendance_sessions')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'title' => $session['title'],
                ],
                [
                    'project_id' => $this->projectId($session['project']),
                    'meeting_id' => $this->meetingId($session['meeting']),
                    'created_by_user_id' => $this->userId($session['creator']),
                    'starts_at' => $session['starts_at'],
                    'ends_at' => $session['ends_at'],
                    'status' => $session['status'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(2),
                ],
            );
        }

        DB::table('attendance_qr_tokens')->updateOrInsert(
            ['token_hash' => hash('sha256', 'demo-career-week-sync-token')],
            [
                'attendance_session_id' => $this->attendanceSessionId('Absensi Weekly Sync Career Week #5'),
                'expires_at' => $now->copy()->addDays(7),
                'last_used_at' => null,
                'revoked_at' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        foreach ([
            ['session' => 'Absensi Review RAB Career Week', 'meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.owner@prokerin.test', 'method' => 'qr', 'time' => '2026-05-12 18:54:00'],
            ['session' => 'Absensi Review RAB Career Week', 'meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.treasurer@prokerin.test', 'method' => 'manual', 'time' => '2026-05-12 18:50:00'],
            ['session' => 'Absensi Review RAB Career Week', 'meeting' => 'Review RAB dan Sponsor Career Week', 'email' => 'demo.lead@prokerin.test', 'method' => 'qr', 'time' => '2026-05-12 18:58:00'],
        ] as $record) {
            DB::table('attendance_records')->updateOrInsert(
                [
                    'attendance_session_id' => $this->attendanceSessionId($record['session']),
                    'user_id' => $this->userId($record['email']),
                ],
                [
                    'meeting_attendee_id' => $this->meetingAttendeeId($record['meeting'], $record['email']),
                    'attendee_name' => $this->userName($record['email']),
                    'attendee_email' => $record['email'],
                    'check_in_method' => $record['method'],
                    'checked_in_at' => $record['time'],
                    'status' => 'present',
                    'notes' => 'Data demo absensi rapat.',
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subDays(5),
                ],
            );
        }
    }

    private function seedCertificates(Carbon $now): void
    {
        DB::table('certificate_templates')->updateOrInsert(
            [
                'organization_id' => $this->organizationId(),
                'name' => 'Sertifikat Career Week Premium',
            ],
            [
                'description' => 'Template sertifikat modern untuk peserta, panitia, dan narasumber Career Week.',
                'template_html' => '<h1>Sertifikat Apresiasi</h1><p>Nomor: {{certificate_number}}</p><p>Diberikan kepada</p><h2>{{recipient_name}}</h2><p>atas kontribusi pada {{project_name}} oleh {{organization_name}}.</p><p>{{signature_label}}<br><strong>{{signature_name}}</strong></p><small>{{verification_url}}</small>',
                'signature_label' => 'Presiden BEM FEB Nusantara Surabaya',
                'signature_name' => 'Aurelia Putri',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now->copy()->subWeeks(3),
            ],
        );

        foreach ([
            ['number' => 'DEMO-CW-2026-0001', 'email' => 'demo.lead@prokerin.test', 'name' => 'Keisha Arum', 'token' => 'a1111111-1111-4111-8111-111111111111'],
            ['number' => 'DEMO-CW-2026-0002', 'email' => 'demo.secretary@prokerin.test', 'name' => 'Nabila Sekar', 'token' => 'a2222222-2222-4222-8222-222222222222'],
            ['number' => 'DEMO-CW-2026-0003', 'email' => 'demo.member@prokerin.test', 'name' => 'Salma Kirana', 'token' => 'a3333333-3333-4333-8333-333333333333'],
        ] as $certificate) {
            DB::table('certificate_recipients')->updateOrInsert(
                ['certificate_number' => $certificate['number']],
                [
                    'organization_id' => $this->organizationId(),
                    'template_id' => $this->certificateTemplateId('Sertifikat Career Week Premium'),
                    'user_id' => $this->userId($certificate['email']),
                    'recipient_name' => $certificate['name'],
                    'recipient_email' => $certificate['email'],
                    'project_id' => $this->projectId('surabaya-career-week-2026'),
                    'meeting_id' => $this->meetingId('Review RAB dan Sponsor Career Week'),
                    'issued_at' => $now->copy()->subDays(1),
                    'issued_by' => $this->userId('demo.owner@prokerin.test'),
                    'verification_token' => $certificate['token'],
                    'pdf_path' => 'certificates/demo/'.$certificate['number'].'.pdf',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedSponsorsVendors(Carbon $now): void
    {
        foreach ([
            ['type' => 'sponsor', 'name' => 'Bank Jatim Young Leaders', 'category' => 'Gold sponsor', 'contact' => 'Maya Anindya', 'phone' => '+6285711100200', 'email' => 'partnership@bankjatim-demo.test', 'address' => 'Jl. Basuki Rahmat, Surabaya', 'status' => 'active', 'notes' => 'Sponsor utama Career Week. Fokus pada talent pipeline fresh graduate.', 'role' => 'Gold sponsor dan booth literasi karier', 'amount' => 18000000, 'document' => 'proposal-career-week-final.pdf'],
            ['type' => 'sponsor', 'name' => 'Telkom Campus Connect', 'category' => 'Silver sponsor', 'contact' => 'Arga Satria', 'phone' => '+6281211100300', 'email' => 'campus@telkom-demo.test', 'address' => 'Jl. Ketintang, Surabaya', 'status' => 'prospect', 'notes' => 'Menunggu approval final paket booth dan talkshow data analytics.', 'role' => 'Silver sponsor booth rekrutmen', 'amount' => 9000000, 'document' => 'media-kit-sponsor.zip'],
            ['type' => 'vendor', 'name' => 'Garuda Convention Hall', 'category' => 'Venue', 'contact' => 'Dewi Larasati', 'phone' => '+6282233300400', 'email' => 'booking@garuda-hall.test', 'address' => 'Kampus FEB Nusantara, Surabaya', 'status' => 'active', 'notes' => 'Venue sudah DP, kapasitas 450 pax, include basic sound.', 'role' => 'Venue utama Career Week', 'amount' => 12000000, 'document' => 'invoice-venue-garuda.pdf'],
        ] as $contact) {
            DB::table('sponsors_vendors')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'name' => $contact['name'],
                ],
                [
                    'type' => $contact['type'],
                    'category' => $contact['category'],
                    'contact_person' => $contact['contact'],
                    'phone' => $contact['phone'],
                    'email' => $contact['email'],
                    'address' => $contact['address'],
                    'status' => $contact['status'],
                    'notes' => $contact['notes'],
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(5),
                ],
            );

            DB::table('sponsor_vendor_project_links')->updateOrInsert(
                [
                    'sponsor_vendor_id' => $this->sponsorVendorId($contact['name']),
                    'project_id' => $this->projectId('surabaya-career-week-2026'),
                ],
                [
                    'role_description' => $contact['role'],
                    'amount' => $contact['amount'],
                    'linked_at' => $now->copy()->subWeeks(2),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('sponsor_vendor_documents')->updateOrInsert(
                [
                    'sponsor_vendor_id' => $this->sponsorVendorId($contact['name']),
                    'document_id' => $this->documentId($contact['document']),
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedEventRegistrations(Carbon $now): void
    {
        DB::table('event_registration_settings')->updateOrInsert(
            ['project_id' => $this->projectId('surabaya-career-week-2026')],
            [
                'is_open' => true,
                'capacity' => 320,
                'opens_at' => '2026-05-01 08:00:00',
                'closes_at' => '2026-06-01 23:59:00',
                'require_payment' => true,
                'updated_at' => $now,
                'created_at' => $now->copy()->subWeeks(3),
            ],
        );

        foreach ([
            ['name' => 'Alya Rahma', 'email' => 'alya.rahma@student.unair.test', 'phone' => '+6282112000001', 'institution' => 'Universitas Airlangga', 'status' => 'confirmed', 'tier' => 'Early Bird'],
            ['name' => 'Bima Prakoso', 'email' => 'bima.prakoso@student.its.test', 'phone' => '+6282112000002', 'institution' => 'Institut Teknologi Sepuluh Nopember', 'status' => 'confirmed', 'tier' => 'Early Bird'],
            ['name' => 'Citra Anggraini', 'email' => 'citra.anggraini@student.ub.test', 'phone' => '+6282112000003', 'institution' => 'Universitas Brawijaya', 'status' => 'pending', 'tier' => 'Regular'],
            ['name' => 'Dion Saputra', 'email' => 'dion.saputra@student.upn.test', 'phone' => '+6282112000004', 'institution' => 'UPN Veteran Jawa Timur', 'status' => 'waiting_list', 'tier' => 'Free Pass'],
            ['name' => 'Elena Wulandari', 'email' => 'elena.wulandari@student.unesa.test', 'phone' => '+6282112000005', 'institution' => 'Universitas Negeri Surabaya', 'status' => 'confirmed', 'tier' => 'Regular'],
        ] as $registration) {
            DB::table('event_registrations')->updateOrInsert(
                [
                    'project_id' => $this->projectId('surabaya-career-week-2026'),
                    'participant_email' => $registration['email'],
                ],
                [
                    'ticket_tier_id' => $this->ticketTierId($registration['tier']),
                    'participant_name' => $registration['name'],
                    'phone' => $registration['phone'],
                    'institution' => $registration['institution'],
                    'status' => $registration['status'],
                    'registered_at' => $now->copy()->subDays(random_int(1, 9)),
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subDays(9),
                ],
            );
        }
    }

    private function seedTicketTiers(Carbon $now): void
    {
        foreach ([
            ['name' => 'Free Pass', 'price' => 0, 'capacity' => 80],
            ['name' => 'Early Bird', 'price' => 35000, 'capacity' => 160],
            ['name' => 'Regular', 'price' => 50000, 'capacity' => 80],
        ] as $tier) {
            DB::table('ticket_tiers')->updateOrInsert(
                [
                    'project_id' => $this->projectId('surabaya-career-week-2026'),
                    'name' => $tier['name'],
                ],
                [
                    'price' => $tier['price'],
                    'capacity' => $tier['capacity'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(4),
                ],
            );
        }
    }

    private function seedPaymentOrders(Carbon $now): void
    {
        foreach ([
            ['email' => 'alya.rahma@student.unair.test', 'tier' => 'Early Bird', 'amount' => 35000, 'status' => 'paid', 'paid' => true],
            ['email' => 'bima.prakoso@student.its.test', 'tier' => 'Early Bird', 'amount' => 35000, 'status' => 'paid', 'paid' => true],
            ['email' => 'citra.anggraini@student.ub.test', 'tier' => 'Regular', 'amount' => 50000, 'status' => 'pending', 'paid' => false],
            ['email' => 'elena.wulandari@student.unesa.test', 'tier' => 'Regular', 'amount' => 50000, 'status' => 'paid', 'paid' => true],
        ] as $order) {
            DB::table('payment_orders')->updateOrInsert(
                ['provider_order_id' => 'DEMO-CW-'.$this->registrationId($order['email'])],
                [
                    'registration_id' => $this->registrationId($order['email']),
                    'tier_id' => $this->ticketTierId($order['tier']),
                    'amount' => $order['amount'],
                    'status' => $order['status'],
                    'paid_at' => $order['paid'] ? $now->copy()->subDays(2) : null,
                    'expires_at' => $order['paid'] ? null : $now->copy()->addDay(),
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subDays(6),
                ],
            );
        }
    }

    private function seedNotificationRules(Carbon $now): void
    {
        foreach ([
            ['event' => 'task.deadline.minus_2_days', 'label' => 'Task deadline H-2', 'audience' => 'PIC task dan project lead', 'channels' => ['database', 'whatsapp'], 'trigger' => 'Setiap pagi jam 08.00', 'status' => 'active'],
            ['event' => 'budget.review.requested', 'label' => 'RAB menunggu review bendahara', 'audience' => 'Treasurer dan owner', 'channels' => ['database'], 'trigger' => 'Saat budget line masuk status review', 'status' => 'active'],
            ['event' => 'lpj.incomplete.h_plus_7', 'label' => 'LPJ belum lengkap H+7', 'audience' => 'Secretary dan project lead', 'channels' => ['database', 'email'], 'trigger' => 'Tujuh hari setelah ends_at proker', 'status' => 'active'],
        ] as $rule) {
            DB::table('notification_rules')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId(),
                    'event' => $rule['event'],
                ],
                [
                    'label' => $rule['label'],
                    'audience' => $rule['audience'],
                    'channels' => json_encode($rule['channels']),
                    'trigger' => $rule['trigger'],
                    'status' => $rule['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedHandover(Carbon $now): void
    {
        DB::table('handover_packages')->updateOrInsert(
            [
                'organization_id' => $this->organizationId(),
                'from_period_id' => $this->periodId('Kabinet Akselerasi 2025'),
                'to_period_id' => $this->periodId('Kabinet Harmoni Karya 2026'),
            ],
            [
                'created_by' => $this->userId('demo.owner@prokerin.test'),
                'status' => 'submitted',
                'snapshot' => json_encode([
                    'projects' => 4,
                    'documents' => 5,
                    'open_tasks' => 5,
                    'planned_budget' => 50500000,
                    'realized_budget' => 17750000,
                    'outstanding_lpj_items' => 3,
                ]),
                'submitted_at' => $now->copy()->subDays(2),
                'accepted_at' => null,
                'updated_at' => $now,
                'created_at' => $now->copy()->subWeeks(2),
            ],
        );

        foreach ([
            ['category' => 'Program', 'label' => 'Peta proker prioritas 2026', 'description' => 'Career Week, UMKM Bootcamp, dan Business Case Challenge sudah punya PIC dan dokumen awal.', 'status' => 'done', 'assignee' => 'demo.lead@prokerin.test'],
            ['category' => 'Finance', 'label' => 'Rekap RAB dan invoice vendor', 'description' => 'Perlu finalisasi bukti sponsor silver dan pelunasan konsumsi.', 'status' => 'in_progress', 'assignee' => 'demo.treasurer@prokerin.test'],
            ['category' => 'Document', 'label' => 'Folder proposal dan LPJ', 'description' => 'Dokumen utama sudah terunggah, dokumentasi foto final menyusul H+1.', 'status' => 'in_progress', 'assignee' => 'demo.secretary@prokerin.test'],
        ] as $item) {
            DB::table('handover_items')->updateOrInsert(
                [
                    'package_id' => $this->handoverPackageId(),
                    'label' => $item['label'],
                ],
                [
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'status' => $item['status'],
                    'assignee_id' => $this->userId($item['assignee']),
                    'updated_at' => $now,
                    'created_at' => $now->copy()->subWeeks(2),
                ],
            );
        }
    }

    private function userId(string $email): int
    {
        return (int) DB::table('users')->where('email', $email)->value('id');
    }

    private function userName(string $email): string
    {
        return (string) DB::table('users')->where('email', $email)->value('name');
    }

    private function organizationId(): int
    {
        return (int) DB::table('organizations')->where('slug', self::ORGANIZATION_SLUG)->value('id');
    }

    private function periodId(string $name): int
    {
        return (int) DB::table('organization_periods')
            ->where('organization_id', $this->organizationId())
            ->where('name', $name)
            ->value('id');
    }

    private function templateId(string $type): int
    {
        return (int) DB::table('project_templates')->where('type', $type)->value('id');
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')
            ->where('organization_id', $this->organizationId())
            ->where('slug', $slug)
            ->value('id');
    }

    private function documentId(string $name): int
    {
        return (int) DB::table('documents')
            ->where('organization_id', $this->organizationId())
            ->where('name', $name)
            ->value('id');
    }

    private function budgetLineId(string $name): int
    {
        return (int) DB::table('budget_lines')->where('name', $name)->value('id');
    }

    private function meetingId(string $title): int
    {
        return (int) DB::table('meetings')
            ->where('organization_id', $this->organizationId())
            ->where('title', $title)
            ->value('id');
    }

    private function meetingAttendeeId(string $meetingTitle, string $email): int
    {
        return (int) DB::table('meeting_attendees')
            ->where('meeting_id', $this->meetingId($meetingTitle))
            ->where('user_id', $this->userId($email))
            ->value('id');
    }

    private function attendanceSessionId(string $title): int
    {
        return (int) DB::table('attendance_sessions')
            ->where('organization_id', $this->organizationId())
            ->where('title', $title)
            ->value('id');
    }

    private function certificateTemplateId(string $name): int
    {
        return (int) DB::table('certificate_templates')
            ->where('organization_id', $this->organizationId())
            ->where('name', $name)
            ->value('id');
    }

    private function sponsorVendorId(string $name): int
    {
        return (int) DB::table('sponsors_vendors')
            ->where('organization_id', $this->organizationId())
            ->where('name', $name)
            ->value('id');
    }

    private function ticketTierId(string $name): int
    {
        return (int) DB::table('ticket_tiers')
            ->where('project_id', $this->projectId('surabaya-career-week-2026'))
            ->where('name', $name)
            ->value('id');
    }

    private function registrationId(string $email): int
    {
        return (int) DB::table('event_registrations')
            ->where('project_id', $this->projectId('surabaya-career-week-2026'))
            ->where('participant_email', $email)
            ->value('id');
    }

    private function handoverPackageId(): int
    {
        return (int) DB::table('handover_packages')
            ->where('organization_id', $this->organizationId())
            ->where('from_period_id', $this->periodId('Kabinet Akselerasi 2025'))
            ->where('to_period_id', $this->periodId('Kabinet Harmoni Karya 2026'))
            ->value('id');
    }
}
