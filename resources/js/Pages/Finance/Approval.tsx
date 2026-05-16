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
    workflowApprovals: WorkflowApprovalItem[];
    delegateOptions: { id: number; name: string }[];
}

interface WorkflowApprovalItem {
    id: number;
    workflowType: string;
    subject: string;
    status: string;
    currentStep: number;
    submittedBy: string;
    canDecide: boolean;
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

function WorkflowApprovalRow({
    approval,
    delegateOptions,
}: {
    approval: WorkflowApprovalItem;
    delegateOptions: { id: number; name: string }[];
}) {
    const decisionForm = useForm<{ decision: string }>({ decision: 'approved' });
    const delegateForm = useForm<{ delegate_user_id: number | ''; note: string }>({
        delegate_user_id: delegateOptions[0]?.id ?? '',
        note: 'Delegasi review approval.',
    });

    const decide = (decision: 'approved' | 'rejected' | 'revision_requested'): void => {
        decisionForm.transform(() => ({ decision }));
        decisionForm.patch(route('approval-workflows.decision', approval.id), {
            preserveScroll: true,
        });
    };

    const delegate = (): void => {
        delegateForm.patch(route('approval-workflows.delegate', approval.id), {
            preserveScroll: true,
        });
    };

    const processing = decisionForm.processing || delegateForm.processing;

    return (
        <div className="space-y-4 p-5">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="font-semibold text-[#242934]">
                            {humanizeStatus(approval.workflowType)} · step {approval.currentStep}
                        </p>
                        <VihoStatusBadge>{humanizeStatus(approval.status)}</VihoStatusBadge>
                    </div>
                    <p className="mt-1 text-sm text-[#717171]">
                        {approval.subject} · Submitted by {approval.submittedBy}
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <button
                        type="button"
                        disabled={!approval.canDecide || processing}
                        onClick={() => decide('revision_requested')}
                        className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#242934] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Revision
                    </button>
                    <button
                        type="button"
                        disabled={!approval.canDecide || processing}
                        onClick={() => decide('rejected')}
                        className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#d22d3d] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Reject
                    </button>
                    <button
                        type="button"
                        disabled={!approval.canDecide || processing}
                        onClick={() => decide('approved')}
                        className="rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Approve
                    </button>
                </div>
            </div>

            {delegateOptions.length > 0 ? (
                <div className="flex flex-col gap-2 rounded-[4px] bg-[#f5f7fb] p-3 ring-1 ring-[#e6edef] sm:flex-row sm:items-center">
                    <select
                        value={delegateForm.data.delegate_user_id}
                        onChange={(event) =>
                            delegateForm.setData('delegate_user_id', Number(event.target.value))
                        }
                        className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] focus:border-[#24695c] focus:ring-[#24695c]"
                    >
                        {delegateOptions.map((user) => (
                            <option key={user.id} value={user.id}>
                                {user.name}
                            </option>
                        ))}
                    </select>
                    <button
                        type="button"
                        disabled={processing}
                        onClick={delegate}
                        className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#242934] transition hover:border-[#24695c] hover:text-[#24695c]"
                    >
                        Delegate
                    </button>
                </div>
            ) : null}
        </div>
    );
}

export default function FinanceApproval({
    approvals,
    workflowApprovals,
    delegateOptions,
}: FinanceApprovalProps) {
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

            <div className="mt-6">
                <VihoCard
                    title="Multi-Level Workflow"
                    subtitle="Step approval aktif untuk user saat ini, termasuk delegasi reviewer."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {workflowApprovals.length > 0 ? (
                            workflowApprovals.map((approval) => (
                                <WorkflowApprovalRow
                                    key={approval.id}
                                    approval={approval}
                                    delegateOptions={delegateOptions}
                                />
                            ))
                        ) : (
                            <div className="p-5 text-sm text-[#59667a]">
                                Tidak ada step workflow aktif untuk user ini.
                            </div>
                        )}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
