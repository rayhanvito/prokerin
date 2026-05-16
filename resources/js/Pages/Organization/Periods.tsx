import { CalendarRange, Plus } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const rows = [
    {
        period: '2026',
        start: 'Jan 2026',
        end: 'Dec 2026',
        owner: 'BEM Fakultas',
        status: 'Active',
    },
    {
        period: '2025',
        start: 'Jan 2025',
        end: 'Dec 2025',
        owner: 'BEM Fakultas',
        status: 'Archived',
    },
    {
        period: '2025/2026',
        start: 'Aug 2025',
        end: 'Jul 2026',
        owner: 'UKM Kreatif',
        status: 'Active',
    },
];

export default function OrganizationPeriods() {
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
                subtitle="Struktur periode dipakai untuk scope proker, member role, dan handover."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <Plus className="h-4 w-4" />
                        Tambah Periode
                    </button>
                }
            >
                <div className="mb-5 rounded-[4px] bg-[#f5f7fb] p-4">
                    <div className="flex gap-3">
                        <CalendarRange className="h-5 w-5 text-[#24695c]" />
                        <p className="text-sm leading-6 text-[#59667a]">
                            Nanti setiap query data organisasi harus terscope
                            `organization_id` dan, bila relevan, `period_id`.
                        </p>
                    </div>
                </div>
                <VihoDataTable
                    columns={[
                        { key: 'period', label: 'Period' },
                        { key: 'start', label: 'Start' },
                        { key: 'end', label: 'End' },
                        { key: 'owner', label: 'Organization' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
