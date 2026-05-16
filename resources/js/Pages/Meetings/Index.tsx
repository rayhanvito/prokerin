import { Head } from '@inertiajs/react';
import { CalendarDays, CheckCircle2, FileText, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface MeetingMetric {
    label: string;
    value: string;
    note: string;
}

interface MeetingItem {
    id: number;
    title: string;
    project: string;
    startsAt: string;
    location: string;
    status: string;
    attendeeCount: number;
    presentCount: number;
    hasMinutes: boolean;
}

interface MeetingActionItem {
    task: string;
    owner: string;
    due: string;
    status: string;
}

interface MeetingMinute {
    id: number;
    meetingTitle: string;
    summary: string;
    decisions: string[];
    actionItems: MeetingActionItem[];
    publishedAt: string | null;
}

interface MeetingsIndexProps {
    metrics: MeetingMetric[];
    meetings: MeetingItem[];
    latestMinutes: MeetingMinute[];
}

export default function MeetingsIndex({
    metrics,
    meetings,
    latestMinutes,
}: MeetingsIndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Collaboration
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Rapat & Notulen
                    </h1>
                </div>
            }
        >
            <Head title="Rapat & Notulen" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <CalendarDays className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Jadwal rapat, keputusan, dan tindak lanjut
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Pantau agenda rapat organisasi, kehadiran,
                                    notulen final, keputusan, dan action item
                                    agar hasil diskusi tidak hilang setelah
                                    meeting selesai.
                                </p>
                            </div>
                        </div>

                        <span className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#f5f7fb] px-4 py-2 text-sm font-semibold text-[#59667a] ring-1 ring-[#e6edef]">
                            Database-backed
                        </span>
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

                <section className="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
                    <VihoCard
                        title="Agenda Rapat"
                        subtitle="Tenant-scoped meeting schedule dengan status notulen."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {meetings.map((meeting) => (
                                <div
                                    key={meeting.id}
                                    className="grid gap-4 p-5 lg:grid-cols-[1fr_150px] lg:items-center"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-semibold text-[#242934]">
                                                {meeting.title}
                                            </p>
                                            {meeting.hasMinutes && (
                                                <span className="inline-flex items-center gap-1 rounded-[4px] bg-[rgba(36,105,92,0.1)] px-2 py-1 text-xs font-semibold text-[#24695c]">
                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                    Notulen
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-2 text-sm text-[#717171]">
                                            {meeting.project} ·{' '}
                                            {meeting.startsAt} ·{' '}
                                            {meeting.location}
                                        </p>
                                        <div className="mt-3 flex flex-wrap gap-3 text-xs font-semibold text-[#59667a]">
                                            <span className="inline-flex items-center gap-1">
                                                <Users className="h-3.5 w-3.5" />
                                                {meeting.presentCount}/
                                                {meeting.attendeeCount} hadir
                                            </span>
                                            <span className="inline-flex items-center gap-1">
                                                <FileText className="h-3.5 w-3.5" />
                                                {meeting.hasMinutes
                                                    ? 'Catatan tersedia'
                                                    : 'Menunggu notulen'}
                                            </span>
                                        </div>
                                    </div>
                                    <VihoStatusBadge>
                                        {meeting.status}
                                    </VihoStatusBadge>
                                </div>
                            ))}
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Notulen Terbaru"
                        subtitle="Keputusan dan tindak lanjut rapat yang sudah dicatat."
                    >
                        <div className="space-y-4">
                            {latestMinutes.map((minute) => (
                                <div
                                    key={minute.id}
                                    className="rounded-[4px] bg-[#f5f7fb] p-4"
                                >
                                    <p className="font-semibold text-[#242934]">
                                        {minute.meetingTitle}
                                    </p>
                                    <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                        {minute.summary}
                                    </p>

                                    <div className="mt-4 space-y-3">
                                        {minute.decisions.map((decision) => (
                                            <div
                                                key={decision}
                                                className="rounded-[4px] bg-white p-3 text-sm font-medium text-[#242934] ring-1 ring-[#e6edef]"
                                            >
                                                {decision}
                                            </div>
                                        ))}
                                    </div>

                                    <div className="mt-4 space-y-2">
                                        {minute.actionItems.map((item) => (
                                            <div
                                                key={`${item.task}-${item.owner}`}
                                                className="grid gap-1 text-sm sm:grid-cols-[1fr_auto]"
                                            >
                                                <span className="font-medium text-[#242934]">
                                                    {item.task}
                                                </span>
                                                <span className="text-[#717171]">
                                                    {item.owner} · {item.due}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </VihoCard>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
