import { Head, Link, router } from '@inertiajs/react';
import { ListChecks, Search } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';

interface ProjectMetric {
    label: string;
    value: string;
    note: string;
}

interface ProjectRow {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    progress: number;
    startsAt: string | null;
    endsAt: string | null;
    lead: string;
    memberCount: number;
}

interface ProkerIndexProps {
    filters: {
        search: string;
        status: string;
        period: string;
    };
    metrics: ProjectMetric[];
    projects: ProjectRow[];
}

const statusOptions = [
    'all',
    'draft',
    'proposal_review',
    'rab_approval',
    'ready_to_execute',
    'running',
    'lpj_review',
    'completed',
    'archived',
];

export default function ProkerIndex({
    filters,
    metrics,
    projects,
}: ProkerIndexProps) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        router.get(
            route('proker.index'),
            { search, status },
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Proker Management
                    </h1>
                </div>
            }
        >
            <Head title="Proker Management" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <ListChecks className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Proker Management
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Kelola daftar program kerja organisasi,
                                    status approval, PIC, dan kesiapan eksekusi.
                                </p>
                            </div>
                        </div>
                        <Link
                            href={route('proker.create')}
                            className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                        >
                            Buat Proker
                        </Link>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {metrics.map((metric) => (
                        <VihoCard key={metric.label}>
                            <p className="text-sm font-medium text-[#59667a]">
                                {metric.label}
                            </p>
                            <p className="mt-3 text-3xl font-semibold text-[#242934]">
                                {metric.value}
                            </p>
                            <p className="mt-3 text-sm text-[#717171]">
                                {metric.note}
                            </p>
                        </VihoCard>
                    ))}
                </section>

                <VihoCard
                    title="Daftar Proker"
                    subtitle="Data project tenant-scoped dari organisasi aktif."
                >
                    <form
                        onSubmit={submit}
                        className="mb-5 grid gap-3 md:grid-cols-[1fr_220px_auto]"
                    >
                        <label className="relative block">
                            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#717171]" />
                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] pl-9 text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                placeholder="Cari nama proker"
                            />
                        </label>
                        <select
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                            className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                        >
                            {statusOptions.map((statusOption) => (
                                <option key={statusOption} value={statusOption}>
                                    {statusOption === 'all'
                                        ? 'All status'
                                        : humanizeStatus(statusOption)}
                                </option>
                            ))}
                        </select>
                        <button
                            type="submit"
                            className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                        >
                            Filter
                        </button>
                    </form>

                    {projects.length === 0 ? (
                        <EmptyState
                            icon={ListChecks}
                            title="Belum ada proker"
                            description="Proker yang dibuat untuk organisasi aktif akan muncul di daftar ini."
                            action={{
                                label: 'Buat Proker',
                                href: route('proker.create'),
                            }}
                        />
                    ) : (
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {projects.map((project) => (
                                <Link
                                    key={project.id}
                                    href={route('proker.detail', project.slug)}
                                    className="grid gap-4 p-5 transition hover:bg-[#f8fafb] lg:grid-cols-[1fr_180px] lg:items-center"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-semibold text-[#242934]">
                                                {project.name}
                                            </p>
                                            <VihoStatusBadge>
                                                {humanizeStatus(project.status)}
                                            </VihoStatusBadge>
                                        </div>
                                        <p className="mt-1 text-sm text-[#717171]">
                                            PIC {project.lead} ·{' '}
                                            {project.memberCount} anggota ·{' '}
                                            {formatDateRange(project)}
                                        </p>
                                        <div className="mt-4 h-2 rounded-full bg-[#e6edef]">
                                            <div
                                                className="h-2 rounded-full bg-[#24695c]"
                                                style={{
                                                    width: `${project.progress}%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                    <p className="text-sm font-semibold text-[#24695c] lg:text-right">
                                        {project.progress}%
                                    </p>
                                </Link>
                            ))}
                        </div>
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function formatDateRange(project: ProjectRow): string {
    if (!project.startsAt) {
        return '-';
    }

    if (!project.endsAt || project.endsAt === project.startsAt) {
        return project.startsAt;
    }

    return `${project.startsAt} - ${project.endsAt}`;
}
