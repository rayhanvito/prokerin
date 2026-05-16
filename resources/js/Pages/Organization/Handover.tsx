import { Archive, CheckCircle2, FileText, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const readiness = [
    {
        title: 'Dokumen proker lengkap',
        description: 'Proposal, RAB, LPJ, dan dokumentasi akhir.',
        icon: FileText,
        status: '72%',
    },
    {
        title: 'Role pengurus terdokumentasi',
        description: 'Struktur role dan PIC per proyek siap diwariskan.',
        icon: Users,
        status: '64%',
    },
    {
        title: 'Archive package',
        description: 'Bundel arsip akhir periode untuk pengurus berikutnya.',
        icon: Archive,
        status: 'Planned',
    },
];

export default function OrganizationHandover() {
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
                                Area ini memastikan data yang dibangun di MVP
                                sudah mendukung handover kepengurusan nanti.
                            </p>
                        </div>
                        <VihoStatusBadge tone="muted">Post-MVP</VihoStatusBadge>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {readiness.map((item) => {
                        const Icon = item.icon;

                        return (
                            <VihoCard key={item.title}>
                                <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-6 w-6" />
                                </span>
                                <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                                    {item.title}
                                </h2>
                                <p className="mt-2 min-h-12 text-sm leading-6 text-[#59667a]">
                                    {item.description}
                                </p>
                                <div className="mt-5 flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-[#24695c]" />
                                    <span className="text-sm font-semibold text-[#24695c]">
                                        {item.status}
                                    </span>
                                </div>
                            </VihoCard>
                        );
                    })}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
