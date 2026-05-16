import { Head, Link } from '@inertiajs/react';
import { Download, ExternalLink, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';

interface RegistrationMetrics {
    eventsOpen: number;
    totalRegistrations: number;
    confirmedRegistrations: number;
    pendingRegistrations: number;
}

interface RegistrationEvent {
    id: number;
    name: string;
    slug: string;
    organizationName: string;
    isOpen: boolean;
    capacity: number | null;
    registeredCount: number;
    remainingQuota: number | null;
    requirePayment: boolean;
    publicUrl: string;
}

interface EventRegistration {
    id: number;
    participantName: string;
    participantEmail: string;
    phone: string;
    institution: string;
    status: string;
    registeredAt: string;
    projectName: string;
}

interface RegistrationsProps {
    metrics: RegistrationMetrics;
    events: RegistrationEvent[];
    registrations: EventRegistration[];
}

export default function Registrations({
    metrics,
    events,
    registrations,
}: RegistrationsProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M21 · Event Registration
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Registrasi Event
                    </h1>
                </div>
            }
        >
            <Head title="Registrasi Event" />

            <div className="grid gap-4 md:grid-cols-4">
                {[
                    ['Event Dibuka', metrics.eventsOpen],
                    ['Total Peserta', metrics.totalRegistrations],
                    ['Terkonfirmasi', metrics.confirmedRegistrations],
                    ['Pending', metrics.pendingRegistrations],
                ].map(([label, value]) => (
                    <div
                        key={label}
                        className="rounded-[4px] border border-[#e6edef] bg-white p-4 shadow-sm"
                    >
                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                            {label}
                        </p>
                        <p className="mt-2 text-2xl font-semibold text-[#242934]">
                            {value}
                        </p>
                    </div>
                ))}
            </div>

            <div className="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.25fr]">
                <VihoCard
                    title="Event Publik"
                    subtitle="Link registrasi publik untuk proker yang sudah dibuka."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {events.length > 0 ? (
                            events.map((event) => (
                                <div key={event.id} className="p-5">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {event.name}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {event.organizationName}
                                            </p>
                                        </div>
                                        <VihoStatusBadge>
                                            {event.isOpen ? 'open' : 'closed'}
                                        </VihoStatusBadge>
                                    </div>
                                    <div className="mt-4 grid grid-cols-2 gap-2 text-sm text-[#59667a]">
                                        <Metric
                                            label="Pendaftar"
                                            value={`${event.registeredCount}/${event.capacity ?? '∞'}`}
                                        />
                                        <Metric
                                            label="Sisa"
                                            value={String(event.remainingQuota ?? 'Tidak dibatasi')}
                                        />
                                    </div>
                                    <Link
                                        href={event.publicUrl}
                                        className="mt-4 inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#24695c] transition hover:border-[#24695c]"
                                    >
                                        <ExternalLink className="h-3.5 w-3.5" />
                                        Buka Form Publik
                                    </Link>
                                </div>
                            ))
                        ) : (
                            <div className="p-5 text-sm text-[#59667a]">
                                Belum ada pengaturan registrasi event.
                            </div>
                        )}
                    </div>
                </VihoCard>

                <VihoCard
                    title="Daftar Peserta"
                    subtitle="Data peserta tenant-scoped dari event publik organisasi."
                    action={
                        <a
                            href={route('events.registrations.export')}
                            className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <Download className="h-4 w-4" />
                            Export CSV
                        </a>
                    }
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {registrations.length > 0 ? (
                            registrations.map((registration) => (
                                <div
                                    key={registration.id}
                                    className="grid gap-4 p-5 lg:grid-cols-[1fr_140px] lg:items-center"
                                >
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="inline-flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                                <Users className="h-4 w-4" />
                                            </span>
                                            <div>
                                                <p className="font-semibold text-[#242934]">
                                                    {registration.participantName}
                                                </p>
                                                <p className="text-sm text-[#717171]">
                                                    {registration.participantEmail}
                                                </p>
                                            </div>
                                        </div>
                                        <p className="mt-3 text-sm text-[#59667a]">
                                            {registration.projectName} ·{' '}
                                            {registration.institution} ·{' '}
                                            {registration.phone}
                                        </p>
                                        <p className="mt-1 text-xs text-[#717171]">
                                            Terdaftar {registration.registeredAt}
                                        </p>
                                    </div>
                                    <VihoStatusBadge>
                                        {humanizeStatus(registration.status)}
                                    </VihoStatusBadge>
                                </div>
                            ))
                        ) : (
                            <div className="p-5 text-sm text-[#59667a]">
                                Belum ada peserta terdaftar.
                            </div>
                        )}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-[4px] bg-[#f5f7fb] p-3">
            <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                {label}
            </p>
            <p className="mt-2 font-semibold text-[#242934]">{value}</p>
        </div>
    );
}
