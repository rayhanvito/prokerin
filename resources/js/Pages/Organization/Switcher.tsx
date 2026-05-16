import { Building2, CheckCircle2, Plus } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const organizations = [
    {
        name: 'BEM Fakultas Teknologi',
        role: 'organization_owner',
        period: '2026',
        active: true,
    },
    {
        name: 'HIMA Informatika',
        role: 'organization_admin',
        period: '2026',
        active: false,
    },
    {
        name: 'UKM Kreatif',
        role: 'viewer',
        period: '2025/2026',
        active: false,
    },
];

export default function OrganizationSwitcher() {
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
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <Plus className="h-4 w-4" />
                        Buat Organisasi
                    </button>
                }
            >
                <div className="-m-5 divide-y divide-[#e6edef]">
                    {organizations.map((organization) => (
                        <div
                            key={organization.name}
                            className="flex flex-col gap-4 p-5 lg:flex-row lg:items-center"
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
                                    {organization.period}
                                </p>
                            </div>
                            <VihoStatusBadge
                                tone={organization.active ? 'success' : 'muted'}
                            >
                                {organization.active ? 'Current' : 'Available'}
                            </VihoStatusBadge>
                        </div>
                    ))}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
