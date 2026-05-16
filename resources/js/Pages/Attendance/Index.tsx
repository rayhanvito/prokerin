import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock3,
    Download,
    QrCode,
    RotateCw,
    ShieldCheck,
    Trash2,
    UserCheck,
} from 'lucide-react';
import type { FormEvent } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';

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
    activeTokenId: number | null;
    canManageQr: boolean;
}

interface AttendanceRecord {
    id: number;
    attendeeName: string;
    sessionTitle: string;
    method: string;
    checkedInAt: string;
    status: string;
}

interface AttendanceQrFlash {
    sessionId: number;
    tokenId: number;
    plainToken: string;
    expiresAt: string;
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
    const { props } = usePage<
        PageProps<{ flash: { attendanceQrToken?: AttendanceQrFlash } }>
    >();
    const issuedToken = props.flash.attendanceQrToken;

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

    const issueNewToken = (sessionId: number) => {
        router.post(
            route('attendance.qr-tokens.store', { session: sessionId }),
            {},
            { preserveScroll: true },
        );
    };

    const revokeToken = (tokenId: number) => {
        router.delete(
            route('attendance.qr-tokens.destroy', { token: tokenId }),
            { preserveScroll: true },
        );
    };

    const exportCsv = (sessionId: number) => {
        window.location.href = route('attendance.export.csv', {
            session: sessionId,
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

                {issuedToken && (
                    <VihoCard
                        title="QR Token Baru Diterbitkan"
                        subtitle={`Berlaku sampai ${issuedToken.expiresAt}. Tampilkan ke peserta untuk discan.`}
                    >
                        <div className="grid gap-6 lg:grid-cols-[auto_1fr] lg:items-center">
                            <div className="rounded-[4px] bg-white p-4 ring-1 ring-[#e6edef]">
                                <img
                                    src={`${route('attendance.qr-image.show')}?token=${encodeURIComponent(issuedToken.plainToken)}`}
                                    alt="QR Code"
                                    className="h-56 w-56"
                                />
                            </div>
                            <div className="space-y-3">
                                <p className="text-sm text-[#59667a]">
                                    Kode token (simpan, jangan share publik):
                                </p>
                                <code className="block break-all rounded-[4px] bg-[#f5f7fb] p-3 text-sm text-[#242934] ring-1 ring-[#e6edef]">
                                    {issuedToken.plainToken}
                                </code>
                                <div className="flex flex-wrap gap-2 text-xs">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            navigator.clipboard.writeText(
                                                issuedToken.plainToken,
                                            )
                                        }
                                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 font-semibold text-white hover:bg-[#1b4c43]"
                                    >
                                        Copy token
                                    </button>
                                    <a
                                        href={`${route('attendance.qr-image.show')}?token=${encodeURIComponent(issuedToken.plainToken)}`}
                                        download={`attendance-qr-${issuedToken.sessionId}.svg`}
                                        className="inline-flex items-center gap-2 rounded-[4px] bg-white px-3 py-2 font-semibold text-[#24695c] ring-1 ring-[#24695c] hover:bg-[rgba(36,105,92,0.05)]"
                                    >
                                        <Download className="h-3.5 w-3.5" />
                                        Download SVG
                                    </a>
                                </div>
                            </div>
                        </div>
                    </VihoCard>
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

                <section className="grid gap-6 xl:grid-cols-[1.25fr_0.95fr]">
                    <VihoCard
                        title="Sesi Absensi"
                        subtitle="Sesi tenant-scoped dengan ringkasan QR dan manual check-in."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {sessions.map((session) => (
                                <div key={session.id} className="p-5">
                                    <div className="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
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
                                                    {session.attendeeCount}{' '}
                                                    hadir
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

                                    {session.canManageQr && (
                                        <div className="mt-4 flex flex-wrap gap-2 rounded-[4px] bg-[#f5f7fb] p-3 ring-1 ring-[#e6edef]">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    issueNewToken(session.id)
                                                }
                                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1b4c43]"
                                            >
                                                <RotateCw className="h-3.5 w-3.5" />
                                                {session.activeTokenId
                                                    ? 'Regenerate QR'
                                                    : 'Generate QR'}
                                            </button>
                                            {session.activeTokenId !== null && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        revokeToken(
                                                            session.activeTokenId!,
                                                        )
                                                    }
                                                    className="inline-flex items-center gap-2 rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#d22d3d] ring-1 ring-[#d22d3d] hover:bg-[rgba(210,45,61,0.05)]"
                                                >
                                                    <Trash2 className="h-3.5 w-3.5" />
                                                    Revoke QR
                                                </button>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    exportCsv(session.id)
                                                }
                                                className="inline-flex items-center gap-2 rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#24695c] ring-1 ring-[#24695c] hover:bg-[rgba(36,105,92,0.05)]"
                                            >
                                                <Download className="h-3.5 w-3.5" />
                                                Export CSV
                                            </button>
                                        </div>
                                    )}
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
