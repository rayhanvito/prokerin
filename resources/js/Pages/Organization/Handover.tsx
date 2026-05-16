import { Head, router, useForm } from '@inertiajs/react';
import { Archive, CheckCircle2, ClipboardList, FileText, Landmark, Users } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';

import PrimaryButton from '@/Components/PrimaryButton';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface HandoverMetric {
    label: string;
    value: string;
    note: string;
}

interface HandoverOrganization {
    id: number;
    name: string;
    periodName: string | null;
}

interface HandoverPackage {
    id: number;
    status: string;
    createdAt: string;
    submittedAt: string | null;
    acceptedAt: string | null;
    acceptedByName: string | null;
    toPeriodId: number | null;
    toPeriodName: string | null;
    incomingOwnerId: number | null;
    incomingOwnerName: string | null;
    canAccept: boolean;
    snapshot: Record<string, unknown>;
}

interface HandoverItem {
    id: number;
    category: string;
    label: string;
    description: string | null;
    status: string;
    assignee: string | null;
}

interface OrganizationHandoverProps {
    organization: HandoverOrganization | null;
    metrics: HandoverMetric[];
    package: HandoverPackage | null;
    items: HandoverItem[];
    transitionOptions: {
        periods: { id: number; name: string }[];
        incomingOwners: { id: number; name: string }[];
    };
    canManage: boolean;
}

const categoryIcons = {
    asset: Archive,
    document: FileText,
    finance: Landmark,
    role: Users,
} as const;

