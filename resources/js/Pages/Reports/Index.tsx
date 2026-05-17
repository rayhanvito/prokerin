import { DownloadCloud, FileText, FileWarning, FolderClock } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

interface MetricItem {
    label: string;
    value: string;
    note: string;
}

interface StatusCount {
    status: string;
    count: number;
}

interface ExportQueueItem {
    id: number;
    title: string;
    type: string;
    format: string;
    status: string;
    requestedBy: string;
    downloadUrl: string | null;
}

interface RecentProject {
    id: number;
    name: string;
    status: string;
    proposalStatus: string | null;
    lpjRequired: number;
    lpjComplete: number;
}

interface ReportsIndexProps {
    metrics: MetricItem[];
    proposalStatuses: StatusCount[];
    lpjStatuses: StatusCount[];
    exportQueue: ExportQueueItem[];
    recentProjects: RecentProject[];
}

export default function ReportsIndex({
    metrics,
    proposalStatuses,
    lpjStatuses,
    exportQueue,
    recentProjects,
}: ReportsIndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M08 · M10 · Documents
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Proposal & LPJ
                    </h1>
                </div>
            }
        >
            <Head title="Proposal & LPJ" />

            <div className="space-y-6">
                <section className="grid gap-4 md:grid-cols-3">
                    {metrics.map((metric) => (
                        <VihoCard key={metric.label}>
                            <p className="text-sm font-semibold text-[#59667a]">
                                {metric.label}
                            </p>
                            <p className="mt-3 text-3xl font-bold text-[#242934]">
                                {metric.value}
                            </p>
                            <p className="mt-2 text-sm text-[#717171]">
                                {metric.note}
                            </p>
                        </VihoCard>
                    ))}
                </section>

                <section className="grid gap-6 xl:grid-cols-[1fr_0.95fr]">
                    <VihoCard
                        title="Status Dokumen"
                        action={
                            <Link
                                href={route('reports.proposal-editor')}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                            >
                                <FileText className="h-4 w-4" />
                                Buka Proposal
                            </Link>
                        }
                    >
                        <div className="grid gap-4 md:grid-cols-2">
                            <StatusPanel
                                title="Proposal"
                                emptyLabel="Belum ada proposal draft."
                                statuses={proposalStatuses}
                            />
                            <StatusPanel
                                title="LPJ"
                                emptyLabel="Belum ada proker LPJ."
                                statuses={lpjStatuses}
                            />
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Export Queue Terbaru"
                        action={
                            <Link
                                href={route('reports.export-queue')}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                            >
                                <FolderClock className="h-4 w-4" />
                                Semua Queue
                            </Link>
                        }
                    >
                        {exportQueue.length === 0 ? (
                            <EmptyState
                                icon={DownloadCloud}
                                title="Belum ada export"
                                description="PDF dan DOCX yang di-queue akan muncul di sini."
                            />
                        ) : (
                            <div className="-m-5 divide-y divide-[#e6edef]">
                                {exportQueue.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {item.title}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {humanizeStatus(item.type)} ·{' '}
                                                {item.format.toUpperCase()} ·{' '}
                                                {item.requestedBy}
                                            </p>
                                        </div>
                                        {item.downloadUrl ? (
                                            <Link
                                                href={item.downloadUrl}
                                                className="text-sm font-semibold text-[#24695c]"
                                            >
                                                Download
                                            </Link>
                                        ) : (
                                            <span className="rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm font-semibold text-[#59667a]">
                                                {humanizeStatus(item.status)}
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </VihoCard>
                </section>

                <VihoCard title="Proker Terkait Dokumen">
                    {recentProjects.length === 0 ? (
                        <EmptyState
                            icon={FileWarning}
                            title="Belum ada proker"
                            description="Proposal dan LPJ akan terhubung otomatis saat proker dibuat."
                        />
                    ) : (
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {recentProjects.map((project) => {
                                const progress =
                                    project.lpjRequired === 0
                                        ? 0
                                        : Math.round(
                                              (project.lpjComplete /
                                                  project.lpjRequired) *
                                                  100,
                                          );

                                return (
                                    <div
                                        key={project.id}
                                        className="grid gap-3 p-5 md:grid-cols-[1fr_180px_140px]"
                                    >
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {project.name}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {humanizeStatus(project.status)} · Proposal{' '}
                                                {humanizeStatus(
                                                    project.proposalStatus ?? 'none',
                                                )}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm font-semibold text-[#59667a]">
                                                LPJ Checklist
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {project.lpjComplete}/{project.lpjRequired}{' '}
                                                item
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <div className="h-2 flex-1 rounded-full bg-[#e6edef]">
                                                <div
                                                    className="h-2 rounded-full bg-[#24695c]"
                                                    style={{ width: `${progress}%` }}
                                                />
                                            </div>
                                            <span className="text-sm font-semibold text-[#24695c]">
                                                {progress}%
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

interface StatusPanelProps {
    title: string;
    emptyLabel: string;
    statuses: StatusCount[];
}

function StatusPanel({ title, emptyLabel, statuses }: StatusPanelProps) {
    return (
        <div className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4">
            <p className="text-sm font-semibold text-[#242934]">{title}</p>
            {statuses.length === 0 ? (
                <p className="mt-3 text-sm text-[#717171]">{emptyLabel}</p>
            ) : (
                <div className="mt-3 space-y-2">
                    {statuses.map((status) => (
                        <div
                            key={status.status}
                            className="flex items-center justify-between rounded-[4px] bg-white px-3 py-2 text-sm"
                        >
                            <span className="font-semibold text-[#59667a]">
                                {humanizeStatus(status.status)}
                            </span>
                            <span className="text-[#24695c]">{status.count}</span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
