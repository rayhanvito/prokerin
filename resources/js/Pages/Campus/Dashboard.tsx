import {
    Building2,
    ClipboardCheck,
    FolderKanban,
    GraduationCap,
    Users,
} from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head } from '@inertiajs/react';

interface CampusSummary {
    id: number;
    name: string;
    domain: string;
    adminName: string;
}

interface CampusMetric {
    label: string;
    value: string | number;
}

interface CampusOrganizationSummary {
    id: number;
    name: string;
    slug: string;
    status: string;
    planTier: string;
    memberCount: number;
    projectCount: number;
    activeProjectCount: number;
    completedProjectCount: number;
    rabTotal: number;
    realizationTotal: number;
    documentCount: number;
}

interface CampusProjectStatus {
    status: string;
    count: number;
}

interface CampusRecentProject {
    id: number;
    name: string;
    organizationName: string;
    status: string;
    progress: number;
}

interface CampusDashboardProps {
    campus: CampusSummary;
    metrics: CampusMetric[];
    organizations: CampusOrganizationSummary[];
    projectStatusBreakdown: CampusProjectStatus[];
    recentProjects: CampusRecentProject[];
}

const metricIcons = [Building2, FolderKanban, ClipboardCheck, Users];

export default function CampusDashboard({
    campus,
    metrics,
    organizations,
    projectStatusBreakdown,
    recentProjects,
}: CampusDashboardProps) {
    const organizationRows = organizations.map((organization) => ({
        name: organization.name,
        status: humanizeStatus(organization.status),
        planTier: organization.planTier.toUpperCase(),
        projects: String(organization.projectCount),
        members: String(organization.memberCount),
        rabTotal: formatRupiah(organization.rabTotal),
        realizationTotal: formatRupiah(organization.realizationTotal),
    }));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M24 · Campus Dashboard
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Dashboard Kampus
                    </h1>
                </div>
            }
        >
            <Head title="Dashboard Kampus" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <GraduationCap className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    {campus.name}
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Domain {campus.domain} · Admin{' '}
                                    {campus.adminName}
                                </p>
                            </div>
                        </div>
                        <VihoStatusBadge tone="success">
                            Read-only
                        </VihoStatusBadge>
                    </div>
                </VihoCard>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((metric, index) => {
                        const Icon = metricIcons[index] ?? Building2;

                        return (
                            <VihoCard key={metric.label} className="min-h-[128px]">
                                <div className="flex items-center justify-between gap-3">
                                    <p className="text-sm font-medium text-[#59667a]">
                                        {metric.label}
                                    </p>
                                    <span className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                        <Icon className="h-5 w-5" />
                                    </span>
                                </div>
                                <p className="mt-3 text-2xl font-semibold tracking-tight text-[#242934]">
                                    {metric.value}
                                </p>
                            </VihoCard>
                        );
                    })}
                </section>

                <div className="grid gap-6 xl:grid-cols-[1fr_360px]">
                    <VihoCard
                        title="Organisasi Terhubung"
                        subtitle="Agregat hanya dari organisasi yang ditautkan ke kampus ini."
                    >
                        <VihoDataTable
                            columns={[
                                { key: 'name', label: 'Organisasi' },
                                { key: 'status', label: 'Status' },
                                { key: 'planTier', label: 'Plan' },
                                { key: 'projects', label: 'Proker', align: 'right' },
                                { key: 'members', label: 'Anggota', align: 'right' },
                                { key: 'rabTotal', label: 'RAB', align: 'right' },
                                {
                                    key: 'realizationTotal',
                                    label: 'Realisasi',
                                    align: 'right',
                                },
                            ]}
                            rows={organizationRows}
                            statusKey="status"
                        />
                    </VihoCard>

                    <VihoCard title="Status Proker">
                        <div className="space-y-3">
                            {projectStatusBreakdown.map((item) => (
                                <div
                                    key={item.status}
                                    className="flex items-center justify-between rounded-[4px] bg-[#f5f7fb] px-4 py-3"
                                >
                                    <span className="text-sm font-semibold text-[#59667a]">
                                        {humanizeStatus(item.status)}
                                    </span>
                                    <span className="text-sm font-semibold text-[#242934]">
                                        {item.count}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </VihoCard>
                </div>

                <VihoCard
                    title="Proker Terbaru"
                    subtitle="Ringkasan lintas organisasi untuk monitoring kampus."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {recentProjects.map((project) => (
                            <div
                                key={project.id}
                                className="flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between"
                            >
                                <div>
                                    <p className="font-semibold text-[#242934]">
                                        {project.name}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {project.organizationName} ·{' '}
                                        {project.progress}% progress
                                    </p>
                                </div>
                                <VihoStatusBadge>
                                    {humanizeStatus(project.status)}
                                </VihoStatusBadge>
                            </div>
                        ))}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
