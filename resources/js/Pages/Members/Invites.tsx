import { MailPlus } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const rows = [
    {
        email: 'sekretaris@kampus.test',
        role: 'secretary',
        organization: 'BEM Fakultas',
        sent: '16 Mei',
        status: 'Pending',
    },
    {
        email: 'bendahara@kampus.test',
        role: 'treasurer',
        organization: 'HIMA Informatika',
        sent: '15 Mei',
        status: 'Accepted',
    },
    {
        email: 'lead@kampus.test',
        role: 'project_lead',
        organization: 'UKM Kreatif',
        sent: '14 Mei',
        status: 'Expired',
    },
];

export default function MemberInvites() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M03 · Invites
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Member Invites
                    </h1>
                </div>
            }
        >
            <Head title="Member Invites" />

            <VihoCard
                title="Invitation Queue"
                subtitle="UI awal invite anggota. Backend nanti mengatur token, expiry, dan accept flow."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <MailPlus className="h-4 w-4" />
                        Invite
                    </button>
                }
            >
                <VihoDataTable
                    columns={[
                        { key: 'email', label: 'Email' },
                        { key: 'role', label: 'Role' },
                        { key: 'organization', label: 'Organization' },
                        { key: 'sent', label: 'Sent' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
