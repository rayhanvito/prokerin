import type {
    BudgetItemSummary,
    DocumentSummary,
    NotificationRuleSummary,
    OrganizationSummary,
    ProjectSummary,
    TaskSummary,
} from '@/types/prokerin';

export const organizations: OrganizationSummary[] = [
    {
        id: 'org-bem-ft',
        name: 'BEM Fakultas Teknologi',
        period: '2026',
        role: 'organization_owner',
        isActive: true,
    },
    {
        id: 'org-hmif',
        name: 'HIMA Informatika',
        period: '2026',
        role: 'organization_admin',
        isActive: false,
    },
    {
        id: 'org-ukm-kreatif',
        name: 'UKM Kreatif',
        period: '2025/2026',
        role: 'viewer',
        isActive: false,
    },
];

export const projects: ProjectSummary[] = [
    {
        id: 'project-seminar-karier',
        name: 'Seminar Karier Digital',
        organization: 'BEM Fakultas',
        owner: 'Dimas Aji',
        status: 'proposal_review',
        progress: 72,
        deadline: '22 Mei',
    },
    {
        id: 'project-workshop-uiux',
        name: 'Workshop UI/UX HMIF',
        organization: 'HIMA Informatika',
        owner: 'Nadia Putri',
        status: 'rab_approval',
        progress: 54,
        deadline: '26 Mei',
    },
    {
        id: 'project-makrab-2026',
        name: 'Makrab Angkatan 2026',
        organization: 'UKM Kreatif',
        owner: 'Raka Pratama',
        status: 'draft',
        progress: 38,
        deadline: '2 Jun',
    },
];

export const tasks: TaskSummary[] = [
    {
        id: 'task-final-proposal',
        title: 'Finalisasi proposal',
        project: 'Seminar Karier Digital',
        pic: 'Nadia Putri',
        status: 'review',
        dueDate: '22 Mei',
    },
    {
        id: 'task-rab-konsumsi',
        title: 'Submit RAB konsumsi',
        project: 'Seminar Karier Digital',
        pic: 'Raka Pratama',
        status: 'in_progress',
        dueDate: '24 Mei',
    },
    {
        id: 'task-speaker',
        title: 'Konfirmasi pembicara',
        project: 'Seminar Karier Digital',
        pic: 'Dimas Aji',
        status: 'backlog',
        dueDate: '25 Mei',
    },
];

export const budgetItems: BudgetItemSummary[] = [
    {
        id: 'budget-consumption',
        item: 'Konsumsi peserta',
        category: 'Operational',
        amount: 6500000,
        owner: 'Bendahara',
        status: 'review',
    },
    {
        id: 'budget-venue',
        item: 'Sewa aula',
        category: 'Venue',
        amount: 8250000,
        owner: 'Ketua Pelaksana',
        status: 'draft',
    },
    {
        id: 'budget-publication',
        item: 'Publikasi',
        category: 'Marketing',
        amount: 1750000,
        owner: 'Humas',
        status: 'approved',
    },
];

export const documents: DocumentSummary[] = [
    {
        id: 'doc-proposal-v2',
        name: 'proposal-v2.pdf',
        folder: 'Proposal',
        owner: 'Secretary',
        visibility: 'private',
        status: 'ready',
    },
    {
        id: 'doc-receipt-consumption',
        name: 'receipt-consumption.jpg',
        folder: 'Finance Receipts',
        owner: 'Treasurer',
        visibility: 'restricted',
        status: 'review',
    },
    {
        id: 'doc-documentation-day-1',
        name: 'documentation-day-1.zip',
        folder: 'Documentation',
        owner: 'Media',
        visibility: 'committee',
        status: 'uploaded',
    },
];

export const notificationRules: NotificationRuleSummary[] = [
    {
        id: 'notify-task-deadline',
        event: 'task_deadline_reminder',
        audience: 'Assigned PIC',
        channel: 'in-app + email',
        trigger: 'H-1 deadline',
        status: 'planned',
    },
    {
        id: 'notify-finance-approval',
        event: 'finance_approval_requested',
        audience: 'Treasurer',
        channel: 'in-app',
        trigger: 'RAB submitted',
        status: 'planned',
    },
    {
        id: 'notify-member-invite',
        event: 'member_invite_sent',
        audience: 'Invitee',
        channel: 'email',
        trigger: 'Invitation created',
        status: 'planned',
    },
];
