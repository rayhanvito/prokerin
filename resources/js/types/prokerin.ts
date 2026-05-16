export type ProjectStatus =
    | 'draft'
    | 'proposal_review'
    | 'rab_approval'
    | 'ready_to_execute'
    | 'running'
    | 'lpj_review'
    | 'completed'
    | 'archived';

export type ProjectTemplateType =
    | 'seminar'
    | 'workshop'
    | 'competition'
    | 'makrab';

export type TaskStatus =
    | 'backlog'
    | 'todo'
    | 'in_progress'
    | 'review'
    | 'done'
    | 'blocked';

export type FinanceStatus =
    | 'draft'
    | 'review'
    | 'approved'
    | 'rejected'
    | 'realized';

export type OrganizationRole =
    | 'organization_owner'
    | 'organization_admin'
    | 'secretary'
    | 'treasurer'
    | 'member'
    | 'viewer';

export type ProjectRole =
    | 'project_lead'
    | 'division_coordinator'
    | 'committee_member'
    | 'viewer';

export type InvitationStatus = 'pending' | 'accepted' | 'expired' | 'revoked';

export type InvitationDecision = 'accept' | 'revoke' | 'expire';

export type PermissionKey =
    | 'manage_organization'
    | 'manage_members'
    | 'manage_projects'
    | 'manage_finance'
    | 'approve_budget'
    | 'manage_documents'
    | 'view_reports';

export type DocumentVisibility = 'private' | 'restricted' | 'committee';

export type ExportDocumentType = 'proposal' | 'lpj';

export type ExportFormat = 'pdf' | 'docx';

export type NotificationChannel = 'in_app' | 'email';

export type NotificationEvent =
    | 'task_deadline_reminder'
    | 'finance_approval_requested'
    | 'member_invite_sent'
    | 'proposal_review_requested'
    | 'lpj_review_requested';

export type DashboardMetricTone =
    | 'primary'
    | 'success'
    | 'warning'
    | 'danger'
    | 'default';

export interface DashboardMetric {
    label: string;
    value: string;
    note: string;
    tone: DashboardMetricTone;
}

export interface DashboardPriorityItem {
    title: string;
    meta: string;
    status: string;
    progress: number;
    href: string | null;
}

export interface OrganizationSummary {
    id: string;
    name: string;
    period: string;
    role: OrganizationRole;
    isActive: boolean;
}

export interface ProjectSummary {
    id: string;
    name: string;
    organization: string;
    owner: string;
    status: ProjectStatus;
    progress: number;
    deadline: string;
}

export interface ProjectTemplatePlan {
    templateType: ProjectTemplateType;
    proposalOutline: string;
    tasks: Array<{
        title: string;
        division: string;
        dueOffsetDays: number;
        status: TaskStatus;
    }>;
    budgetLines: Array<{
        name: string;
        category: string;
        plannedAmount: number;
    }>;
    lpjChecklist: string[];
}

export interface ProjectTemplateSummary {
    type: ProjectTemplateType;
    label: string;
    plan: ProjectTemplatePlan;
}

export interface ProposalDraft {
    id: number | null;
    title: string;
    subtitle: string;
    status: 'draft' | 'submitted' | 'approved' | 'empty';
    projectSlug: string | null;
    projectStatus: ProjectStatus | null;
    canSubmit: boolean;
    sections: Array<{
        heading: string;
        body: string;
    }>;
}

export interface TaskSummary {
    id: string;
    title: string;
    project: string;
    pic: string;
    status: TaskStatus;
    dueDate: string;
}

export interface BudgetItemSummary {
    id: string;
    item: string;
    category: string;
    amount: number;
    owner: string;
    status: FinanceStatus;
}

export interface DocumentSummary {
    id: string;
    name: string;
    folder: string;
    owner: string;
    visibility: DocumentVisibility;
    status: 'ready' | 'review' | 'uploaded';
}

export interface NotificationRuleSummary {
    id: string;
    event: NotificationEvent;
    audience: string;
    channel: 'in-app' | 'email' | 'in-app + email';
    trigger: string;
    status: 'planned' | 'active' | 'paused';
}

export interface RolePermissionSummary {
    role: OrganizationRole | ProjectRole;
    label: string;
    scope: 'organization' | 'project';
    permissions: PermissionKey[];
    isSystemRole: boolean;
}

export interface ExportQueuePlan {
    queueName: string;
    engine: 'browsershot' | 'phpword';
    storageDisk: 's3';
    outputPath: string;
    shouldQueue: boolean;
}
