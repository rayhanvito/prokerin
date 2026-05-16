import { Head, useForm } from '@inertiajs/react';
import { CalendarDays, CheckCircle2, ClipboardCheck, Mail, Users } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import type { PageProps } from '@/types';

interface EventRegistrationPageProps extends PageProps {
    event: {
        projectId: number;
        name: string;
        slug: string;
        description: string;
        organizationName: string;
        startsAt: string;
        endsAt: string;
        registrationStatus: string;
    };
    settings: {
        isOpen: boolean;
        capacity: number | null;
        registeredCount: number;
        remainingQuota: number | null;
        opensAt: string | null;
        closesAt: string | null;
        requirePayment: boolean;
    };
    ticketTiers: TicketTier[];
}

interface RegistrationFormData {
    participant_name: string;
    participant_email: string;
    phone: string;
    institution: string;
    ticket_tier_id: string;
}

interface TicketTier {
    id: number;
    name: string;
    price: number;
    capacity: number | null;
    registeredCount: number;
    remainingQuota: number | null;
}

export default function EventRegister({
    event,
    settings,
    ticketTiers,
    flash,
}: EventRegistrationPageProps) {
    const form = useForm<RegistrationFormData>({
        participant_name: '',
        participant_email: '',
        phone: '',
        institution: '',
        ticket_tier_id: '',
    });
    const canRegister =
        event.registrationStatus === 'open' &&
        (settings.remainingQuota === null || settings.remainingQuota > 0);

    const submit = (submitEvent: FormEvent<HTMLFormElement>): void => {
        submitEvent.preventDefault();

        form.post(route('events.register.store', event.slug), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <main className="min-h-screen bg-[#f5f7fb] px-4 py-10 text-[#242934] sm:px-6 lg:px-8">
            <Head title={`Registrasi ${event.name}`} />

            <div className="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1fr_420px]">
                <section className="rounded-[4px] bg-white p-6 shadow-sm ring-1 ring-[#e6edef]">
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Registrasi Event
                    </p>
                    <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#242934]">
                        {event.name}
                    </h1>
                    <p className="mt-3 text-sm font-semibold text-[#59667a]">
                        {event.organizationName}
                    </p>
                    <p className="mt-5 max-w-3xl text-sm leading-6 text-[#59667a]">
                        {event.description}
                    </p>

                    <div className="mt-8 grid gap-3 sm:grid-cols-3">
                        <InfoCard
                            icon={<CalendarDays className="h-4 w-4" />}
                            label="Tanggal"
                            value={`${event.startsAt} - ${event.endsAt}`}
                        />
                        <InfoCard
                            icon={<Users className="h-4 w-4" />}
                            label="Kuota"
                            value={
                                settings.capacity === null
                                    ? 'Tidak dibatasi'
                                    : `${settings.registeredCount}/${settings.capacity}`
                            }
                        />
                        <InfoCard
                            icon={<ClipboardCheck className="h-4 w-4" />}
                            label="Status"
                            value={statusLabel(event.registrationStatus)}
                        />
                    </div>

                    {settings.requirePayment && (
                        <div className="mt-6 rounded-[4px] bg-[rgba(186,137,93,0.1)] p-4 text-sm text-[#59667a] ring-1 ring-[rgba(186,137,93,0.25)]">
                            Event ini memiliki kategori tiket berbayar.
                            Registrasi berbayar akan berstatus pending sampai
                            pembayaran terverifikasi.
                        </div>
                    )}
                </section>

                <section className="rounded-[4px] bg-white p-6 shadow-sm ring-1 ring-[#e6edef]">
                    <div className="flex items-center gap-3">
                        <span className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] bg-[#24695c] text-white">
                            <Mail className="h-5 w-5" />
                        </span>
                        <div>
                            <h2 className="font-semibold text-[#242934]">
                                Form Registrasi
                            </h2>
                            <p className="mt-1 text-xs text-[#717171]">
                                Konfirmasi dikirim ke email peserta.
                            </p>
                        </div>
                    </div>

                    {flash.success && (
                        <div className="mt-5 flex gap-3 rounded-[4px] bg-[rgba(36,105,92,0.1)] p-4 text-sm text-[#24695c]">
                            <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
                            <span>{flash.success}</span>
                        </div>
                    )}

                    {!canRegister && (
                        <div className="mt-5 rounded-[4px] bg-[#f5f7fb] p-4 text-sm text-[#59667a] ring-1 ring-[#e6edef]">
                            Registrasi belum tersedia untuk event ini.
                        </div>
                    )}

                    <form onSubmit={submit} className="mt-6 space-y-4">
                        {ticketTiers.length > 0 && (
                            <Field label="Kategori tiket" error={form.errors.ticket_tier_id}>
                                <select
                                    value={form.data.ticket_tier_id}
                                    onChange={(inputEvent) =>
                                        form.setData('ticket_tier_id', inputEvent.target.value)
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    disabled={!canRegister}
                                >
                                    <option value="">Pilih tiket</option>
                                    {ticketTiers.map((tier) => (
                                        <option
                                            key={tier.id}
                                            value={String(tier.id)}
                                            disabled={tier.remainingQuota === 0}
                                        >
                                            {tier.name} · {formatRupiah(tier.price)} ·{' '}
                                            {tier.remainingQuota === null
                                                ? 'Kuota tidak dibatasi'
                                                : `${tier.remainingQuota} tersisa`}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                        )}
                        <Field label="Nama peserta" error={form.errors.participant_name}>
                            <TextInput
                                value={form.data.participant_name}
                                onChange={(inputEvent) =>
                                    form.setData('participant_name', inputEvent.target.value)
                                }
                                className="block w-full"
                                disabled={!canRegister}
                            />
                        </Field>
                        <Field label="Email" error={form.errors.participant_email}>
                            <TextInput
                                type="email"
                                value={form.data.participant_email}
                                onChange={(inputEvent) =>
                                    form.setData('participant_email', inputEvent.target.value)
                                }
                                className="block w-full"
                                disabled={!canRegister}
                            />
                        </Field>
                        <Field label="Telepon" error={form.errors.phone}>
                            <TextInput
                                value={form.data.phone}
                                onChange={(inputEvent) =>
                                    form.setData('phone', inputEvent.target.value)
                                }
                                className="block w-full"
                                disabled={!canRegister}
                            />
                        </Field>
                        <Field label="Institusi" error={form.errors.institution}>
                            <TextInput
                                value={form.data.institution}
                                onChange={(inputEvent) =>
                                    form.setData('institution', inputEvent.target.value)
                                }
                                className="block w-full"
                                disabled={!canRegister}
                            />
                        </Field>

                        <button
                            type="submit"
                            disabled={!canRegister || form.processing}
                            className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:bg-[#a6b7b3]"
                        >
                            Kirim Registrasi
                        </button>
                    </form>
                </section>
            </div>
        </main>
    );
}

function InfoCard({
    icon,
    label,
    value,
}: {
    icon: ReactNode;
    label: string;
    value: string;
}) {
    return (
        <div className="rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]">
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#24695c]">
                {icon}
                {label}
            </div>
            <p className="mt-3 text-sm font-semibold text-[#242934]">{value}</p>
        </div>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">{label}</span>
            <span className="mt-2 block">{children}</span>
            <InputError message={error} className="mt-2" />
        </label>
    );
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        closed: 'Ditutup',
        not_open_yet: 'Belum dibuka',
        open: 'Dibuka',
    };

    return labels[status] ?? status;
}

function formatRupiah(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        currency: 'IDR',
        maximumFractionDigits: 0,
        style: 'currency',
    }).format(amount);
}
