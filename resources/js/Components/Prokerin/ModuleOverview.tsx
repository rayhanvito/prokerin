import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export interface ModuleMetric {
    label: string;
    value: string;
    note: string;
}

export interface ModuleItem {
    title: string;
    meta: string;
    status: string;
    progress?: number;
    href?: string;
}

interface ModuleOverviewProps {
    title: string;
    eyebrow: string;
    description: string;
    actionLabel: string;
    actionHref?: string;
    icon: LucideIcon;
    metrics: ModuleMetric[];
    items: ModuleItem[];
    focus: string[];
}

export default function ModuleOverview({
    title,
    eyebrow,
    description,
    actionLabel,
    actionHref,
    icon: Icon,
    metrics,
    items,
    focus,
}: ModuleOverviewProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        {eyebrow}
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {title}
                    </h1>
                </div>
            }
        >
            <Head title={title} />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <Icon className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    {title}
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    {description}
                                </p>
                            </div>
                        </div>

                        {actionHref ? (
                            <Link
                                href={actionHref}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                            >
                                {actionLabel}
                                <ArrowUpRight className="h-4 w-4" />
                            </Link>
                        ) : (
                            <span className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#f5f7fb] px-4 py-2 text-sm font-semibold text-[#59667a] ring-1 ring-[#e6edef]">
                                {actionLabel}
                            </span>
                        )}
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

                <section className="grid gap-6 xl:grid-cols-[1.5fr_0.85fr]">
                    <VihoCard
                        title="Daftar Prioritas"
                        subtitle="Ringkasan item yang paling perlu dipantau oleh pengurus."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {items.map((item) => {
                                const content = (
                                    <>
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {item.title}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {item.meta}
                                            </p>
                                            {typeof item.progress ===
                                                'number' && (
                                                <div className="mt-4 h-2 rounded-full bg-[#e6edef]">
                                                    <div
                                                        className="h-2 rounded-full bg-[#24695c]"
                                                        style={{
                                                            width: `${item.progress}%`,
                                                        }}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                        <VihoStatusBadge>
                                            {item.status}
                                        </VihoStatusBadge>
                                    </>
                                );

                                if (item.href) {
                                    return (
                                        <Link
                                            key={item.title}
                                            href={item.href}
                                            className="grid gap-4 p-5 transition hover:bg-[#f8fafb] md:grid-cols-[1fr_150px] md:items-center"
                                        >
                                            {content}
                                        </Link>
                                    );
                                }

                                return (
                                    <div
                                        key={item.title}
                                        className="grid gap-4 p-5 md:grid-cols-[1fr_150px] md:items-center"
                                    >
                                        {content}
                                    </div>
                                );
                            })}
                        </div>
                    </VihoCard>

                    <VihoCard title="Fokus Berikutnya">
                        <div className="space-y-3">
                            {focus.map((item, index) => (
                                <div
                                    key={item}
                                    className="flex gap-3 rounded-[4px] bg-[#f5f7fb] p-3"
                                >
                                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-[#24695c] ring-1 ring-[#e6edef]">
                                        {index + 1}
                                    </span>
                                    <p className="text-sm font-medium leading-6 text-[#59667a]">
                                        {item}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </VihoCard>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
