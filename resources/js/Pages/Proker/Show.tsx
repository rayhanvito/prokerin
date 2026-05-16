import { Archive, CalendarDays, FileText, ReceiptText, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { Head, Link, useForm } from '@inertiajs/react';

interface ProjectDetail {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    progress: number;
    startsAt: string | null;
    endsAt: string | null;
    organization: string;
    lead: string | null;
}

interface ProjectMetric {
    label: string;
    value: string;
}

interface ProjectTaskRow extends Record<string, string> {
    task: string;
    pic: string;
    due: string;
    status: string;
}

interface ProkerShowProps {
    project: ProjectDetail;
    metrics: ProjectMetric[];
    tasks: ProjectTaskRow[];
}

const metricIcons = [CalendarDays, Users, ReceiptText, FileText];

export default function ProkerShow({
    project,
    metrics,
    tasks,
}: ProkerShowProps) {
    const { delete: destroy, processing } = useForm();

    const archiveProject = (): void => {
        if (
            window.confirm(
                'Arsipkan proker ini? Data task, dokumen, RAB, proposal, dan LPJ tetap tersimpan.',
            )
        ) {
            destroy(route('proker.destroy', project.slug), {
                preserveScroll: true,
            });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Detail Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {project.name}
                    </h1>
                </div>
            }
        >
            <Head title={project.name} />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <div className="flex flex-wrap items-center gap-3">
                                <h2 className="text-2xl font-semibold text-[#242934]">
                                    {project.name}
                                </h2>
                                <VihoStatusBadge>
                                    {humanizeStatus(project.status)}
                                </VihoStatusBadge>
                            </div>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                {project.description ??
                                    'Draft proker belum memiliki deskripsi.'}
                            </p>
                            <p className="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                {project.organization} · PIC{' '}
                                {project.lead ?? '-'} · Progress{' '}
                                {project.progress}%
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href={route('proker.edit', project.slug)}
                                className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                            >
                                Edit Proker
                            </Link>
                            <button
                                type="button"
                                disabled={
                                    processing || project.status === 'archived'
                                }
                                onClick={archiveProject}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#d22d3d] hover:text-[#d22d3d] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Archive className="h-4 w-4" />
                                Arsipkan
                            </button>
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((metric, index) => {
                        const Icon = metricIcons[index] ?? CalendarDays;

                        return (
                            <VihoCard key={metric.label}>
                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <p className="mt-4 text-sm text-[#717171]">
                                    {metric.label}
                                </p>
                                <p className="mt-1 text-2xl font-semibold text-[#242934]">
                                    {metric.value}
                                </p>
                            </VihoCard>
                        );
                    })}
                </section>

                <VihoCard
                    title="Task Terdekat"
                    subtitle="Task ringkas yang tersambung ke execution view."
                >
                    <VihoDataTable
                        columns={[
                            { key: 'task', label: 'Task' },
                            { key: 'pic', label: 'PIC' },
                            { key: 'due', label: 'Due' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={tasks}
                        statusKey="status"
                    />
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
