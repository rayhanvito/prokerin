import { Database, Gauge, Shield, Wrench } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

interface AdminCard {
    title: string;
    description: string;
    status: string;
}

interface AdminResource {
    resource: string;
    owner: string;
    purpose: string;
    package: string;
    status: string;
}

interface SystemHealth {
    queuedExports: number;
    failedExports: number;
    pendingNotifications: number;
    filamentInstalled: boolean;
}

interface AdminIndexProps {
    cards: AdminCard[];
    resources: AdminResource[];
    systemHealth: SystemHealth;
}

const cardIcons = [Database, Gauge, Shield];

export default function AdminIndex({
    cards,
    resources,
    systemHealth,
}: AdminIndexProps) {
    const healthSummary = `${systemHealth.queuedExports} queued export · ${systemHealth.failedExports} failed · ${systemHealth.pendingNotifications} unread notification`;
    const rows = resources.map((resource) => ({
        owner: resource.owner,
        package: resource.package,
        purpose: resource.purpose,
        resource: resource.resource,
        status: resource.status,
    }));

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
                                    Halaman ini membaca readiness backend M13.
                                    Filament:{' '}
                                    {systemHealth.filamentInstalled
                                        ? 'installed'
                                        : 'pending package'}
                                    . {healthSummary}.
                                </p>
                            </div>
                        </div>
                        <VihoStatusBadge tone="muted">
                            Scheduled
                        </VihoStatusBadge>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {cards.map((resource, index) => {
                        const Icon = cardIcons[index] ?? Database;

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
