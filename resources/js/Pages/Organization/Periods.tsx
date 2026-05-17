import { CalendarRange, Plus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

import EmptyState from '@/Components/ui/EmptyState';
import FormField from '@/Components/ui/FormField';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface OrganizationPeriodRow {
    id: number;
    period: string;
    start: string;
    end: string;
    owner: string;
    status: string;
}

interface OrganizationPeriodsProps {
    canManage: boolean;
    organization: {
        id: number;
        name: string;
    } | null;
    periods: OrganizationPeriodRow[];
}

interface PeriodFormData {
    name: string;
    starts_at: string;
    ends_at: string;
    is_active: boolean;
}

export default function OrganizationPeriods({
    canManage,
    organization,
    periods,
}: OrganizationPeriodsProps) {
    const [showForm, setShowForm] = useState(false);
    const form = useForm<PeriodFormData>({
        name: '',
        starts_at: '',
        ends_at: '',
        is_active: true,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        form.post(route('organization.periods.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setShowForm(false);
            },
        });
    };

    function setActive(period: OrganizationPeriodRow): void {
        router.patch(
            route('organization.periods.update', { period: period.id }),
            {
                name: period.period,
                starts_at: period.start,
                ends_at: period.end,
                is_active: true,
            },
            { preserveScroll: true },
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M02 · Period
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Organization Periods
                    </h1>
                </div>
            }
        >
            <Head title="Organization Periods" />

            <VihoCard
                title="Periode Kepengurusan"
                subtitle={
                    organization
                        ? `Struktur periode aktif untuk ${organization.name}.`
                        : 'Struktur periode dipakai untuk scope proker, member role, dan handover.'
                }
                action={
                    canManage ? (
                        <button
                            type="button"
                            onClick={() => setShowForm((current) => !current)}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <Plus className="h-4 w-4" />
                            Tambah Periode
                        </button>
                    ) : null
                }
            >
                {showForm ? (
                    <form
                        onSubmit={submit}
                        className="mb-5 rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-5"
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField
                                label="Nama Periode"
                                htmlFor="period-name"
                                required
                                error={form.errors.name}
                            >
                                <input
                                    id="period-name"
                                    type="text"
                                    value={form.data.name}
                                    onChange={(event) =>
                                        form.setData(
                                            'name',
                                            event.target.value,
                                        )
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </FormField>
                            <FormField
                                label="Mulai"
                                htmlFor="period-start"
                                required
                                error={form.errors.starts_at}
                            >
                                <input
                                    id="period-start"
                                    type="date"
                                    value={form.data.starts_at}
                                    onChange={(event) =>
                                        form.setData(
                                            'starts_at',
                                            event.target.value,
                                        )
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </FormField>
                            <FormField
                                label="Selesai"
                                htmlFor="period-end"
                                required
                                error={form.errors.ends_at}
                            >
                                <input
                                    id="period-end"
                                    type="date"
                                    value={form.data.ends_at}
                                    onChange={(event) =>
                                        form.setData(
                                            'ends_at',
                                            event.target.value,
                                        )
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </FormField>
                        </div>
                        <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <label className="inline-flex items-center gap-2 text-sm font-medium text-[#59667a]">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_active}
                                    onChange={(event) =>
                                        form.setData(
                                            'is_active',
                                            event.target.checked,
                                        )
                                    }
                                    className="rounded border-[#e6edef] text-[#24695c] focus:ring-[#24695c]"
                                />
                                Jadikan periode aktif
                            </label>
                            <div className="flex justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setShowForm(false)}
                                    className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                >
                                    Simpan Periode
                                </button>
                            </div>
                        </div>
                    </form>
                ) : null}

                <div className="mb-5 rounded-[4px] bg-[#f5f7fb] p-4">
                    <div className="flex gap-3">
                        <CalendarRange className="h-5 w-5 text-[#24695c]" />
                        <p className="text-sm leading-6 text-[#59667a]">
                            Nanti setiap query data organisasi harus terscope
                            `organization_id` dan, bila relevan, `period_id`.
                        </p>
                    </div>
                </div>
                {periods.length === 0 ? (
                    <EmptyState
                        icon={CalendarRange}
                        title="Belum ada periode"
                        description="Tambahkan periode kepengurusan agar proker, member role, dan handover punya scope waktu yang jelas."
                        action={
                            canManage
                                ? {
                                      label: 'Tambah Periode',
                                      onClick: () => setShowForm(true),
                                  }
                                : undefined
                        }
                    />
                ) : (
                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full border-collapse text-sm">
                            <thead>
                                <tr className="border-b border-[#e6edef] bg-[#f5f7fb] text-left text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                                    <th className="px-5 py-3">Period</th>
                                    <th className="px-5 py-3">Start</th>
                                    <th className="px-5 py-3">End</th>
                                    <th className="px-5 py-3">Organization</th>
                                    <th className="px-5 py-3">Status</th>
                                    {canManage ? (
                                        <th className="px-5 py-3 text-right">
                                            Action
                                        </th>
                                    ) : null}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#e6edef] bg-white">
                                {periods.map((period) => (
                                    <tr key={period.id}>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {period.period}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {period.start}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {period.end}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {period.owner}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4">
                                            <VihoStatusBadge
                                                tone={
                                                    period.status === 'Active'
                                                        ? 'success'
                                                        : 'muted'
                                                }
                                            >
                                                {period.status}
                                            </VihoStatusBadge>
                                        </td>
                                        {canManage ? (
                                            <td className="whitespace-nowrap px-5 py-4 text-right">
                                                {period.status === 'Active' ? (
                                                    <span className="text-xs font-semibold text-[#717171]">
                                                        Current
                                                    </span>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setActive(period)
                                                        }
                                                        className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#24695c] transition hover:bg-[#f5f7fb]"
                                                    >
                                                        Set Active
                                                    </button>
                                                )}
                                            </td>
                                        ) : null}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </VihoCard>
        </AuthenticatedLayout>
    );
}
