import {
    CheckCircle2,
    FilePenLine,
    RotateCcw,
    Save,
    Send,
    WandSparkles,
} from 'lucide-react';

import ApprovalWorkflowTimeline from '@/Components/Approval/ApprovalWorkflowTimeline';
import RichTextEditor from '@/Components/Editor/RichTextEditor';
import RichTextRenderer from '@/Components/Editor/RichTextRenderer';
import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import type { PageProps } from '@/types';
import type { ProposalDraft } from '@/types/prokerin';
import { normalizeTiptap } from '@/types/rich-text';
import type { TiptapJson } from '@/types/rich-text';
import { Head, useForm, usePage } from '@inertiajs/react';

interface ProposalEditorProps {
    proposalDraft: ProposalDraft;
}

interface ProposalDraftForm {
    sections: Array<{
        heading: string;
        body: TiptapJson;
    }>;
}

interface ProposalAiSuggestion {
    type: 'proposal_draft';
    proposalDraftId: number;
    sections: ProposalDraft['sections'];
    promptHash: string;
}

export default function ProposalEditor({ proposalDraft }: ProposalEditorProps) {
    const { flash } = usePage<PageProps>().props;
    const aiSuggestion = resolveProposalAiSuggestion(
        flash.aiSuggestion,
        proposalDraft.id,
    );
    const draftForm = useForm<ProposalDraftForm>({
        sections: proposalDraft.sections.map((section) => ({
            heading: section.heading,
            body: normalizeTiptap(section.body),
        })),
    });
    const aiForm = useForm();
    const submitForm = useForm();
    const decisionForm = useForm<{ decision: 'approve' | 'request_changes' }>({
        decision: 'approve',
    });

    const updateSectionBody = (index: number, body: TiptapJson): void => {
        draftForm.setData(
            'sections',
            draftForm.data.sections.map((section, sectionIndex) =>
                sectionIndex === index ? { ...section, body } : section,
            ),
        );
    };

    const saveDraft = (): void => {
        if (proposalDraft.id === null) {
            return;
        }

        draftForm.patch(
            route('reports.proposal-drafts.update', proposalDraft.id),
            {
                preserveScroll: true,
            },
        );
    };

    const generateAiSuggestion = (): void => {
        if (proposalDraft.id === null) {
            return;
        }

        aiForm.post(
            route('reports.proposal-drafts.ai-suggestions', proposalDraft.id),
            {
                preserveScroll: true,
            },
        );
    };

    const applyAiSuggestion = (): void => {
        if (aiSuggestion === null) {
            return;
        }

        draftForm.setData(
            'sections',
            aiSuggestion.sections.map((section) => ({
                heading: section.heading,
                body: normalizeTiptap(section.body),
            })),
        );
    };

    const submitForApproval = (): void => {
        if (proposalDraft.id === null) {
            return;
        }

        submitForm.post(
            route('reports.proposal-drafts.submit', proposalDraft.id),
            {
                preserveScroll: true,
            },
        );
    };

    const decideProposal = (
        decision: 'approve' | 'request_changes',
    ): void => {
        if (proposalDraft.id === null) {
            return;
        }

        decisionForm.transform(() => ({ decision }));
        decisionForm.patch(
            route('reports.proposal-drafts.decision', proposalDraft.id),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M08 · Proposal Generator
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Proposal Editor
                    </h1>
                </div>
            }
        >
            <Head title="Proposal Editor" />

            <div className="grid gap-6 xl:grid-cols-[280px_1fr]">
                <VihoCard title="Sections">
                    <div className="space-y-2">
                        {proposalDraft.sections.map((section, index) => (
                            <button
                                key={section.heading}
                                type="button"
                                className="flex w-full items-center justify-between rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-left text-sm font-semibold text-[#59667a] transition hover:text-[#24695c]"
                            >
                                <span>{section.heading}</span>
                                {index < 3 && (
                                    <VihoStatusBadge tone="success">
                                        Done
                                    </VihoStatusBadge>
                                )}
                            </button>
                        ))}
                    </div>
                </VihoCard>

                <VihoCard
                    title="Draft Proposal"
                    subtitle="Konten auto-fill dari data proker, timeline, dan RAB sebelum dikirim ke approval."
                    action={
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                disabled={
                                    !proposalDraft.canEdit ||
                                    aiForm.processing
                                }
                                onClick={generateAiSuggestion}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#ba895d] hover:text-[#ba895d] disabled:cursor-not-allowed disabled:bg-[#f5f7fb]"
                            >
                                <WandSparkles className="h-4 w-4" />
                                Buat Saran AI
                            </button>
                            <button
                                type="button"
                                disabled={
                                    !proposalDraft.canEdit ||
                                    draftForm.processing
                                }
                                onClick={saveDraft}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c] disabled:cursor-not-allowed disabled:bg-[#f5f7fb]"
                            >
                                <Save className="h-4 w-4" />
                                Simpan
                            </button>
                            <button
                                type="button"
                                disabled={
                                    !proposalDraft.canSubmit ||
                                    submitForm.processing
                                }
                                onClick={submitForApproval}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                            >
                                <Send className="h-4 w-4" />
                                Kirim Approval
                            </button>
                        </div>
                    }
                >
                    <div className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-5">
                        <div className="flex items-center gap-3 border-b border-[#e6edef] pb-4">
                            <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-white text-[#24695c]">
                                <FilePenLine className="h-5 w-5" />
                            </span>
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="font-semibold text-[#242934]">
                                        {proposalDraft.title}
                                    </p>
                                    <VihoStatusBadge tone="success">
                                        {humanizeStatus(proposalDraft.status)}
                                    </VihoStatusBadge>
                                </div>
                                <p className="mt-1 text-sm text-[#717171]">
                                    {proposalDraft.subtitle}
                                </p>
                            </div>
                        </div>
                        {proposalDraft.canDecide && (
                            <div className="mt-5 flex flex-wrap gap-2 border-b border-[#e6edef] pb-5">
                                <button
                                    type="button"
                                    disabled={decisionForm.processing}
                                    onClick={() => decideProposal('approve')}
                                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                >
                                    <CheckCircle2 className="h-4 w-4" />
                                    Approve
                                </button>
                                <button
                                    type="button"
                                    disabled={decisionForm.processing}
                                    onClick={() =>
                                        decideProposal('request_changes')
                                    }
                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#ba895d] hover:text-[#ba895d] disabled:cursor-not-allowed disabled:bg-[#f5f7fb]"
                                >
                                    <RotateCcw className="h-4 w-4" />
                                    Revisi
                                </button>
                            </div>
                        )}
                        <div className="mt-5">
                            <ApprovalWorkflowTimeline
                                timeline={proposalDraft.workflowTimeline}
                            />
                        </div>
                        {aiSuggestion && (
                            <div className="mt-5 rounded-[4px] border border-[#e6edef] bg-white p-4">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-semibold text-[#242934]">
                                            Saran AI siap dipakai
                                        </p>
                                        <p className="mt-1 text-xs text-[#717171]">
                                            Hash prompt: {aiSuggestion.promptHash.slice(0, 12)}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={applyAiSuggestion}
                                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        <WandSparkles className="h-4 w-4" />
                                        Terapkan
                                    </button>
                                </div>
                                <div className="mt-4 space-y-3">
                                    {aiSuggestion.sections.map((section) => (
                                        <div
                                            key={section.heading}
                                            className="rounded-[4px] bg-[#f5f7fb] p-3"
                                        >
                                            <p className="text-sm font-semibold text-[#242934]">
                                                {section.heading}
                                            </p>
                                            <div className="mt-1">
                                                <RichTextRenderer
                                                    value={normalizeTiptap(
                                                        section.body,
                                                    )}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                        <div className="mt-5 space-y-4">
                            {draftForm.data.sections.map((section, index) => (
                                <label key={section.heading} className="block">
                                    <span className="text-sm font-semibold text-[#242934]">
                                        {section.heading}
                                    </span>
                                    <RichTextEditor
                                        value={section.body}
                                        readOnly={!proposalDraft.canEdit}
                                        maxChars={5000}
                                        placeholder={`Tulis ${section.heading.toLowerCase()}...`}
                                        onChange={(body) =>
                                            updateSectionBody(index, body)
                                        }
                                    />
                                    <InputError
                                        message={
                                            draftForm.errors[
                                                `sections.${index}.body`
                                            ]
                                        }
                                        className="mt-2"
                                    />
                                </label>
                            ))}
                        </div>
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function resolveProposalAiSuggestion(
    value: Record<string, unknown> | undefined,
    proposalDraftId: number | null,
): ProposalAiSuggestion | null {
    if (
        proposalDraftId === null ||
        value?.type !== 'proposal_draft' ||
        value.proposalDraftId !== proposalDraftId ||
        !Array.isArray(value.sections) ||
        typeof value.promptHash !== 'string'
    ) {
        return null;
    }

    return {
        type: 'proposal_draft',
        proposalDraftId,
        sections: value.sections
            .filter(
                (section): section is { heading: string; body: string } =>
                    typeof section === 'object' &&
                    section !== null &&
                    'heading' in section &&
                    'body' in section &&
                    typeof section.heading === 'string' &&
                    typeof section.body === 'string',
            )
            .map((section) => ({
                heading: section.heading,
                body: section.body,
            })),
        promptHash: value.promptHash,
    };
}
