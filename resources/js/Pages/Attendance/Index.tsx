import { Head, useForm } from '@inertiajs/react';
import { CheckCircle2, Clock3, QrCode, ShieldCheck, UserCheck } from 'lucide-react';
import type { FormEvent } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface AttendanceMetric {
    label: string;
    value: string;
    note: string;
}

interface AttendanceSession {
    id: number;
    title: string;
    project: string;
    meeting: string;
    startsAt: string;
    status: string;
    expiresAt: string | null;
    attendeeCount: number;
    presentCount: number;
    qrCount: number;
    manualCount: number;
}

interface AttendanceRecord {
    id: number;
    attendeeName: string;
    sessionTitle: string;
    method: string;
    checkedInAt: string;
    status: string;
}

interface AttendanceIndexProps {
    metrics: AttendanceMetric[];
    sessions: AttendanceSession[];
    recentRecords: AttendanceRecord[];
}

export default function AttendanceIndex({
    metrics,
    sessions,
    recentRecords,
}: AttendanceIndexProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: '',
    });

    const submitQrCheckIn = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        post(route('attendance.check-in.store'), {
            preserveScroll: true,
            onSuccess: () => reset('token'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Attendance
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Absensi QR
                    </h1>
                </div>
            }
        >
            <Head title="Absensi QR" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 xl:grid-cols-[1fr_360px] xl:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <QrCode className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Check-in rapat dan kegiatan dengan QR
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Sesi absensi terhubung ke rapat, proker, dan
                                    anggota organisasi. Token QR divalidasi
                                    dengan expiry, tenant scope, dan guard
                                    anti-duplikat.
                                </p>
                            </div>
                        </div>

                        <form
                            onSubmit={submitQrCheckIn}
                            className="rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]"
                        >
                            <label
                                htmlFor="token"
                                className="text-sm font-semibold text-[#242934]"
                            >
                                Token QR
                            </label>
                            <div className="mt-2 flex gap-2">
                                <TextInput
                                    id="token"
                                    value={data.token}
                                    onChange={(event) =>
                                        setData('token', event.target.value)
                                    }
                                    className="block w-full"
                                    placeholder="Paste token hasil scan"
                                />
                                <PrimaryButton disabled={processing}>
                                    Check-in
                                </PrimaryButton>
                            </div>
                            <InputError
                                message={errors.token}
                                className="mt-2"
                            />
                        </form>
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

                <section className="grid gap-6 xl:grid-cols-[1.25fr_0.95fr]">
                    <VihoCard
                        title="Sesi Absensi"
                        subtitle="Sesi tenant-scoped dengan ringkasan QR dan manual check-in."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {sessions.map((session) => (
                                <div
                                    key={session.id}
                                    className="grid gap-4 p-5 lg:grid-cols-[1fr_150px] lg:items-center"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-semibold text-[#242934]">
                                                {session.title}
                                            </p>
                                            <span className="inline-flex items-center gap-1 rounded-[4px] bg-[rgba(36,105,92,0.1)] px-2 py-1 text-xs font-semibold text-[#24695c]">
                                                <ShieldCheck className="h-3.5 w-3.5" />
                                                Tenant scoped
                                            </span>
                                        </div>
                                        <p className="mt-2 text-sm text-[#717171]">
                                            {session.project} ·{' '}
                                            {session.meeting} ·{' '}
                                            {session.startsAt}
                                        </p>
                                        <div className="mt-3 flex flex-wrap gap-3 text-xs font-semibold text-[#59667a]">
                                            <span className="inline-flex items-center gap-1">
                                                <UserCheck className="h-3.5 w-3.5" />
                                                {session.presentCount}/
                                                {session.attendeeCount} hadir
                                            </span>
                                            <span className="inline-flex items-center gap-1">
                                                <QrCode className="h-3.5 w-3.5" />
                                                {session.qrCount} QR
                                            </span>
                                            <span className="inline-flex items-center gap-1">
                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                {session.manualCount} manual
                                            </span>
                                            {session.expiresAt && (
                                                <span className="inline-flex items-center gap-1">
                                                    <Clock3 className="h-3.5 w-3.5" />
                                                    Exp {session.expiresAt}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <VihoStatusBadge>
                                        {session.status}
                                    </VihoStatusBadge>
                                </div>
                            ))}
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Check-in Terbaru"
                        subtitle="Audit ringkas absensi QR dan fallback manual."
                    >
                        <div className="space-y-3">
                            {recentRecords.map((record) => (
                                <div
                                    key={record.id}
                                    className="rounded-[4px] bg-[#f5f7fb] p-4"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {record.attendeeName}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {record.sessionTitle}
                                            </p>
                                        </div>
                                        <VihoStatusBadge>
                                            {record.method}
                                        </VihoStatusBadge>
                                    </div>
                                    <p className="mt-3 text-xs font-semibold text-[#59667a]">
                                        {record.checkedInAt} · {record.status}
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
