import { CheckCircle2, XCircle } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const approvals = [
    {
        title: 'RAB konsumsi Seminar Karier',
        amount: 'Rp6.500.000',
        requester: 'Nadia Putri',
    },
    {
        title: 'Venue Workshop UI/UX',
        amount: 'Rp8.250.000',
        requester: 'Dimas Aji',
    },
    {
        title: 'Publikasi Makrab',
        amount: 'Rp1.750.000',
        requester: 'Raka Pratama',
    },
];

export default function FinanceApproval() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M07 · Approval
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Finance Approval
                    </h1>
                </div>
            }
        >
            <Head title="Finance Approval" />

            <VihoCard
                title="Approval Queue"
                subtitle="UI antrian approval bendahara sebelum mutation backend dibuat."
            >
                <div className="-m-5 divide-y divide-[#e6edef]">
                    {approvals.map((approval) => (
                        <div
                            key={approval.title}
                            className="flex flex-col gap-4 p-5 lg:flex-row lg:items-center"
                        >
                            <div className="min-w-0 flex-1">
                                <p className="font-semibold text-[#242934]">
                                    {approval.title}
                                </p>
                                <p className="mt-1 text-sm text-[#717171]">
                                    {approval.amount} · Requested by{' '}
                                    {approval.requester}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#d22d3d]"
                                >
                                    <XCircle className="h-4 w-4" />
                                    Reject
                                </button>
                                <button
                                    type="button"
                                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white"
                                >
                                    <CheckCircle2 className="h-4 w-4" />
                                    Approve
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
