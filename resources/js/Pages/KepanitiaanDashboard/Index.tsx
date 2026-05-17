import { Head } from '@inertiajs/react';
import {
    CalendarDays,
    ClipboardCheck,
    FileText,
    Users,
    Wallet,
} from 'lucide-react';
import { ReactNode } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface KepanitiaanPayload {
    organization: {
        name: string;
        description: string;
        status: string;
        eventDate: string | null;
        autoArchiveAt: string | null;
        daysToEvent: number | null;
    };
    metrics: {
        projectCount: number;
        taskCompletion: number;
        pendingTasks: number;
        plannedBudget: number;
        realizedBudget: number;
        budgetRealization: number;
        attendanceCount: number;
        documentCount: number;
    };
    focusTasks: Array<{
        title: string;
        status: string;
        dueAt: string | null;
        projectName: string;
    }>;
}

interface KepanitiaanDashboardProps {
    payload: KepanitiaanPayload;
}

export default function KepanitiaanDashboardIndex({
    payload,
}: KepanitiaanDashboardProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Kepanitiaan Mode
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {payload.organization.name}
                    </h1>
                </div>
            }
        >
            <Head title={`Dashboard ${payload.organization.name}`} />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1.4fr_0.8fr]">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.16em] text-[#24695c]">
                                Event Workspace
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold text-[#242934]">
                                {payload.organization.name}
                            </h2>
                            <p className="mt-3 max-w-3xl text-sm leading-6 text-[#59667a]">
                                {payload.organization.description ||
                                    'Workspace kepanitiaan aktif untuk mengawal persiapan, administrasi, dokumentasi, dan pelaksanaan event.'}
                            </p>
                        </div>
                        <div className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4">
                            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-[#717171]">
                                Tanggal Event
                            </p>
                            <p className="mt-2 text-lg font-semibold text-[#242934]">
                                {payload.organization.eventDate ?? '-'}
                            </p>
                            <p className="mt-2 text-sm text-[#59667a]">
                                {eventCountdownLabel(
                                    payload.organization.daysToEvent,
                                )}
                            </p>
                        </div>
                    </div>
                </VihoCard>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <MetricCard
                        icon={<ClipboardCheck className="h-5 w-5" />}
                        label="Task Completion"
                        value={`${payload.metrics.taskCompletion}%`}
                        caption={`${payload.metrics.pendingTasks} task terbuka`}
                    />
                    <MetricCard
                        icon={<Wallet className="h-5 w-5" />}
                        label="Realisasi Budget"
                        value={`${payload.metrics.budgetRealization}%`}
                        caption={`${formatRupiah(
                            payload.metrics.realizedBudget,
                        )} dari ${formatRupiah(payload.metrics.plannedBudget)}`}
                    />
                    <MetricCard
                        icon={<Users className="h-5 w-5" />}
                        label="Absensi Hadir"
                        value={payload.metrics.attendanceCount.toString()}
                        caption="Total check-in tercatat"
                    />
                    <MetricCard
                        icon={<FileText className="h-5 w-5" />}
                        label="Dokumen"
                        value={payload.metrics.documentCount.toString()}
                        caption={`${payload.metrics.projectCount} proker/event`}
                    />
                </div>

                <VihoCard
                    title="Fokus Terdekat"
                    subtitle="Task aktif yang perlu dipantau sebelum hari pelaksanaan."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {payload.focusTasks.length === 0 ? (
                            <p className="p-5 text-sm text-[#59667a]">
                                Belum ada task aktif di workspace ini.
                            </p>
                        ) : (
                            payload.focusTasks.map((task) => (
                                <div
                                    key={`${task.projectName}-${task.title}`}
                                    className="flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p className="font-semibold text-[#242934]">
                                            {task.title}
                                        </p>
                                        <p className="mt-1 text-sm text-[#717171]">
                                            {task.projectName}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className="inline-flex items-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 py-1 text-xs font-semibold text-[#59667a]">
                                            <CalendarDays className="h-3.5 w-3.5" />
                                            {task.dueAt ?? '-'}
                                        </span>
                                        <span className="rounded-[4px] bg-[rgba(36,105,92,0.1)] px-3 py-1 text-xs font-semibold text-[#24695c]">
                                            {task.status}
                                        </span>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

interface MetricCardProps {
    icon: ReactNode;
    label: string;
    value: string;
    caption: string;
}

function MetricCard({ icon, label, value, caption }: MetricCardProps) {
    return (
        <VihoCard>
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-semibold text-[#59667a]">
                        {label}
                    </p>
                    <p className="mt-2 text-2xl font-semibold text-[#242934]">
                        {value}
                    </p>
                    <p className="mt-2 text-xs text-[#717171]">{caption}</p>
                </div>
                <span className="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                    {icon}
                </span>
            </div>
        </VihoCard>
    );
}

function eventCountdownLabel(daysToEvent: number | null): string {
    if (daysToEvent === null) {
        return 'Tanggal event belum disetel.';
    }

    if (daysToEvent > 0) {
        return `${daysToEvent} hari menuju event.`;
    }

    if (daysToEvent === 0) {
        return 'Event berlangsung hari ini.';
    }

    return `Event selesai ${Math.abs(daysToEvent)} hari lalu.`;
}

function formatRupiah(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}
