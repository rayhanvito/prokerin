import { CheckCircle2, CircleDashed, FileClock, FileSearch, Rocket } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const statuses = [
    {
        title: 'Draft',
        description: 'Pengurus membuat data awal proker dan memilih template.',
        icon: CircleDashed,
    },
    {
        title: 'Proposal Review',
        description: 'Sekretaris dan admin organisasi meninjau proposal.',
        icon: FileSearch,
    },
    {
        title: 'RAB Approval',
        description: 'Bendahara memeriksa anggaran sebelum eksekusi.',
        icon: FileClock,
    },
    {
        title: 'Execution',
        description: 'Task, timeline, dokumentasi, dan realisasi berjalan.',
        icon: Rocket,
    },
    {
        title: 'Closed',
        description: 'LPJ final, arsip dokumen, dan handover selesai.',
        icon: CheckCircle2,
    },
];

export default function ProkerStatusFlow() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Status Flow
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Proker Status Flow
                    </h1>
                </div>
            }
        >
            <Head title="Proker Status Flow" />

            <VihoCard
                title="Lifecycle proker"
                subtitle="Flow awal untuk menjaga proposal, finance, execution, dan LPJ tetap berurutan."
            >
                <div className="grid gap-4 lg:grid-cols-5">
                    {statuses.map((status, index) => {
                        const Icon = status.icon;

                        return (
                            <div
                                key={status.title}
                                className="relative rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4"
                            >
                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-white text-[#24695c] shadow-sm">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <p className="mt-4 text-xs font-semibold text-[#ba895d]">
                                    Step {index + 1}
                                </p>
                                <h2 className="mt-1 font-semibold text-[#242934]">
                                    {status.title}
                                </h2>
                                <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                    {status.description}
                                </p>
                            </div>
                        );
                    })}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
