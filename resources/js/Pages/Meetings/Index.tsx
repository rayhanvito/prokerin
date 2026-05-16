import { Head } from '@inertiajs/react';
import {
    CalendarDays,
    CheckCircle2,
    ChevronDown,
    ChevronUp,
    FileText,
    Plus,
    Users,
} from 'lucide-react';
import { useState } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

import MeetingCreateForm from './Partials/MeetingCreateForm';
import MeetingDetail from './Partials/MeetingDetail';

export interface MeetingMetric {
    label: string;
    value: string;
    note: string;
}

export interface MeetingActionItem {
    task: string;
    owner: string;
    due: string;
    status: string;
}

export interface MeetingMinutesPayload {
    id: number;
    summary: string;
    decisions: string[];
    actionItems: MeetingActionItem[];
    publishedAt: string | null;
}

export interface MeetingAttendee {
    id: number;
    name: string;
    role: string | null;
    attendanceStatus: string;
    userId: number | null;
}

export interface MeetingItem {
    id: number;
    title: string;
    agenda: string;
    project: string;
    projectId: number | null;
    startsAt: string;
    endsAt: string | null;
    location: string;
    status: string;
    attendeeCount: number;
    presentCount: number;
    hasMinutes: boolean;
    minutes: MeetingMinutesPayload | null;
    attendees: MeetingAttendee[];
}

export interface MeetingMinute {
    id: number;
    meetingTitle: string;
    summary: string;
    decisions: string[];
    actionItems: MeetingActionItem[];
    publishedAt: string | null;
}

export interface MeetingFormOptions {
    canManage: boolean;
    projects: Array<{ id: number; name: string }>;
    organizationMembers: Array<{ id: number; name: string; role: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    attendanceStatusOptions: Array<{ value: string; label: string }>;
}

interface MeetingsIndexProps {
    metrics: MeetingMetric[];
    meetings: MeetingItem[];
    latestMinutes: MeetingMinute[];
    formOptions: MeetingFormOptions;
}

export default function MeetingsIndex({
    metrics,
    meetings,
    latestMinutes,
    formOptions,
}: MeetingsIndexProps) {
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [expandedMeetingId, setExpandedMeetingId] = useState<number | null>(
        null,
    );

    const toggleExpand = (meetingId: number) => {
        setExpandedMeetingId((current) =>
            current === meetingId ? null : meetingId,
        );
    };

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
                                    Buat rapat baru, catat kehadiran, publish
                                    notulen final, dan export agar hasil
                                    diskusi tidak hilang setelah meeting
                                    selesai.
                                </p>
                            </div>
                        </div>

                        {formOptions.canManage && (
                            <button
                                type="button"
                                onClick={() => setShowCreateForm((v) => !v)}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43]"
                            >
                                <Plus className="h-4 w-4" />
                                {showCreateForm ? 'Tutup form' : 'Buat Rapat'}
                            </button>
                        )}
                    </div>
                </VihoCard>

                {formOptions.canManage && showCreateForm && (
                    <MeetingCreateForm
                        formOptions={formOptions}
                        onSuccess={() => setShowCreateForm(false)}
                    />
                )}

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
                    title="Agenda Rapat"
                    subtitle="Tenant-scoped meeting schedule, kehadiran, notulen, dan export."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {meetings.length === 0 && (
                            <div className="p-6 text-center text-sm text-[#59667a]">
                                Belum ada rapat tercatat.
                            </div>
                        )}

                        {meetings.map((meeting) => {
                            const isExpanded = expandedMeetingId === meeting.id;

                            return (
                                <div key={meeting.id} className="p-5">
                                    <div className="grid gap-4 lg:grid-cols-[1fr_180px] lg:items-center">
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
                                                    {meeting.attendeeCount}{' '}
                                                    hadir
                                                </span>
                                                <span className="inline-flex items-center gap-1">
                                                    <FileText className="h-3.5 w-3.5" />
                                                    {meeting.hasMinutes
                                                        ? meeting.minutes
                                                              ?.publishedAt !==
                                                          null
                                                            ? 'Notulen publish'
                                                            : 'Draft notulen'
                                                        : 'Menunggu notulen'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex flex-col items-end gap-2">
                                            <VihoStatusBadge>
                                                {meeting.status}
                                            </VihoStatusBadge>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    toggleExpand(meeting.id)
                                                }
                                                className="inline-flex items-center gap-1 rounded-[4px] bg-[#f5f7fb] px-3 py-1 text-xs font-semibold text-[#24695c] ring-1 ring-[#e6edef] hover:bg-white"
                                            >
                                                {isExpanded ? (
                                                    <>
                                                        Tutup detail
                                                        <ChevronUp className="h-3.5 w-3.5" />
                                                    </>
                                                ) : (
                                                    <>
                                                        Kelola
                                                        <ChevronDown className="h-3.5 w-3.5" />
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    </div>

                                    {isExpanded && (
                                        <MeetingDetail
                                            meeting={meeting}
                                            formOptions={formOptions}
                                        />
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </VihoCard>

                <VihoCard
                    title="Notulen Terbaru"
                    subtitle="Keputusan dan tindak lanjut rapat yang sudah dicatat."
                >
                    <div className="grid gap-4 lg:grid-cols-2">
                        {latestMinutes.length === 0 && (
                            <p className="col-span-full text-sm text-[#59667a]">
                                Belum ada notulen ter-publish.
                            </p>
                        )}
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
                                    {minute.decisions.map((decision, index) => (
                                        <div
                                            key={`${minute.id}-d-${index}`}
                                            className="rounded-[4px] bg-white p-3 text-sm font-medium text-[#242934] ring-1 ring-[#e6edef]"
                                        >
                                            {decision}
                                        </div>
                                    ))}
                                </div>

                                <div className="mt-4 space-y-2">
                                    {minute.actionItems.map((item, index) => (
                                        <div
                                            key={`${minute.id}-a-${index}`}
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
            </div>
        </AuthenticatedLayout>
    );
}
