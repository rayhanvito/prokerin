<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\DashboardVariant;
use App\Models\User;
use App\Support\OrganizationModeGate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class SidebarMenuAction
{
    public function __construct(private DashboardRoleResolverAction $roleResolver) {}

    /**
     * @return array<int, array{groupLabel: string, items: array<int, array{label: string, href: string, icon: string, badgeCount: int|null}>}>
     */
    public function execute(User $user, int $organizationId): array
    {
        $variant = $this->roleResolver->execute($user, $organizationId);
        $badges = Cache::remember("sidebar_badges:{$organizationId}:{$user->id}", 60, fn (): array => [
            'approval_count' => $this->approvalCount($organizationId, $variant),
            'pending_tasks' => $this->pendingTasks((int) $user->id, $organizationId),
            'unread_notifications' => $this->unreadNotifications((int) $user->id),
        ]);

        return array_map(
            fn (array $group): array => [
                'groupLabel' => $group['groupLabel'],
                'items' => array_map(
                    fn (array $item): array => [
                        'label' => $item['label'],
                        'href' => $item['href'],
                        'icon' => $item['icon'],
                        'badgeCount' => isset($item['badge']) ? (int) ($badges[$item['badge']] ?? 0) : null,
                    ],
                    $group['items'],
                ),
            ],
            $this->menuForMode($variant, OrganizationModeGate::forOrganization($organizationId)),
        );
    }

    private function approvalCount(int $organizationId, DashboardVariant $variant): int
    {
        if (! in_array($variant, [DashboardVariant::Pimpinan, DashboardVariant::Bendahara], true)) {
            return 0;
        }

        return DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->where('approval_workflow_definitions.organization_id', $organizationId)
            ->where('approval_instances.status', 'pending')
            ->count()
            + DB::table('budget_lines')
                ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                ->where('projects.organization_id', $organizationId)
                ->where('budget_lines.status', 'review')
                ->count();
    }

    private function pendingTasks(int $userId, int $organizationId): int
    {
        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_tasks.pic_user_id', $userId)
            ->where('project_tasks.status', '!=', 'done')
            ->whereDate('project_tasks.due_at', '<=', now())
            ->count();
    }

    private function unreadNotifications(int $userId): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @return array<int, array{groupLabel: string, items: array<int, array{label: string, href: string, icon: string, badge?: string}>}>
     */
    private function menu(DashboardVariant $variant): array
    {
        return match ($variant) {
            DashboardVariant::Pimpinan => $this->pimpinanMenu(),
            DashboardVariant::Sekretaris => $this->sekretarisMenu(),
            DashboardVariant::Bendahara => $this->bendaharaMenu(),
            DashboardVariant::Operasional => $this->operasionalMenu(),
            DashboardVariant::Member => $this->memberMenu(),
            DashboardVariant::Viewer => $this->viewerMenu(),
        };
    }

    /**
     * @return array<int, array{groupLabel: string, items: array<int, array{label: string, href: string, icon: string, badge?: string}>}>
     */
    private function menuForMode(DashboardVariant $variant, OrganizationModeGate $modeGate): array
    {
        if (! $modeGate->isKepanitiaan()) {
            return $this->menu($variant);
        }

        $hiddenLabels = [
            'Periode' => ! $modeGate->canUsePeriods(),
            'Handover' => ! $modeGate->canUseHandover(),
            'Anggota & Role' => ! $modeGate->canUseRoleMatrix(),
        ];

        return array_values(array_filter(array_map(
            static function (array $group) use ($hiddenLabels): array {
                $group['items'] = array_values(array_filter(
                    $group['items'],
                    static fn (array $item): bool => ! ($hiddenLabels[$item['label']] ?? false),
                ));

                return $group;
            },
            $this->menu($variant),
        ), static fn (array $group): bool => $group['items'] !== []));
    }

    private function pimpinanMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
                $this->item('Notifikasi', route('notifications.index', absolute: false), 'Bell', 'unread_notifications'),
            ]),
            $this->group('Organisasi', [
                $this->item('Anggota & Role', route('members.index', absolute: false), 'Users'),
                $this->item('Periode', route('organization.periods', absolute: false), 'Calendar'),
                $this->item('Pengaturan', route('organization.setup', absolute: false), 'Settings'),
            ]),
            $this->group('Proker', [
                $this->item('Semua Proker', route('proker.index', absolute: false), 'FolderKanban'),
                $this->item('Template Proker', route('proker.templates', absolute: false), 'LayoutTemplate'),
                $this->item('Timeline & Task', route('tasks.kanban', absolute: false), 'GitBranch'),
            ]),
            $this->group('Administrasi', [
                $this->item('Proposal', route('reports.proposal-editor', absolute: false), 'FileText', 'approval_count'),
                $this->item('LPJ', route('reports.lpj-checklist', absolute: false), 'ClipboardCheck', 'approval_count'),
                $this->item('Dokumen', route('documents.index', absolute: false), 'Folder'),
                $this->item('Surat Menyurat', route('letters.index', absolute: false), 'Mail'),
                $this->item('Rapat & Notulen', route('meetings.index', absolute: false), 'CalendarDays'),
                $this->item('Registrasi Event', route('events.registrations.index', absolute: false), 'ClipboardCheck'),
                $this->item('Absensi QR', route('attendance.index', absolute: false), 'ScanLine'),
                $this->item('Sertifikat', route('certificates.index', absolute: false), 'Award'),
            ]),
            $this->group('Keuangan', [
                $this->item('RAB & Keuangan', route('finance.index', absolute: false), 'Wallet', 'approval_count'),
                $this->item('Sponsor & Vendor', route('organization.sponsors-vendors', absolute: false), 'Handshake'),
            ]),
            $this->group('Serah Terima', [
                $this->item('Handover', route('organization.handover', absolute: false), 'ArrowRightLeft'),
            ]),
        ];
    }

    private function sekretarisMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
                $this->item('Notifikasi', route('notifications.index', absolute: false), 'Bell', 'unread_notifications'),
            ]),
            $this->group('Administrasi', [
                $this->item('Proposal', route('reports.proposal-editor', absolute: false), 'FileText'),
                $this->item('LPJ', route('reports.lpj-checklist', absolute: false), 'ClipboardCheck'),
                $this->item('Dokumen', route('documents.index', absolute: false), 'Folder'),
                $this->item('Surat Menyurat', route('letters.index', absolute: false), 'Mail'),
                $this->item('Rapat & Notulen', route('meetings.index', absolute: false), 'CalendarDays'),
                $this->item('Registrasi Event', route('events.registrations.index', absolute: false), 'ClipboardCheck'),
                $this->item('Absensi QR', route('attendance.index', absolute: false), 'ScanLine'),
            ]),
            $this->group('Proker', [
                $this->item('Semua Proker', route('proker.index', absolute: false), 'FolderKanban'),
                $this->item('Timeline & Task', route('tasks.kanban', absolute: false), 'GitBranch'),
            ]),
            $this->group('Organisasi', [
                $this->item('Anggota', route('members.index', absolute: false), 'Users'),
            ]),
        ];
    }

    private function bendaharaMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
                $this->item('Notifikasi', route('notifications.index', absolute: false), 'Bell', 'unread_notifications'),
            ]),
            $this->group('Keuangan', [
                $this->item('RAB & Keuangan', route('finance.index', absolute: false), 'Wallet', 'approval_count'),
                $this->item('Sponsor & Vendor', route('organization.sponsors-vendors', absolute: false), 'Handshake'),
            ]),
            $this->group('Proker', [
                $this->item('Semua Proker', route('proker.index', absolute: false), 'FolderKanban'),
            ]),
            $this->group('Organisasi', [
                $this->item('Anggota', route('members.index', absolute: false), 'Users'),
            ]),
        ];
    }

    private function operasionalMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
                $this->item('Notifikasi', route('notifications.index', absolute: false), 'Bell', 'unread_notifications'),
            ]),
            $this->group('Proker Saya', [
                $this->item('Proker', route('proker.index', absolute: false), 'FolderKanban'),
                $this->item('Timeline & Task', route('tasks.kanban', absolute: false), 'GitBranch', 'pending_tasks'),
                $this->item('Proposal', route('reports.proposal-editor', absolute: false), 'FileText'),
                $this->item('RAB', route('finance.index', absolute: false), 'Wallet'),
                $this->item('LPJ', route('reports.lpj-checklist', absolute: false), 'ClipboardCheck'),
            ]),
            $this->group('Tim', [
                $this->item('Absensi QR', route('attendance.index', absolute: false), 'ScanLine'),
                $this->item('Registrasi Event', route('events.registrations.index', absolute: false), 'ClipboardCheck'),
                $this->item('Dokumen', route('documents.index', absolute: false), 'Folder'),
            ]),
        ];
    }

    private function memberMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
                $this->item('Notifikasi', route('notifications.index', absolute: false), 'Bell', 'unread_notifications'),
            ]),
            $this->group('Aktivitas Saya', [
                $this->item('Task Saya', route('tasks.kanban', absolute: false), 'CheckSquare', 'pending_tasks'),
                $this->item('Proker', route('proker.index', absolute: false), 'FolderKanban'),
                $this->item('Absensi QR', route('attendance.index', absolute: false), 'ScanLine'),
                $this->item('Dokumen', route('documents.index', absolute: false), 'Folder'),
            ]),
        ];
    }

    private function viewerMenu(): array
    {
        return [
            $this->group('Utama', [
                $this->item('Dashboard', route('dashboard', absolute: false), 'LayoutDashboard'),
            ]),
            $this->group('Lihat', [
                $this->item('Proker', route('proker.index', absolute: false), 'FolderKanban'),
                $this->item('Dokumen Publik', route('documents.index', absolute: false), 'Folder'),
            ]),
        ];
    }

    private function group(string $label, array $items): array
    {
        return ['groupLabel' => $label, 'items' => $items];
    }

    private function item(string $label, string $href, string $icon, ?string $badge = null): array
    {
        return array_filter([
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'badge' => $badge,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
