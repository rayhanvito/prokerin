<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Actions\Membership\GetRolePermissionMatrixAction;
use App\Actions\Notification\GetDefaultNotificationRulesAction;
use App\Actions\Project\BuildProjectTemplatePlanAction;
use App\Actions\Proposal\BuildProposalDraftAction;
use App\Domain\Document\DocumentVisibility;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\Domain\Finance\BudgetStatus;
use App\Domain\Membership\InvitationStatus;
use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ProjectTemplateType;
use App\Domain\Task\TaskStatus;
use App\DTOs\DocumentExport\ExportRequestData;
use App\DTOs\Proposal\ProposalProjectData;
use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $this->seedUsers($now);
        $this->seedOrganizations($now);
        $this->seedOrganizationPeriods($now);
        $this->seedOrganizationMembers($now);
        $this->seedOrganizationInvitations($now);
        $this->seedRolePermissionMatrix($now);
        $this->seedProjectTemplates($now);
        $this->seedProjects($now);
        $this->seedProjectMembers($now);
        $this->seedProjectTasks($now);
        $this->seedDocuments($now);
        $this->seedSponsorsVendors($now);
        $this->seedBudgetLinesAndTransactions($now);
        $this->seedProposalDrafts($now);
        $this->seedLpjChecklist($now);
        $this->seedMeetings($now);
        $this->seedAttendance($now);
        $this->seedCertificates($now);
        $this->seedEventRegistrations($now);
        $this->seedTicketTiers($now);
        $this->seedNotificationRules($now);
        $this->seedDocumentExports($now);
    }

    private function seedUsers($now): void
    {
        foreach ([
            ['name' => 'Dimas Aji', 'email' => 'owner@prokerin.test', 'whatsapp_number' => '+628111111111'],
            ['name' => 'Nadia Putri', 'email' => 'admin@prokerin.test', 'whatsapp_number' => '+628122222222'],
            ['name' => 'Salsa Kirana', 'email' => 'sekretaris@prokerin.test', 'whatsapp_number' => '+628133333333'],
            ['name' => 'Raka Pratama', 'email' => 'bendahara@prokerin.test', 'whatsapp_number' => '+628144444444'],
            ['name' => 'Fajar Nugroho', 'email' => 'lead@prokerin.test', 'whatsapp_number' => '+628155555555'],
            ['name' => 'Mira Anggraini', 'email' => 'koordinator@prokerin.test', 'whatsapp_number' => '+628166666666'],
            ['name' => 'Ardi Saputra', 'email' => 'member@prokerin.test', 'whatsapp_number' => '+628177777777'],
            ['name' => 'Tari Lestari', 'email' => 'viewer@prokerin.test', 'whatsapp_number' => '+628188888888'],
            ['name' => 'Test User', 'email' => 'test@example.com', 'whatsapp_number' => null],
        ] as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'whatsapp_number' => $user['whatsapp_number'],
                    'email_verified_at' => $now,
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOrganizations($now): void
    {
        foreach ([
            ['name' => 'BEM Fakultas Teknologi', 'slug' => 'bem-fakultas-teknologi', 'plan_tier' => 'pro'],
            ['name' => 'HIMA Informatika', 'slug' => 'hima-informatika', 'plan_tier' => 'free'],
            ['name' => 'UKM Kreatif', 'slug' => 'ukm-kreatif', 'plan_tier' => 'free'],
        ] as $organization) {
            DB::table('organizations')->updateOrInsert(
                ['slug' => $organization['slug']],
                [
                    'name' => $organization['name'],
                    'logo_path' => 'organizations/'.$organization['slug'].'/logo.png',
                    'status' => 'active',
                    'plan_tier' => $organization['plan_tier'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOrganizationPeriods($now): void
    {
        foreach ([
            ['org' => 'bem-fakultas-teknologi', 'name' => '2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-12-31', 'is_active' => true],
            ['org' => 'hima-informatika', 'name' => '2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-12-31', 'is_active' => true],
            ['org' => 'ukm-kreatif', 'name' => '2025/2026', 'starts_at' => '2025-08-01', 'ends_at' => '2026-07-31', 'is_active' => true],
        ] as $period) {
            DB::table('organization_periods')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId($period['org']),
                    'name' => $period['name'],
                ],
                [
                    'starts_at' => $period['starts_at'],
                    'ends_at' => $period['ends_at'],
                    'is_active' => $period['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOrganizationMembers($now): void
    {
        foreach ([
            ['org' => 'bem-fakultas-teknologi', 'email' => 'owner@prokerin.test', 'role' => 'organization_owner'],
            ['org' => 'bem-fakultas-teknologi', 'email' => 'admin@prokerin.test', 'role' => 'organization_admin'],
            ['org' => 'bem-fakultas-teknologi', 'email' => 'sekretaris@prokerin.test', 'role' => 'secretary'],
            ['org' => 'bem-fakultas-teknologi', 'email' => 'bendahara@prokerin.test', 'role' => 'treasurer'],
            ['org' => 'bem-fakultas-teknologi', 'email' => 'member@prokerin.test', 'role' => 'member'],
            ['org' => 'bem-fakultas-teknologi', 'email' => 'viewer@prokerin.test', 'role' => 'viewer'],
            ['org' => 'hima-informatika', 'email' => 'admin@prokerin.test', 'role' => 'organization_admin'],
            ['org' => 'ukm-kreatif', 'email' => 'viewer@prokerin.test', 'role' => 'viewer'],
        ] as $member) {
            DB::table('organization_members')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId($member['org']),
                    'user_id' => $this->userId($member['email']),
                ],
                [
                    'role' => $member['role'],
                    'joined_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedOrganizationInvitations($now): void
    {
        foreach ([
            ['email' => 'calon.sekretaris@kampus.test', 'role' => 'secretary', 'status' => InvitationStatus::Pending->value],
            ['email' => 'calon.bendahara@kampus.test', 'role' => 'treasurer', 'status' => InvitationStatus::Accepted->value],
            ['email' => 'calon.lead@kampus.test', 'role' => 'project_lead', 'status' => InvitationStatus::Expired->value],
        ] as $invite) {
            DB::table('organization_invitations')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'email' => $invite['email'],
                ],
                [
                    'role' => $invite['role'],
                    'status' => $invite['status'],
                    'token' => hash('sha256', $invite['email']),
                    'expires_at' => $now->copy()->addDays(7),
                    'invited_by_user_id' => $this->userId('admin@prokerin.test'),
                    'accepted_by_user_id' => $invite['status'] === InvitationStatus::Accepted->value
                        ? $this->userId('member@prokerin.test')
                        : null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedRolePermissionMatrix($now): void
    {
        foreach ((new GetRolePermissionMatrixAction)->execute() as $rolePermission) {
            DB::table('role_permission_matrix')->updateOrInsert(
                [
                    'role' => $rolePermission->role,
                    'scope' => $rolePermission->scope,
                ],
                [
                    'label' => $rolePermission->label,
                    'permissions' => json_encode($rolePermission->toArray()['permissions']),
                    'is_system_role' => $rolePermission->isSystemRole,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedProjectTemplates($now): void
    {
        $builder = new BuildProjectTemplatePlanAction;

        foreach (ProjectTemplateType::cases() as $templateType) {
            $plan = $builder->execute($templateType)->toArray();

            DB::table('project_templates')->updateOrInsert(
                ['type' => $templateType->value],
                [
                    'label' => $templateType->label(),
                    'proposal_outline' => $plan['proposalOutline'],
                    'tasks' => json_encode($plan['tasks']),
                    'budget_lines' => json_encode($plan['budgetLines']),
                    'lpj_checklist' => json_encode($plan['lpjChecklist']),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedProjects($now): void
    {
        foreach ([
            [
                'org' => 'bem-fakultas-teknologi',
                'period' => '2026',
                'template' => ProjectTemplateType::Seminar->value,
                'lead' => 'lead@prokerin.test',
                'name' => 'Seminar Karier Digital',
                'slug' => 'seminar-karier-digital',
                'description' => 'Seminar karier bersama praktisi industri digital.',
                'status' => ProjectStatus::ProposalReview->value,
                'progress' => 72,
                'starts_at' => '2026-06-12',
                'ends_at' => '2026-06-12',
            ],
            [
                'org' => 'hima-informatika',
                'period' => '2026',
                'template' => ProjectTemplateType::Workshop->value,
                'lead' => 'admin@prokerin.test',
                'name' => 'Workshop UI/UX HMIF',
                'slug' => 'workshop-ui-ux-hmif',
                'description' => 'Workshop praktik desain produk digital.',
                'status' => ProjectStatus::RabApproval->value,
                'progress' => 54,
                'starts_at' => '2026-07-20',
                'ends_at' => '2026-07-21',
            ],
            [
                'org' => 'ukm-kreatif',
                'period' => '2025/2026',
                'template' => ProjectTemplateType::Makrab->value,
                'lead' => 'owner@prokerin.test',
                'name' => 'Makrab Angkatan 2026',
                'slug' => 'makrab-angkatan-2026',
                'description' => 'Agenda internal untuk penguatan kepengurusan.',
                'status' => ProjectStatus::Draft->value,
                'progress' => 38,
                'starts_at' => '2026-08-10',
                'ends_at' => '2026-08-12',
            ],
        ] as $project) {
            DB::table('projects')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId($project['org']),
                    'slug' => $project['slug'],
                ],
                [
                    'organization_period_id' => $this->periodId($project['org'], $project['period']),
                    'project_template_id' => $this->templateId($project['template']),
                    'project_lead_id' => $this->userId($project['lead']),
                    'name' => $project['name'],
                    'description' => $project['description'],
                    'status' => $project['status'],
                    'progress' => $project['progress'],
                    'starts_at' => $project['starts_at'],
                    'ends_at' => $project['ends_at'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedProjectMembers($now): void
    {
        foreach ([
            ['project' => 'seminar-karier-digital', 'email' => 'lead@prokerin.test', 'role' => ProjectRole::ProjectLead->value],
            ['project' => 'seminar-karier-digital', 'email' => 'koordinator@prokerin.test', 'role' => ProjectRole::DivisionCoordinator->value],
            ['project' => 'seminar-karier-digital', 'email' => 'member@prokerin.test', 'role' => ProjectRole::CommitteeMember->value],
            ['project' => 'seminar-karier-digital', 'email' => 'viewer@prokerin.test', 'role' => ProjectRole::Viewer->value],
        ] as $member) {
            DB::table('project_members')->updateOrInsert(
                [
                    'project_id' => $this->projectId($member['project']),
                    'user_id' => $this->userId($member['email']),
                ],
                [
                    'role' => $member['role'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedProjectTasks($now): void
    {
        foreach ([
            ['project' => 'seminar-karier-digital', 'title' => 'Finalisasi proposal', 'division' => 'Sekretaris', 'pic' => 'sekretaris@prokerin.test', 'status' => TaskStatus::Review->value, 'due_at' => '2026-05-22'],
            ['project' => 'seminar-karier-digital', 'title' => 'Submit RAB konsumsi', 'division' => 'Bendahara', 'pic' => 'bendahara@prokerin.test', 'status' => TaskStatus::InProgress->value, 'due_at' => '2026-05-24'],
            ['project' => 'seminar-karier-digital', 'title' => 'Konfirmasi narasumber', 'division' => 'Acara', 'pic' => 'lead@prokerin.test', 'status' => TaskStatus::Backlog->value, 'due_at' => '2026-05-25'],
            ['project' => 'seminar-karier-digital', 'title' => 'Upload dokumentasi kegiatan', 'division' => 'Dokumentasi', 'pic' => 'member@prokerin.test', 'status' => TaskStatus::Done->value, 'due_at' => '2026-06-13'],
        ] as $task) {
            DB::table('project_tasks')->updateOrInsert(
                [
                    'project_id' => $this->projectId($task['project']),
                    'title' => $task['title'],
                ],
                [
                    'division' => $task['division'],
                    'pic_user_id' => $this->userId($task['pic']),
                    'status' => $task['status'],
                    'due_at' => $task['due_at'],
                    'completed_at' => $task['status'] === TaskStatus::Done->value ? $now : null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedDocuments($now): void
    {
        foreach ([
            ['name' => 'proposal-v2.pdf', 'folder' => 'Proposal', 'owner' => 'sekretaris@prokerin.test', 'visibility' => DocumentVisibility::Private->value, 'status' => 'ready', 'mime' => 'application/pdf', 'size' => 2048],
            ['name' => 'receipt-consumption.jpg', 'folder' => 'Finance Receipts', 'owner' => 'bendahara@prokerin.test', 'visibility' => DocumentVisibility::Restricted->value, 'status' => 'review', 'mime' => 'image/jpeg', 'size' => 512],
            ['name' => 'documentation-day-1.zip', 'folder' => 'Documentation', 'owner' => 'member@prokerin.test', 'visibility' => DocumentVisibility::Committee->value, 'status' => 'uploaded', 'mime' => 'application/zip', 'size' => 4096],
        ] as $document) {
            DB::table('documents')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'name' => $document['name'],
                ],
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'owner_user_id' => $this->userId($document['owner']),
                    'folder' => $document['folder'],
                    'storage_path' => 'documents/seminar-karier-digital/'.$document['name'],
                    'mime_type' => $document['mime'],
                    'size_kb' => $document['size'],
                    'visibility' => $document['visibility'],
                    'status' => $document['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedSponsorsVendors($now): void
    {
        foreach ([
            [
                'type' => 'sponsor',
                'name' => 'Bank Jatim Youth Program',
                'category' => 'Financial sponsor',
                'contact_person' => 'Rina Maharani',
                'phone' => '+6282111002200',
                'email' => 'partnership@bankjatim.example',
                'address' => 'Jl. Basuki Rahmat, Surabaya',
                'status' => 'active',
                'notes' => 'Tertarik pada program karier dan literasi keuangan mahasiswa.',
                'role' => 'Gold sponsor seminar',
                'amount' => 7500000,
                'document' => 'proposal-v2.pdf',
            ],
            [
                'type' => 'vendor',
                'name' => 'CV Audio Visual Nusantara',
                'category' => 'Sound system',
                'contact_person' => 'Agus Santoso',
                'phone' => '+6281233004400',
                'email' => 'sales@avn.example',
                'address' => 'Ruko Manyar Indah, Surabaya',
                'status' => 'active',
                'notes' => 'Vendor langganan untuk aula sedang dan outdoor kecil.',
                'role' => 'Vendor sound system',
                'amount' => 8250000,
                'document' => 'receipt-consumption.jpg',
            ],
            [
                'type' => 'vendor',
                'name' => 'Studio Kreatif Campus',
                'category' => 'Design & printing',
                'contact_person' => 'Maya Putri',
                'phone' => '+6285551007700',
                'email' => 'hello@campuscreative.example',
                'address' => 'Jl. Kaliurang, Yogyakarta',
                'status' => 'inactive',
                'notes' => 'Perlu update price list sebelum dipakai lagi.',
                'role' => 'Vendor publikasi',
                'amount' => 1750000,
                'document' => 'documentation-day-1.zip',
            ],
        ] as $contact) {
            DB::table('sponsors_vendors')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'name' => $contact['name'],
                ],
                [
                    'type' => $contact['type'],
                    'category' => $contact['category'],
                    'contact_person' => $contact['contact_person'],
                    'phone' => $contact['phone'],
                    'email' => $contact['email'],
                    'address' => $contact['address'],
                    'status' => $contact['status'],
                    'notes' => $contact['notes'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $contactId = (int) DB::table('sponsors_vendors')
                ->where('organization_id', $this->organizationId('bem-fakultas-teknologi'))
                ->where('name', $contact['name'])
                ->value('id');

            DB::table('sponsor_vendor_project_links')->updateOrInsert(
                [
                    'sponsor_vendor_id' => $contactId,
                    'project_id' => $this->projectId('seminar-karier-digital'),
                ],
                [
                    'role_description' => $contact['role'],
                    'amount' => $contact['amount'],
                    'linked_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('sponsor_vendor_documents')->updateOrInsert(
                [
                    'sponsor_vendor_id' => $contactId,
                    'document_id' => $this->documentId((string) $contact['document']),
                ],
                [
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedBudgetLinesAndTransactions($now): void
    {
        foreach ([
            ['name' => 'Konsumsi peserta', 'category' => 'Konsumsi', 'planned' => 6500000, 'realized' => 2000000, 'status' => BudgetStatus::Realized->value],
            ['name' => 'Sewa aula dan sound system', 'category' => 'Venue', 'planned' => 8250000, 'realized' => 0, 'status' => BudgetStatus::Review->value],
            ['name' => 'Publikasi dan printing', 'category' => 'Marketing', 'planned' => 1750000, 'realized' => 650000, 'status' => BudgetStatus::Approved->value],
        ] as $line) {
            DB::table('budget_lines')->updateOrInsert(
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'name' => $line['name'],
                ],
                [
                    'category' => $line['category'],
                    'planned_amount' => $line['planned'],
                    'realized_amount' => $line['realized'],
                    'status' => $line['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        DB::table('budget_transactions')->updateOrInsert(
            [
                'budget_line_id' => $this->budgetLineId('Konsumsi peserta'),
                'name' => 'DP konsumsi',
            ],
            [
                'receipt_document_id' => $this->documentId('receipt-consumption.jpg'),
                'amount' => 2000000,
                'status' => 'verified',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    private function seedProposalDrafts($now): void
    {
        $project = DB::table('projects')->where('slug', 'seminar-karier-digital')->first();
        $templatePlan = (new BuildProjectTemplatePlanAction)->execute(ProjectTemplateType::Seminar);
        $draft = (new BuildProposalDraftAction)->execute(
            new ProposalProjectData(
                name: $project->name,
                organizationName: 'BEM Fakultas Teknologi',
                description: $project->description,
                targetAudience: 'Mahasiswa aktif tingkat akhir dan pengurus organisasi kampus.',
                startsAt: new DateTimeImmutable((string) $project->starts_at),
                endsAt: new DateTimeImmutable((string) $project->ends_at),
                projectLeadName: 'Fajar Nugroho',
            ),
            $templatePlan,
        )->toArray();

        DB::table('proposal_drafts')->updateOrInsert(
            [
                'project_id' => $project->id,
                'title' => $draft['title'],
            ],
            [
                'subtitle' => $draft['subtitle'],
                'sections' => json_encode($draft['sections']),
                'status' => 'draft',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    private function seedLpjChecklist($now): void
    {
        foreach ([
            ['title' => 'Data realisasi anggaran lengkap', 'complete' => true],
            ['title' => 'Dokumentasi kegiatan terunggah', 'complete' => true],
            ['title' => 'Daftar hadir panitia dan peserta', 'complete' => false],
            ['title' => 'Evaluasi kegiatan dari ketua pelaksana', 'complete' => false],
            ['title' => 'Approval bendahara dan sekretaris', 'complete' => false],
        ] as $item) {
            DB::table('lpj_checklist_items')->updateOrInsert(
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'title' => $item['title'],
                ],
                [
                    'is_required' => true,
                    'is_complete' => $item['complete'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedMeetings($now): void
    {
        foreach ([
            [
                'title' => 'Technical Meeting Seminar Karier',
                'agenda' => 'Final check rundown, PIC venue, alur registrasi, dan kebutuhan narasumber.',
                'location' => 'Ruang BEM FT',
                'starts_at' => '2026-05-26 15:30:00',
                'ends_at' => '2026-05-26 17:00:00',
                'status' => 'planned',
                'creator' => 'sekretaris@prokerin.test',
            ],
            [
                'title' => 'Evaluasi Proposal dan RAB',
                'agenda' => 'Review revisi proposal, catatan RAB konsumsi, dan timeline approval.',
                'location' => 'Google Meet',
                'starts_at' => '2026-05-14 19:00:00',
                'ends_at' => '2026-05-14 20:15:00',
                'status' => 'completed',
                'creator' => 'admin@prokerin.test',
            ],
        ] as $meeting) {
            DB::table('meetings')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'title' => $meeting['title'],
                ],
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'created_by_user_id' => $this->userId($meeting['creator']),
                    'agenda' => $meeting['agenda'],
                    'location' => $meeting['location'],
                    'starts_at' => $meeting['starts_at'],
                    'ends_at' => $meeting['ends_at'],
                    'status' => $meeting['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        foreach ([
            ['meeting' => 'Technical Meeting Seminar Karier', 'email' => 'sekretaris@prokerin.test', 'role' => 'Notulis', 'status' => 'invited'],
            ['meeting' => 'Technical Meeting Seminar Karier', 'email' => 'lead@prokerin.test', 'role' => 'Ketua Pelaksana', 'status' => 'invited'],
            ['meeting' => 'Technical Meeting Seminar Karier', 'email' => 'bendahara@prokerin.test', 'role' => 'Bendahara', 'status' => 'invited'],
            ['meeting' => 'Evaluasi Proposal dan RAB', 'email' => 'admin@prokerin.test', 'role' => 'Reviewer', 'status' => 'present'],
            ['meeting' => 'Evaluasi Proposal dan RAB', 'email' => 'sekretaris@prokerin.test', 'role' => 'Notulis', 'status' => 'present'],
            ['meeting' => 'Evaluasi Proposal dan RAB', 'email' => 'bendahara@prokerin.test', 'role' => 'Bendahara', 'status' => 'present'],
        ] as $attendee) {
            $user = DB::table('users')->where('email', $attendee['email'])->first();

            DB::table('meeting_attendees')->updateOrInsert(
                [
                    'meeting_id' => $this->meetingId($attendee['meeting']),
                    'user_id' => $user->id,
                ],
                [
                    'name' => $user->name,
                    'role' => $attendee['role'],
                    'attendance_status' => $attendee['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        DB::table('meeting_minutes')->updateOrInsert(
            ['meeting_id' => $this->meetingId('Evaluasi Proposal dan RAB')],
            [
                'created_by_user_id' => $this->userId('sekretaris@prokerin.test'),
                'summary' => 'Proposal sudah siap diajukan setelah revisi narasi target peserta dan penyesuaian konsumsi.',
                'decisions' => json_encode([
                    'RAB konsumsi dikunci di angka Rp6.500.000 sebelum submit approval.',
                    'Proposal final dikirim ke pembina maksimal 16 Mei 2026.',
                ]),
                'action_items' => json_encode([
                    ['task' => 'Upload proposal revisi final', 'owner' => 'Salsa Kirana', 'due' => '2026-05-16', 'status' => 'open'],
                    ['task' => 'Lengkapi bukti vendor konsumsi', 'owner' => 'Raka Pratama', 'due' => '2026-05-17', 'status' => 'open'],
                ]),
                'published_at' => '2026-05-14 21:00:00',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    private function seedAttendance($now): void
    {
        foreach ([
            [
                'title' => 'Absensi Technical Meeting Seminar Karier',
                'meeting' => 'Technical Meeting Seminar Karier',
                'starts_at' => '2026-05-26 15:15:00',
                'ends_at' => '2026-05-26 17:15:00',
                'status' => 'open',
                'creator' => 'sekretaris@prokerin.test',
            ],
            [
                'title' => 'Absensi Evaluasi Proposal dan RAB',
                'meeting' => 'Evaluasi Proposal dan RAB',
                'starts_at' => '2026-05-14 18:45:00',
                'ends_at' => '2026-05-14 20:30:00',
                'status' => 'closed',
                'creator' => 'admin@prokerin.test',
            ],
        ] as $session) {
            DB::table('attendance_sessions')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'title' => $session['title'],
                ],
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'meeting_id' => $this->meetingId($session['meeting']),
                    'created_by_user_id' => $this->userId($session['creator']),
                    'starts_at' => $session['starts_at'],
                    'ends_at' => $session['ends_at'],
                    'status' => $session['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        foreach ([
            [
                'session' => 'Absensi Technical Meeting Seminar Karier',
                'token' => 'prokerin-m15-technical-meeting-token',
                'expires_at' => $now->copy()->addDays(7),
            ],
            [
                'session' => 'Absensi Evaluasi Proposal dan RAB',
                'token' => 'prokerin-m15-expired-evaluation-token',
                'expires_at' => $now->copy()->subDay(),
            ],
        ] as $token) {
            DB::table('attendance_qr_tokens')->updateOrInsert(
                ['token_hash' => hash('sha256', $token['token'])],
                [
                    'attendance_session_id' => $this->attendanceSessionId($token['session']),
                    'expires_at' => $token['expires_at'],
                    'revoked_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        $manualAttendeeId = $this->meetingAttendeeId('Technical Meeting Seminar Karier', 'sekretaris@prokerin.test');

        DB::table('attendance_records')->updateOrInsert(
            [
                'attendance_session_id' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
                'meeting_attendee_id' => $manualAttendeeId,
            ],
            [
                'user_id' => $this->userId('sekretaris@prokerin.test'),
                'attendee_name' => 'Salsa Kirana',
                'attendee_email' => 'sekretaris@prokerin.test',
                'check_in_method' => 'manual',
                'checked_in_at' => '2026-05-26 15:20:00',
                'status' => 'present',
                'notes' => 'Seed manual fallback untuk demo M15.',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        DB::table('meeting_attendees')
            ->where('id', $manualAttendeeId)
            ->update([
                'attendance_status' => 'present',
                'updated_at' => $now,
            ]);
    }

    private function seedCertificates($now): void
    {
        DB::table('certificate_templates')->updateOrInsert(
            [
                'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                'name' => 'Sertifikat Partisipasi Proker',
            ],
            [
                'description' => 'Template sertifikat untuk peserta kegiatan dan kepanitiaan proker.',
                'template_html' => '<h1>Sertifikat Penghargaan</h1><p class="meta">Nomor: {{certificate_number}}</p><p>Diberikan kepada</p><p class="recipient">{{recipient_name}}</p><p>atas partisipasi dalam {{project_name}} yang diselenggarakan oleh {{organization_name}}.</p><div class="signature"><p>{{signature_label}}</p><strong>{{signature_name}}</strong></div><p class="meta">Verifikasi: {{verification_url}}</p>',
                'signature_label' => 'Ketua BEM FT',
                'signature_name' => 'Dimas Aji',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        foreach ([
            [
                'number' => 'PRK-2026-BEMFAKULTAST-0001',
                'email' => 'sekretaris@prokerin.test',
                'name' => 'Salsa Kirana',
                'token' => '11111111-1111-4111-8111-111111111111',
                'pdf_path' => 'certificates/bem-fakultas-teknologi/prk-2026-bemfakultast-0001.pdf',
            ],
            [
                'number' => 'PRK-2026-BEMFAKULTAST-0002',
                'email' => 'bendahara@prokerin.test',
                'name' => 'Raka Pratama',
                'token' => '22222222-2222-4222-8222-222222222222',
                'pdf_path' => null,
            ],
        ] as $certificate) {
            DB::table('certificate_recipients')->updateOrInsert(
                ['certificate_number' => $certificate['number']],
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'template_id' => $this->certificateTemplateId('Sertifikat Partisipasi Proker'),
                    'user_id' => $this->userId($certificate['email']),
                    'recipient_name' => $certificate['name'],
                    'recipient_email' => $certificate['email'],
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'meeting_id' => $this->meetingId('Technical Meeting Seminar Karier'),
                    'issued_at' => '2026-05-16 09:00:00',
                    'issued_by' => $this->userId('owner@prokerin.test'),
                    'verification_token' => $certificate['token'],
                    'pdf_path' => $certificate['pdf_path'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedEventRegistrations($now): void
    {
        DB::table('event_registration_settings')->updateOrInsert(
            ['project_id' => $this->projectId('seminar-karier-digital')],
            [
                'is_open' => true,
                'capacity' => 120,
                'opens_at' => '2026-05-01 08:00:00',
                'closes_at' => '2026-06-10 23:59:00',
                'require_payment' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        foreach ([
            [
                'name' => 'Alya Rahma',
                'email' => 'alya.rahma@student.example',
                'phone' => '+6282112340001',
                'institution' => 'Universitas Negeri Surabaya',
                'status' => 'confirmed',
            ],
            [
                'name' => 'Bima Prakoso',
                'email' => 'bima.prakoso@student.example',
                'phone' => '+6282112340002',
                'institution' => 'Institut Teknologi Sepuluh Nopember',
                'status' => 'confirmed',
            ],
        ] as $registration) {
            DB::table('event_registrations')->updateOrInsert(
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'participant_email' => $registration['email'],
                ],
                [
                    'participant_name' => $registration['name'],
                    'phone' => $registration['phone'],
                    'institution' => $registration['institution'],
                    'status' => $registration['status'],
                    'registered_at' => $now->copy()->subDays(2),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedTicketTiers($now): void
    {
        foreach ([
            ['name' => 'Free Pass', 'price' => 0, 'capacity' => 60],
            ['name' => 'Early Bird', 'price' => 25000, 'capacity' => 40],
            ['name' => 'Regular', 'price' => 50000, 'capacity' => 20],
        ] as $tier) {
            DB::table('ticket_tiers')->updateOrInsert(
                [
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'name' => $tier['name'],
                ],
                [
                    'price' => $tier['price'],
                    'capacity' => $tier['capacity'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedNotificationRules($now): void
    {
        foreach ((new GetDefaultNotificationRulesAction)->execute() as $rule) {
            $payload = $rule->toArray();

            DB::table('notification_rules')->updateOrInsert(
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'event' => $payload['event'],
                ],
                [
                    'label' => $payload['label'],
                    'audience' => $payload['audience'],
                    'channels' => json_encode($payload['channels']),
                    'trigger' => $payload['trigger'],
                    'status' => $payload['status'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function seedDocumentExports($now): void
    {
        $planner = new PlanDocumentExportAction;

        foreach ([
            new ExportRequestData('proposal-seminar-karier', 'Proposal Seminar Karier', ExportDocumentType::Proposal, ExportFormat::Pdf, 'Secretary'),
            new ExportRequestData('lpj-workshop-ui-ux', 'LPJ Workshop UI/UX', ExportDocumentType::Lpj, ExportFormat::Docx, 'Treasurer'),
        ] as $request) {
            $plan = $planner->execute($request);

            DB::table('document_exports')->updateOrInsert(
                ['output_path' => $plan->outputPath],
                [
                    'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
                    'project_id' => $this->projectId('seminar-karier-digital'),
                    'requested_by_user_id' => $this->userId($request->requestedBy === 'Secretary' ? 'sekretaris@prokerin.test' : 'bendahara@prokerin.test'),
                    'document_title' => $request->documentTitle,
                    'document_type' => $request->documentType->value,
                    'format' => $request->format->value,
                    'queue_name' => $plan->queueName,
                    'engine' => $plan->engine,
                    'storage_disk' => $plan->storageDisk,
                    'status' => 'queued',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function userId(string $email): int
    {
        return (int) DB::table('users')->where('email', $email)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function periodId(string $organizationSlug, string $name): int
    {
        return (int) DB::table('organization_periods')
            ->where('organization_id', $this->organizationId($organizationSlug))
            ->where('name', $name)
            ->value('id');
    }

    private function templateId(string $type): int
    {
        return (int) DB::table('project_templates')->where('type', $type)->value('id');
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function budgetLineId(string $name): int
    {
        return (int) DB::table('budget_lines')->where('name', $name)->value('id');
    }

    private function documentId(string $name): int
    {
        return (int) DB::table('documents')->where('name', $name)->value('id');
    }

    private function meetingId(string $title): int
    {
        return (int) DB::table('meetings')->where('title', $title)->value('id');
    }

    private function attendanceSessionId(string $title): int
    {
        return (int) DB::table('attendance_sessions')->where('title', $title)->value('id');
    }

    private function certificateTemplateId(string $name): int
    {
        return (int) DB::table('certificate_templates')->where('name', $name)->value('id');
    }

    private function meetingAttendeeId(string $meetingTitle, string $email): int
    {
        return (int) DB::table('meeting_attendees')
            ->where('meeting_id', $this->meetingId($meetingTitle))
            ->where('user_id', $this->userId($email))
            ->value('id');
    }
}
