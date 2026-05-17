import { Building2, CheckCircle2, Plus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

import EmptyState from '@/Components/ui/EmptyState';
import FormField from '@/Components/ui/FormField';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface OrganizationItem {
    id: number;
    name: string;
    role: string;
    period: string;
    memberCount: number;
    active: boolean;
}

interface OrganizationSwitcherProps {
    activeOrganizationId: number | null;
    organizations: OrganizationItem[];
}

interface OrganizationFormData {
    name: string;
    slug: string;
    plan_tier: 'free';
}

export default function OrganizationSwitcher({
    organizations,
}: OrganizationSwitcherProps) {
    const [showCreateForm, setShowCreateForm] = useState(
        organizations.length === 0,
    );
    const form = useForm<OrganizationFormData>({
        name: '',
        slug: '',
        plan_tier: 'free',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        form.post(route('organization.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setShowCreateForm(false);
            },
        });
    };

    function switchOrganization(organizationId: number): void {
        router.post(
            route('organization.switch'),
            { organization_id: organizationId },
            { preserveScroll: true },
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M02 · Organization
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Organization Switcher
                    </h1>
                </div>
            }
        >
            <Head title="Organization Switcher" />

            <VihoCard
                title="Pilih Workspace Organisasi"
                subtitle="UI awal untuk user yang punya role berbeda di beberapa organisasi."
                action={
                    <button
                        type="button"
                        onClick={() =>
                            setShowCreateForm((current) => !current)
                        }
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <Plus className="h-4 w-4" />
                        Buat Organisasi
                    </button>
                }
            >
                <div className="space-y-5">
                    {showCreateForm ? (
                        <form
                            onSubmit={submit}
                            className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-5"
                        >
                            <div className="grid gap-4 md:grid-cols-2">
                                <FormField
                                    label="Nama Organisasi"
                                    htmlFor="organization-name"
                                    required
                                    error={form.errors.name}
                                >
                                    <input
                                        id="organization-name"
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
                                    label="Slug"
                                    htmlFor="organization-slug"
                                    error={form.errors.slug}
                                    hint="Kosongkan untuk dibuat otomatis."
                                >
                                    <input
                                        id="organization-slug"
                                        type="text"
                                        value={form.data.slug}
                                        onChange={(event) =>
                                            form.setData(
                                                'slug',
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                    />
                                </FormField>
                            </div>
                            <div className="mt-5 flex justify-end gap-3">
                                {organizations.length > 0 ? (
                                    <button
                                        type="button"
                                        onClick={() => setShowCreateForm(false)}
                                        className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                                    >
                                        Batal
                                    </button>
                                ) : null}
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                >
                                    Simpan Organisasi
                                </button>
                            </div>
                        </form>
                    ) : null}

                    {organizations.length === 0 ? (
                        <EmptyState
                            icon={Building2}
                            title="Belum ada organisasi"
                            description="Buat organisasi pertama untuk mulai mengelola proker, anggota, dokumen, dan laporan."
                            action={{
                                label: 'Buat Organisasi Pertama',
                                onClick: () => setShowCreateForm(true),
                            }}
                        />
                    ) : (
                        <div className="-mx-5 -mb-5 divide-y divide-[#e6edef]">
                            {organizations.map((organization) => (
                                <button
                                    type="button"
                                    key={organization.id}
                                    onClick={() =>
                                        switchOrganization(organization.id)
                                    }
                                    disabled={organization.active}
                                    className="flex w-full flex-col gap-4 p-5 text-left transition hover:bg-[#f8fafb] disabled:cursor-default disabled:bg-white lg:flex-row lg:items-center"
                                >
                                    <span className="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                        <Building2 className="h-6 w-6" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-semibold text-[#242934]">
                                                {organization.name}
                                            </p>
                                            {organization.active && (
                                                <CheckCircle2 className="h-4 w-4 text-[#24695c]" />
                                            )}
                                        </div>
                                        <p className="mt-1 text-sm text-[#717171]">
                                            {organization.role} · Periode{' '}
                                            {organization.period} ·{' '}
                                            {organization.memberCount} anggota
                                        </p>
                                    </div>
                                    <VihoStatusBadge
                                        tone={
                                            organization.active
                                                ? 'success'
                                                : 'muted'
                                        }
                                    >
                                        {organization.active
                                            ? 'Current'
                                            : 'Available'}
                                    </VihoStatusBadge>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
