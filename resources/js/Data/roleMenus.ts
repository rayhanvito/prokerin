export type DashboardRoleVariant =
    | 'pimpinan'
    | 'sekretaris'
    | 'bendahara'
    | 'operasional'
    | 'member'
    | 'viewer';

export interface RoleMenuItem {
    label: string;
    routeName: string;
    icon: string;
    badgeKey?: 'approval_count' | 'pending_tasks' | 'unread_notifications';
}

export interface RoleMenuGroup {
    groupLabel: string;
    items: RoleMenuItem[];
}

export const roleMenus: Record<DashboardRoleVariant, RoleMenuGroup[]> = {
    pimpinan: [
        {
            groupLabel: 'Utama',
            items: [
                { label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' },
                {
                    label: 'Notifikasi',
                    routeName: 'notifications.index',
                    icon: 'Bell',
                    badgeKey: 'unread_notifications',
                },
            ],
        },
        {
            groupLabel: 'Organisasi',
            items: [
                { label: 'Anggota & Role', routeName: 'members.index', icon: 'Users' },
                { label: 'Periode', routeName: 'organization.periods', icon: 'Calendar' },
                { label: 'Pengaturan', routeName: 'organization.setup', icon: 'Settings' },
            ],
        },
        {
            groupLabel: 'Proker',
            items: [
                { label: 'Semua Proker', routeName: 'proker.index', icon: 'FolderKanban' },
                { label: 'Template Proker', routeName: 'proker.templates', icon: 'LayoutTemplate' },
                {
                    label: 'Timeline & Task',
                    routeName: 'tasks.kanban',
                    icon: 'GitBranch',
                },
            ],
        },
        {
            groupLabel: 'Administrasi',
            items: [
                {
                    label: 'Proposal',
                    routeName: 'reports.proposal-editor',
                    icon: 'FileText',
                    badgeKey: 'approval_count',
                },
                {
                    label: 'LPJ',
                    routeName: 'reports.lpj-checklist',
                    icon: 'ClipboardCheck',
                    badgeKey: 'approval_count',
                },
                { label: 'Dokumen', routeName: 'documents.index', icon: 'Folder' },
                { label: 'Rapat & Notulen', routeName: 'meetings.index', icon: 'CalendarDays' },
                { label: 'Absensi QR', routeName: 'attendance.index', icon: 'ScanLine' },
                { label: 'Sertifikat', routeName: 'certificates.index', icon: 'Award' },
            ],
        },
        {
            groupLabel: 'Keuangan',
            items: [
                {
                    label: 'RAB & Keuangan',
                    routeName: 'finance.index',
                    icon: 'Wallet',
                    badgeKey: 'approval_count',
                },
                {
                    label: 'Sponsor & Vendor',
                    routeName: 'organization.sponsors-vendors',
                    icon: 'Handshake',
                },
            ],
        },
        {
            groupLabel: 'Serah Terima',
            items: [
                {
                    label: 'Handover',
                    routeName: 'organization.handover',
                    icon: 'ArrowRightLeft',
                },
            ],
        },
    ],
    sekretaris: [
        {
            groupLabel: 'Utama',
            items: [
                { label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' },
                {
                    label: 'Notifikasi',
                    routeName: 'notifications.index',
                    icon: 'Bell',
                    badgeKey: 'unread_notifications',
                },
            ],
        },
        {
            groupLabel: 'Administrasi',
            items: [
                { label: 'Proposal', routeName: 'reports.proposal-editor', icon: 'FileText' },
                { label: 'LPJ', routeName: 'reports.lpj-checklist', icon: 'ClipboardCheck' },
                { label: 'Dokumen', routeName: 'documents.index', icon: 'Folder' },
                { label: 'Rapat & Notulen', routeName: 'meetings.index', icon: 'CalendarDays' },
                { label: 'Absensi QR', routeName: 'attendance.index', icon: 'ScanLine' },
            ],
        },
        {
            groupLabel: 'Proker',
            items: [
                { label: 'Semua Proker', routeName: 'proker.index', icon: 'FolderKanban' },
                { label: 'Timeline & Task', routeName: 'tasks.kanban', icon: 'GitBranch' },
            ],
        },
        {
            groupLabel: 'Organisasi',
            items: [{ label: 'Anggota', routeName: 'members.index', icon: 'Users' }],
        },
    ],
    bendahara: [
        {
            groupLabel: 'Utama',
            items: [
                { label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' },
                {
                    label: 'Notifikasi',
                    routeName: 'notifications.index',
                    icon: 'Bell',
                    badgeKey: 'unread_notifications',
                },
            ],
        },
        {
            groupLabel: 'Keuangan',
            items: [
                {
                    label: 'RAB & Keuangan',
                    routeName: 'finance.index',
                    icon: 'Wallet',
                    badgeKey: 'approval_count',
                },
                {
                    label: 'Sponsor & Vendor',
                    routeName: 'organization.sponsors-vendors',
                    icon: 'Handshake',
                },
            ],
        },
        {
            groupLabel: 'Proker',
            items: [{ label: 'Semua Proker', routeName: 'proker.index', icon: 'FolderKanban' }],
        },
        {
            groupLabel: 'Organisasi',
            items: [{ label: 'Anggota', routeName: 'members.index', icon: 'Users' }],
        },
    ],
    operasional: [
        {
            groupLabel: 'Utama',
            items: [
                { label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' },
                {
                    label: 'Notifikasi',
                    routeName: 'notifications.index',
                    icon: 'Bell',
                    badgeKey: 'unread_notifications',
                },
            ],
        },
        {
            groupLabel: 'Proker Saya',
            items: [
                { label: 'Proker', routeName: 'proker.index', icon: 'FolderKanban' },
                {
                    label: 'Timeline & Task',
                    routeName: 'tasks.kanban',
                    icon: 'GitBranch',
                    badgeKey: 'pending_tasks',
                },
                { label: 'Proposal', routeName: 'reports.proposal-editor', icon: 'FileText' },
                { label: 'RAB', routeName: 'finance.index', icon: 'Wallet' },
                { label: 'LPJ', routeName: 'reports.lpj-checklist', icon: 'ClipboardCheck' },
            ],
        },
        {
            groupLabel: 'Tim',
            items: [
                { label: 'Absensi QR', routeName: 'attendance.index', icon: 'ScanLine' },
                { label: 'Dokumen', routeName: 'documents.index', icon: 'Folder' },
            ],
        },
    ],
    member: [
        {
            groupLabel: 'Utama',
            items: [
                { label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' },
                {
                    label: 'Notifikasi',
                    routeName: 'notifications.index',
                    icon: 'Bell',
                    badgeKey: 'unread_notifications',
                },
            ],
        },
        {
            groupLabel: 'Aktivitas Saya',
            items: [
                {
                    label: 'Task Saya',
                    routeName: 'tasks.kanban',
                    icon: 'CheckSquare',
                    badgeKey: 'pending_tasks',
                },
                { label: 'Proker', routeName: 'proker.index', icon: 'FolderKanban' },
                { label: 'Absensi QR', routeName: 'attendance.index', icon: 'ScanLine' },
                { label: 'Dokumen', routeName: 'documents.index', icon: 'Folder' },
            ],
        },
    ],
    viewer: [
        {
            groupLabel: 'Utama',
            items: [{ label: 'Dashboard', routeName: 'dashboard', icon: 'LayoutDashboard' }],
        },
        {
            groupLabel: 'Lihat',
            items: [
                { label: 'Proker', routeName: 'proker.index', icon: 'FolderKanban' },
                { label: 'Dokumen Publik', routeName: 'documents.index', icon: 'Folder' },
            ],
        },
    ],
};
