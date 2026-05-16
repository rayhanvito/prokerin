import { CheckCircle2, XCircle } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

interface ApprovalItem {
    id: number;
    title: string;
    projectName: string;
    category: string;
    amount: number;
    requester: string;
    status: string;
    canDecide: boolean;
}

interface FinanceApprovalProps {
    approvals: ApprovalItem[];
}

function ApprovalRow({ approval }: { approval: ApprovalItem }) {
    const approveForm = useForm({
        decision: 'approve',
    });
    const rejectForm = useForm({
        decision: 'reject',
    });

    const decide = (decision: 'approve' | 'reject'): void => {
        const form = decision === 'approve' ? approveForm : rejectForm;

        form.patch(route('finance.approvals.update', approval.id), {
            preserveScroll: true,
        });
    };

    const processing = approveForm.processing || rejectForm.processing;

    return (
        <div className="flex flex-col gap-4 p-5 lg:flex-row lg:items-center">
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                    <p className="font-semibold text-[#242934]">
                        {approval.title}
                    </p>
                    <VihoStatusBadge>{humanizeStatus(approval.status)}</VihoStatusBadge>
                </div>
                <p className="mt-1 text-sm text-[#717171]">
                    {formatRupiah(approval.amount)} · {approval.projectName} ·{' '}
                    {approval.category} · Requested by {approval.requester}
                </p>
            </div>
            <div className="flex gap-2">
                <button
                    type="button"
                    disabled={!approval.canDecide || processing}
                    onClick={() => decide('reject')}
                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#d22d3d] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <XCircle className="h-4 w-4" />
                    Reject
                </button>
                <button
                    type="button"
                    disabled={!approval.canDecide || processing}
                    onClick={() => decide('approve')}
                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <CheckCircle2 className="h-4 w-4" />
                    Approve
                </button>
            </div>
        </div>
    );
}

export default function FinanceApproval({ approvals }: FinanceApprovalProps) {
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
                subtitle="Review RAB yang menunggu keputusan bendahara atau admin organisasi."
            >
                <div className="-m-5 divide-y divide-[#e6edef]">
                    {approvals.map((approval) => (
                        <ApprovalRow key={approval.id} approval={approval} />
                    ))}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
