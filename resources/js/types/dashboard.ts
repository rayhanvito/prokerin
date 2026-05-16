export type DashboardVariant =
    | 'pimpinan'
    | 'sekretaris'
    | 'bendahara'
    | 'operasional'
    | 'member'
    | 'viewer';

export interface KpiMetric {
    label: string;
    value: string | number;
}

export interface ApprovalQueueItem {
    id: number;
    type: string;
    prokerName: string;
    submittedBy: string;
    submittedAt: string;
    approvalInstanceId?: number;
}

export interface ProkerSummary {
    id: number;
    slug: string;
    name: string;
    status: string;
    progressPercentage: number;
    deadline: string | null;
    projectLead: string;
}

export interface FinanceProjectSummary {
    prokerName: string;
    rabTotal: number;
    realisasiTotal: number;
    usagePercentage: number;
    isOverBudget: boolean;
}

export interface SimpleItem {
    id?: number | string;
    title?: string;
    name?: string;
    label?: string;
    status?: string;
    projectName?: string;
    meta?: string;
    [key: string]: string | number | boolean | null | undefined;
}

export interface PimpinanPayload {
    kpiMetrics: KpiMetric[];
    approvalQueue: ApprovalQueueItem[];
    priorityProjects: ProkerSummary[];
    financeSummary: FinanceProjectSummary[];
    upcomingMeetings: SimpleItem[];
    memberActivity: SimpleItem[];
}

export interface SekretarisPayload {
    kpiMetrics: KpiMetric[];
    proposalStatusOverview: SimpleItem[];
    lpjChecklistOverview: SimpleItem[];
    meetingsWithoutMinutes: SimpleItem[];
    pendingInvitations: SimpleItem[];
    recentDocuments: SimpleItem[];
}

export interface BendaharaPayload {
    kpiMetrics: KpiMetric[];
    pendingTransactions: SimpleItem[];
    rabVsRealisasiChart: FinanceProjectSummary[];
    overBudgetProjects: FinanceProjectSummary[];
    recentTransactions: SimpleItem[];
}

export interface OperasionalPayload {
    kpiMetrics: KpiMetric[];
    myProjects: ProkerSummary[];
    urgentTasks: SimpleItem[];
    upcomingMilestones: SimpleItem[];
    teamSummary: SimpleItem[];
}

export interface MemberPayload {
    kpiMetrics: KpiMetric[];
    myTasks: SimpleItem[];
    myProjects: ProkerSummary[];
    recentNotifications: SimpleItem[];
}

export type DashboardPayload =
    | PimpinanPayload
    | SekretarisPayload
    | BendaharaPayload
    | OperasionalPayload
    | MemberPayload;

export interface DashboardPageProps {
    dashboardVariant: DashboardVariant;
    payload: DashboardPayload;
}
