import { CheckCircle2, Circle, RotateCcw, Send, WandSparkles } from 'lucide-react';

import ApprovalWorkflowTimeline from '@/Components/Approval/ApprovalWorkflowTimeline';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps } from '@/types';
import type { ApprovalWorkflowTimeline as ApprovalWorkflowTimelineData } from '@/types/prokerin';
import { Head, useForm, usePage } from '@inertiajs/react';

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

interface LpjAiSuggestion {
    type: 'lpj_summary';
    projectId: number;
    summary: string;
    recommendations: string[];
    promptHash: string;
}

export default function LpjChecklist({
    project,
    checklistItems,
    readiness,
    workflowTimeline,
}: LpjChecklistProps) {
    const { flash } = usePage<PageProps>().props;
    const aiSuggestion = resolveLpjAiSuggestion(flash.aiSuggestion, project.id);
    const aiForm = useForm();
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

    const generateAiSummary = (): void => {
        if (project.id === null) {
            return;
        }

        aiForm.post(route('reports.lpj.ai-summary', project.id), {
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
                                disabled={project.id === null || aiForm.processing}
                                onClick={generateAiSummary}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#ba895d] hover:text-[#ba895d] disabled:cursor-not-allowed disabled:bg-[#f5f7fb]"
                            >
                                <WandSparkles className="h-4 w-4" />
                                Ringkas AI
                            </button>
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
                        {aiSuggestion && (
                            <div className="bg-[#f5f7fb] p-5">
                                <div className="flex items-start gap-4">
                                    <span className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] bg-white text-[#24695c]">
                                        <WandSparkles className="h-5 w-5" />
                                    </span>
                                    <div className="flex-1">
                                        <p className="font-semibold text-[#242934]">
                                            Ringkasan AI LPJ
                                        </p>
                                        <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                            {aiSuggestion.summary}
                                        </p>
                                        <p className="mt-2 text-xs text-[#717171]">
                                            Hash prompt: {aiSuggestion.promptHash.slice(0, 12)}
                                        </p>
                                        {aiSuggestion.recommendations.length > 0 && (
                                            <div className="mt-3 space-y-2">
                                                {aiSuggestion.recommendations.map((recommendation) => (
                                                    <p
                                                        key={recommendation}
                                                        className="rounded-[4px] bg-white px-3 py-2 text-sm text-[#59667a]"
                                                    >
                                                        {recommendation}
                                                    </p>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
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

function resolveLpjAiSuggestion(
    value: Record<string, unknown> | undefined,
    projectId: number | null,
): LpjAiSuggestion | null {
    if (
        projectId === null ||
        value?.type !== 'lpj_summary' ||
        value.projectId !== projectId ||
        typeof value.summary !== 'string' ||
        !Array.isArray(value.recommendations) ||
        typeof value.promptHash !== 'string'
    ) {
        return null;
    }

    return {
        type: 'lpj_summary',
        projectId,
        summary: value.summary,
        recommendations: value.recommendations.filter(
            (recommendation): recommendation is string =>
                typeof recommendation === 'string',
        ),
        promptHash: value.promptHash,
    };
}
