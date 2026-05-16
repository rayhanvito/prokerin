import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowUpRight,
    CalendarCheck,
    CheckCircle2,
    Clock3,
    FileText,
    ReceiptText,
    Users,
} from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type {
    DashboardMetric,
    DashboardMetricTone,
    DashboardPriorityItem,
} from '@/types/prokerin';

interface DashboardProps {
    metrics: DashboardMetric[];
    priorityProjects: DashboardPriorityItem[];
    weeklyFocus: string[];
    memberSummary: {
        value: string;
        note: string;
    };
}

const metricIcons: Record<DashboardMetricTone, typeof CheckCircle2> = {
    primary: CalendarCheck,
    success: CheckCircle2,
    warning: ReceiptText,
    danger: FileText,
    default: CheckCircle2,
};

const metricTones: Record<DashboardMetricTone, string> = {
    primary: 'bg-[rgba(36,105,92,0.1)] text-[#24695c]',
    success: 'bg-[rgba(27,76,67,0.1)] text-[#1b4c43]',
    warning: 'bg-[rgba(186,137,93,0.12)] text-[#ba895d]',
    danger: 'bg-[rgba(210,45,61,0.1)] text-[#d22d3d]',
    default: 'bg-[#f5f7fb] text-[#59667a]',
};

export default function Dashboard({
    metrics,
    priorityProjects,
    weeklyFocus,
    memberSummary,
}: DashboardProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Workspace Overview
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Dashboard Prokerin
                    </h1>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="space-y-6">
                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((metric) => {
                        const Icon = metricIcons[metric.tone];

                        return (
                            <VihoCard
                                key={metric.label}
                                className="min-h-[150px]"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-[#59667a]">
                                            {metric.label}
                                        </p>
                                        <p className="mt-3 text-3xl font-semibold tracking-tight text-[#242934]">
                                            {metric.value}
                                        </p>
                                    </div>
                                    <span
                                        className={`inline-flex h-11 w-11 items-center justify-center rounded-[4px] ${
                                            metricTones[metric.tone]
                                        }`}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </span>
                                </div>
                                <p className="mt-4 text-sm text-[#717171]">
                                    {metric.note}
                                </p>
                            </VihoCard>
                        );
                    })}
                </section>

                <section className="grid gap-6 xl:grid-cols-[1.6fr_0.9fr]">
                    <VihoCard
                        title="Proker Prioritas"
                        subtitle="Ringkasan pekerjaan yang butuh perhatian pengurus."
                        action={
                            <Link
                                href={route('proker.create')}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                            >
                                Buat Proker
                                <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        }
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {priorityProjects.map((project) => (
                                <Link
                                    key={project.title}
                                    href={project.href ?? route('proker.index')}
                                    className="grid gap-4 p-5 md:grid-cols-[1fr_150px_110px] md:items-center"
                                >
                                    <div>
                                        <p className="font-semibold text-[#242934]">
                                            {project.title}
                                        </p>
                                        <p className="mt-1 text-sm text-[#717171]">
                                            {project.meta}
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
                                    <div className="text-sm">
                                        <p className="font-semibold text-[#242934]">
                                            {project.progress}%
                                        </p>
                                        <p className="text-[#717171]">
                                            progress
                                        </p>
                                    </div>
                                    <div className="inline-flex items-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm font-semibold text-[#59667a]">
                                        <Clock3 className="h-4 w-4" />
                                        {project.status}
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </VihoCard>

                    <div className="space-y-6">
                        <VihoCard>
                            <div className="flex items-center justify-between">
                                <h2 className="text-base font-semibold text-[#242934]">
                                    Fokus Minggu Ini
                                </h2>
                                <AlertTriangle className="h-5 w-5 text-[#ba895d]" />
                            </div>
                            <div className="mt-5 space-y-3">
                                {weeklyFocus.map((item, index) => (
                                    <div
                                        key={item}
                                        className="flex items-start gap-3 rounded-[4px] bg-[#f5f7fb] p-3"
                                    >
                                        <span className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-[#24695c] ring-1 ring-[#e6edef]">
                                            {index + 1}
                                        </span>
                                        <p className="text-sm font-medium text-[#59667a]">
                                            {item}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </VihoCard>

                        <section className="rounded-[4px] border border-[#24695c] bg-[#24695c] p-5 text-white shadow-[0_25px_50px_rgba(8,21,66,0.06)]">
                            <div className="flex items-center gap-3">
                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-white/10">
                                    <Users className="h-5 w-5" />
                                </span>
                                <div>
                                    <h2 className="text-base font-semibold">
                                        {memberSummary.value}
                                    </h2>
                                    <p className="text-sm text-white/75">
                                        {memberSummary.note}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
