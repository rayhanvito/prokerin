import { Database, Gauge, Shield, Wrench } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const resources = [
    {
        title: 'Organizations',
        description: 'Internal overview untuk tenant organisasi.',
        icon: Database,
        status: 'Filament later',
    },
    {
        title: 'System Health',
        description: 'Queue, storage, email, dan export worker status.',
        icon: Gauge,
        status: 'Planned',
    },
    {
        title: 'Access Audit',
        description: 'Audit role, policy, dan permission assignment.',
        icon: Shield,
        status: 'Planned',
    },
];

const rows = [
    {
        resource: 'OrganizationResource',
        owner: 'Internal Admin',
        purpose: 'Tenant monitoring',
        package: 'Filament',
        status: 'Pending',
    },
    {
        resource: 'UserResource',
        owner: 'Internal Admin',
        purpose: 'Account support',
        package: 'Filament',
        status: 'Pending',
    },
    {
        resource: 'ExportJobResource',
        owner: 'Ops',
        purpose: 'PDF/DOCX queue monitoring',
        package: 'Filament',
        status: 'Pending',
    },
];

export default function AdminIndex() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M13 · Admin Internal
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Internal Admin
                    </h1>
                </div>
            }
        >
            <Head title="Internal Admin" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <Wrench className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Internal admin readiness
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Halaman ini bukan pengganti Filament. Ini
                                    adalah permukaan perencanaan internal sampai
                                    package Filament dipasang dan resource
                                    dibuat.
                                </p>
                            </div>
                        </div>
                        <VihoStatusBadge tone="muted">
                            Scheduled
                        </VihoStatusBadge>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {resources.map((resource) => {
                        const Icon = resource.icon;

                        return (
                            <VihoCard key={resource.title}>
                                <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-6 w-6" />
                                </span>
                                <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                                    {resource.title}
                                </h2>
                                <p className="mt-2 min-h-12 text-sm leading-6 text-[#59667a]">
                                    {resource.description}
                                </p>
                                <div className="mt-5">
                                    <VihoStatusBadge>
                                        {resource.status}
                                    </VihoStatusBadge>
                                </div>
                            </VihoCard>
                        );
                    })}
                </section>

                <VihoCard
                    title="Planned Filament Resources"
                    subtitle="Resource list awal untuk admin panel internal."
                >
                    <VihoDataTable
                        columns={[
                            { key: 'resource', label: 'Resource' },
                            { key: 'owner', label: 'Owner' },
                            { key: 'purpose', label: 'Purpose' },
                            { key: 'package', label: 'Package' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={rows}
                        statusKey="status"
                    />
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
