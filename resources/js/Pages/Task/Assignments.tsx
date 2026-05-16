import { UserCheck } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const rows = [
    {
        member: 'Nadia Putri',
        role: 'Secretary',
        project: 'Seminar Karier',
        task: 'Proposal review',
        status: 'Active',
    },
    {
        member: 'Raka Pratama',
        role: 'Treasurer',
        project: 'Workshop UI/UX',
        task: 'RAB approval',
        status: 'Review',
    },
    {
        member: 'Dimas Aji',
        role: 'Project Lead',
        project: 'Makrab 2026',
        task: 'Timeline setup',
        status: 'Draft',
    },
];

export default function TaskAssignments() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M06 · PIC Assignment
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        PIC Assignment
                    </h1>
                </div>
            }
        >
            <Head title="PIC Assignment" />

            <VihoCard
                title="Assignment Matrix"
                subtitle="Tabel awal untuk relasi member, role, project, dan task."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <UserCheck className="h-4 w-4" />
                        Assign PIC
                    </button>
                }
            >
                <VihoDataTable
                    columns={[
                        { key: 'member', label: 'Member' },
                        { key: 'role', label: 'Role' },
                        { key: 'project', label: 'Project' },
                        { key: 'task', label: 'Task' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
