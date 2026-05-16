import { CheckCircle2, Circle, RotateCcw, Send } from 'lucide-react';

import ApprovalWorkflowTimeline from '@/Components/Approval/ApprovalWorkflowTimeline';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { ApprovalWorkflowTimeline as ApprovalWorkflowTimelineData } from '@/types/prokerin';
import { Head, useForm } from '@inertiajs/react';

interface LpjChecklistItem {
    title: string;
    isComplete: boolean;
    isRequired: boolean;
}

interface LpjReadiness {
    requiredItemCount: number;
    completedRequiredItemCount: number;
    completionProgress: number;
    isReadyForReview: boolean;
    missingRequiredItems: string[];
}

interface LpjChecklistProps {
    project: {
        id: number | null;
        status: string | null;
        canSubmit: boolean;
        canApprove: boolean;
    };
    checklistItems: LpjChecklistItem[];
    readiness: LpjReadiness;
    workflowTimeline: ApprovalWorkflowTimelineData;
}

export default function LpjChecklist({
    project,
    checklistItems,
    readiness,
    workflowTimeline,
}: LpjChecklistProps) {
    const reviewForm = useForm();
    const decisionForm = useForm<{ decision: 'approve' | 'request_changes' }>({
        decision: 'approve',
    });

    const submitReview = (): void => {
        if (project.id === null) {
            return;
        }

        reviewForm.post(route('reports.lpj.review', project.id), {
            preserveScroll: true,
        });
    };

    const decideLpj = (decision: 'approve' | 'request_changes'): void => {
        if (project.id === null) {
            return;
        }

        decisionForm.transform(() => ({ decision }));
        decisionForm.patch(route('reports.lpj.decision', project.id), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M10 · LPJ Generator
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        LPJ Checklist
                    </h1>
                </div>
            }
        >
            <Head title="LPJ Checklist" />

            <div className="grid gap-6 xl:grid-cols-[1fr_360px]">
                <VihoCard
                    title="Checklist Pertanggungjawaban"
                    subtitle={`${readiness.completionProgress}% lengkap · ${readiness.missingRequiredItems.length} item wajib belum lengkap.`}
                    action={
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                disabled={
                                    !project.canSubmit || reviewForm.processing
                                }
                                onClick={submitReview}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                            >
                                <Send className="h-4 w-4" />
                                Kirim Review
                            </button>
                            {project.canApprove && (
                                <>
                                    <button
                                        type="button"
                                        disabled={decisionForm.processing}
                                        onClick={() => decideLpj('approve')}
                                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                    >
                                        <CheckCircle2 className="h-4 w-4" />
                                        Approve
                                    </button>
                                    <button
                                        type="button"
                                        disabled={decisionForm.processing}
                                        onClick={() =>
                                            decideLpj('request_changes')
                                        }
                                        className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#ba895d] hover:text-[#ba895d] disabled:cursor-not-allowed disabled:bg-[#f5f7fb]"
                                    >
                                        <RotateCcw className="h-4 w-4" />
                                        Revisi
                                    </button>
                                </>
                            )}
                        </div>
                    }
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {checklistItems.map((item) => (
                            <div
                                key={item.title}
                                className="flex items-center gap-4 p-5"
                            >
                                {item.isComplete ? (
                                    <CheckCircle2 className="h-5 w-5 text-[#24695c]" />
                                ) : (
                                    <Circle className="h-5 w-5 text-[#ba895d]" />
                                )}
                                <div className="flex-1">
                                    <p className="font-semibold text-[#242934]">
                                        {item.title}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {item.isComplete
                                            ? 'Sudah tersedia untuk dokumen LPJ.'
                                            : 'Masih perlu dilengkapi oleh panitia.'}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </VihoCard>

                <VihoCard title="Status Workflow">
                    <ApprovalWorkflowTimeline timeline={workflowTimeline} />
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