export default function OrganizationHandover({
    organization,
    metrics,
    package: handoverPackage,
    items,
    transitionOptions,
    canManage,
}: OrganizationHandoverProps) {
    const { post, processing } = useForm({});
    const [updatingItemId, setUpdatingItemId] = useState<number | null>(null);
    const [updatingPackage, setUpdatingPackage] = useState(false);
    const [updatingTransition, setUpdatingTransition] = useState(false);
    const [exportingPackage, setExportingPackage] = useState(false);
    const canSubmitPackage =
        handoverPackage?.status === 'draft' &&
        items.length > 0 &&
        items.every((item) => item.status === 'done');

    const initiateHandover = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        post(route('organization.handover.store'), {
            preserveScroll: true,
        });
    };

    const updateItemStatus = (item: HandoverItem): void => {
        setUpdatingItemId(item.id);

        router.patch(
            route('organization.handover.items.update', item.id),
            {
                status: item.status === 'done' ? 'pending' : 'done',
            },
            {
                preserveScroll: true,
                onFinish: () => setUpdatingItemId(null),
            },
        );
    };

    const updatePackageStatus = (status: 'submitted' | 'accepted'): void => {
        if (handoverPackage === null) {
            return;
        }

        setUpdatingPackage(true);

        router.patch(
            route('organization.handover.packages.status', handoverPackage.id),
            { status },
            {
                preserveScroll: true,
                onFinish: () => setUpdatingPackage(false),
            },
        );
    };

    const updateTransition = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        if (handoverPackage === null) {
            return;
        }

        const form = new FormData(event.currentTarget);
        setUpdatingTransition(true);

        router.patch(
            route('organization.handover.packages.transition', handoverPackage.id),
            {
                to_period_id: form.get('to_period_id') || null,
                incoming_owner_id: form.get('incoming_owner_id') || null,
            },
            {
                preserveScroll: true,
                onFinish: () => setUpdatingTransition(false),
            },
        );
    };

    const queueHandoverExport = (): void => {
        if (handoverPackage === null) {
            return;
        }

        setExportingPackage(true);

        router.post(
            route('organization.handover.packages.export', handoverPackage.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => setExportingPackage(false),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M19 · Post-MVP
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Handover Readiness
                    </h1>
                </div>
            }
        >
            <Head title="Handover Readiness" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="text-xl font-semibold text-[#242934]">
                                Snapshot kesiapan serah terima
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                {organization
                                    ? `${organization.name} · Periode ${organization.periodName ?? 'belum aktif'}`
                                    : 'Belum ada organisasi aktif untuk user ini.'}
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-3">
                            <VihoStatusBadge tone={handoverPackage === null ? 'muted' : 'success'}>
                                {handoverPackage?.status ?? 'not started'}
                            </VihoStatusBadge>
                            {handoverPackage === null && canManage ? (
                                <form onSubmit={initiateHandover}>
                                    <PrimaryButton disabled={processing}>
                                        Buat Paket Handover
                                    </PrimaryButton>
                                </form>
                            ) : null}
                            {handoverPackage?.status === 'draft' && canManage ? (
                                <PrimaryButton
                                    type="button"
                                    disabled={!canSubmitPackage || updatingPackage}
                                    onClick={() => updatePackageStatus('submitted')}
                                >
                                    Submit Paket
                                </PrimaryButton>
                            ) : null}
                            {handoverPackage?.status === 'submitted' && handoverPackage.canAccept ? (
                                <PrimaryButton
                                    type="button"
                                    disabled={updatingPackage}
                                    onClick={() => updatePackageStatus('accepted')}
                                >
                                    Terima Paket
                                </PrimaryButton>
                            ) : null}
                            {handoverPackage?.status === 'accepted' && canManage ? (
                                <PrimaryButton
                                    type="button"
                                    disabled={exportingPackage}
                                    onClick={queueHandoverExport}
                                >
                                    Export PDF
                                </PrimaryButton>
                            ) : null}
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {metrics.map((metric) => (
                        <VihoCard key={metric.label}>
                            <p className="text-sm font-semibold text-[#59667a]">
                                {metric.label}
                            </p>
                            <p className="mt-3 text-3xl font-semibold text-[#242934]">
                                {metric.value}
                            </p>
                            <p className="mt-2 text-sm leading-6 text-[#717171]">
                                {metric.note}
                            </p>
                        </VihoCard>
                    ))}
                </section>

                <div className="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <VihoCard
                        title="Checklist Handover"
                        subtitle="Item awal dibuat otomatis dari snapshot proker, finance, dokumen, dan LPJ."
                    >
                        {items.length > 0 ? (
                            <div className="-m-5 divide-y divide-[#e6edef]">
                                {items.map((item) => {
                                    const Icon =
                                        categoryIcons[item.category as keyof typeof categoryIcons] ??
                                        ClipboardList;

                                    return (
                                        <div key={item.id} className="p-5">
                                            <div className="flex items-start gap-3">
                                                <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                                    <Icon className="h-5 w-5" />
                                                </span>
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                                        <p className="font-semibold text-[#242934]">
                                                            {item.label}
                                                        </p>
                                                        <VihoStatusBadge tone={item.status === 'done' ? 'success' : 'muted'}>
                                                            {item.status}
                                                        </VihoStatusBadge>
                                                    </div>
                                                    <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                                        {item.description ?? 'Belum ada deskripsi.'}
                                                    </p>
                                                    <p className="mt-3 text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                                        {item.assignee ?? 'Belum ada assignee'}
                                                    </p>
                                                    {canManage && handoverPackage?.status === 'draft' ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => updateItemStatus(item)}
                                                            disabled={updatingItemId === item.id}
                                                            className="mt-4 inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] px-3 py-2 text-xs font-semibold text-[#242934] transition hover:border-[#24695c] hover:text-[#24695c] disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                                            {item.status === 'done'
                                                                ? 'Kembalikan pending'
                                                                : 'Tandai selesai'}
                                                        </button>
                                                    ) : null}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="rounded-[4px] bg-[#f5f7fb] p-5 text-sm leading-6 text-[#59667a] ring-1 ring-[#e6edef]">
                                Paket handover belum dibuat. Owner/admin bisa membuat
                                paket awal dari snapshot data saat ini.
                            </div>
                        )}
                    </VihoCard>

                    <VihoCard title="Snapshot Paket" subtitle="Data dibekukan saat paket dibuat.">
                        {handoverPackage ? (
                            <div className="space-y-4">
                                <SnapshotLine
                                    label="Paket dibuat"
                                    value={handoverPackage.createdAt}
                                />
                                <SnapshotLine
                                    label="Periode penerima"
                                    value={handoverPackage.toPeriodName ?? 'Belum ditentukan'}
                                />
                                <SnapshotLine
                                    label="Incoming owner"
                                    value={handoverPackage.incomingOwnerName ?? 'Belum ditentukan'}
                                />
                                {handoverPackage.acceptedByName ? (
                                    <SnapshotLine
                                        label="Diterima oleh"
                                        value={handoverPackage.acceptedByName}
                                    />
                                ) : null}
                                <SnapshotLine
                                    label="Budget rencana"
                                    value={formatRupiah(handoverPackage.snapshot.planned_budget)}
                                />
                                <SnapshotLine
                                    label="Budget realisasi"
                                    value={formatRupiah(handoverPackage.snapshot.realized_budget)}
                                />
                                <SnapshotLine
                                    label="LPJ wajib belum lengkap"
                                    value={String(handoverPackage.snapshot.outstanding_lpj_items ?? 0)}
                                />
                                {canManage && handoverPackage.status !== 'accepted' ? (
                                    <form
                                        onSubmit={updateTransition}
                                        className="space-y-3 rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]"
                                    >
                                        <label className="block text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                            Periode penerima
                                            <select
                                                name="to_period_id"
                                                defaultValue={handoverPackage.toPeriodId ?? ''}
                                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm font-medium text-[#242934] focus:border-[#24695c] focus:ring-[#24695c]"
                                            >
                                                <option value="">Belum ditentukan</option>
                                                {transitionOptions.periods.map((period) => (
                                                    <option key={period.id} value={period.id}>
                                                        {period.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>
                                        <label className="block text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                            Incoming owner
                                            <select
                                                name="incoming_owner_id"
                                                defaultValue={handoverPackage.incomingOwnerId ?? ''}
                                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm font-medium text-[#242934] focus:border-[#24695c] focus:ring-[#24695c]"
                                            >
                                                <option value="">Fallback owner/admin</option>
                                                {transitionOptions.incomingOwners.map((owner) => (
                                                    <option key={owner.id} value={owner.id}>
                                                        {owner.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>
                                        <PrimaryButton type="submit" disabled={updatingTransition}>
                                            Simpan Penerima
                                        </PrimaryButton>
                                    </form>
                                ) : null}
                            </div>
                        ) : (
                            <div className="flex items-start gap-3 rounded-[4px] bg-[#f5f7fb] p-5 ring-1 ring-[#e6edef]">
                                <CheckCircle2 className="mt-0.5 h-5 w-5 text-[#24695c]" />
                                <p className="text-sm leading-6 text-[#59667a]">
                                    Snapshot live sudah terlihat di metric. Klik buat
                                    paket untuk membekukan data sebagai arsip handover.
                                </p>
                            </div>
                        )}
                    </VihoCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function SnapshotLine({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]">
            <p className="text-sm font-semibold text-[#59667a]">{label}</p>
            <p className="text-sm font-semibold text-[#242934]">{value}</p>
        </div>
    );
}

function formatRupiah(value: unknown): string {
    const amount = typeof value === 'number' ? value : Number(value ?? 0);

    return `Rp${amount.toLocaleString('id-ID')}`;
}
