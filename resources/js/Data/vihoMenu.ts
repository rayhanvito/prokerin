import {
    Archive,
    Building2,
    CalendarDays,
    CheckSquare,
    FileText,
    FolderOpen,
    Home,
    ListChecks,
    NotebookTabs,
    QrCode,
    Award,
    Shield,
    Bell,
    ReceiptText,
    Handshake,
    Settings,
    Users,
} from 'lucide-react';

export interface VihoMenuItem {
    title: string;
    href: string;
    icon: typeof Home;
    badge?: string;
    children?: Array<{
        title: string;
        href: string;
    }>;
}

export interface VihoMenuSection {
    title: string;
    items: VihoMenuItem[];
}

export const vihoMenu: VihoMenuSection[] = [
    {
        title: 'General',
        items: [
            {
                title: 'Dashboard',
                href: route('dashboard'),
                icon: Home,
            },
            {
                title: 'Proker',
                href: route('proker.index'),
                icon: ListChecks,
                badge: 'MVP',
                children: [
                    { title: 'Daftar Proker', href: route('proker.index') },
                    { title: 'Create Proker', href: route('proker.create') },
                    { title: 'Detail Proker', href: route('proker.show') },
                    { title: 'Template Proker', href: route('proker.templates') },
                    { title: 'Status Flow', href: route('proker.status-flow') },
                ],
            },
        ],
    },
    {
        title: 'Applications',
        items: [
            {
                title: 'Timeline & Task',
                href: route('tasks.index'),
                icon: CheckSquare,
                children: [
                    { title: 'Kanban', href: route('tasks.kanban') },
                    { title: 'Calendar', href: route('tasks.calendar') },
                    { title: 'PIC Assignment', href: route('tasks.assignments') },
                ],
            },
            {
                title: 'RAB & Finance',
                href: route('finance.index'),
                icon: ReceiptText,
                children: [
                    { title: 'Budget Draft', href: route('finance.budget-draft') },
                    { title: 'Realization', href: route('finance.realization') },
                    { title: 'Approval', href: route('finance.approval') },
                ],
            },
            {
                title: 'Proposal & LPJ',
                href: route('reports.index'),
                icon: FileText,
                children: [
                    { title: 'Proposal Editor', href: route('reports.proposal-editor') },
                    { title: 'LPJ Checklist', href: route('reports.lpj-checklist') },
                    { title: 'Export Queue', href: route('reports.export-queue') },
                ],
            },
            {
                title: 'Documents',
                href: route('documents.index'),
                icon: FolderOpen,
                children: [
                    { title: 'Folder Structure', href: route('documents.folders') },
                    { title: 'Upload Center', href: route('documents.upload-center') },
                ],
            },
            {
                title: 'Members',
                href: route('members.index'),
                icon: Users,
                children: [
                    { title: 'Invites', href: route('members.invites') },
                    { title: 'Role Matrix', href: route('members.roles') },
                ],
            },
            {
                title: 'Rapat & Notulen',
                href: route('meetings.index'),
                icon: NotebookTabs,
                badge: 'M14',
            },
            {
                title: 'Absensi QR',
                href: route('attendance.index'),
                icon: QrCode,
                badge: 'M15',
            },
            {
                title: 'Sertifikat Digital',
                href: route('certificates.index'),
                icon: Award,
                badge: 'M16',
                children: [
                    { title: 'Daftar Sertifikat', href: route('certificates.index') },
                    { title: 'Template', href: route('certificates.templates') },
                    { title: 'Issue Batch', href: route('certificates.issue') },
                ],
            },
        ],
    },
    {
        title: 'Organization',
        items: [
            {
                title: 'Organization Setup',
                href: route('organization.setup'),
                icon: Building2,
                children: [
                    { title: 'Switcher', href: route('organization.switcher') },
                    { title: 'Periods', href: route('organization.periods') },
                    { title: 'Calendar', href: route('organization.calendar') },
                ],
            },
            {
                title: 'Handover',
                href: route('organization.handover'),
                icon: Archive,
            },
            {
                title: 'Sponsor & Vendor',
                href: route('organization.sponsors-vendors'),
                icon: Handshake,
                badge: 'M20',
            },
            {
                title: 'Period Calendar',
                href: route('organization.calendar'),
                icon: CalendarDays,
            },
            {
                title: 'Notifications',
                href: route('notifications.index'),
                icon: Bell,
            },
            {
                title: 'Internal Admin',
                href: route('admin.index'),
                icon: Shield,
            },
            {
                title: 'Settings',
                href: route('profile.edit'),
                icon: Settings,
            },
        ],
    },
];
