import { FilePenLine, Send } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import type { ProposalDraft } from '@/types/prokerin';
import { Head, useForm } from '@inertiajs/react';

interface ProposalEditorProps {
    proposalDraft: ProposalDraft;
}

export default function ProposalEditor({ proposalDraft }: ProposalEditorProps) {
    const { post, processing } = useForm();

    const submitForApproval = (): void => {
        if (proposalDraft.id === null) {
            return;
        }

        post(route('reports.proposal-drafts.submit', proposalDraft.id), {
            preserveScroll: true,
        });
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
                        <button
                            type="button"
                            disabled={!proposalDraft.canSubmit || processing}
                            onClick={submitForApproval}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                        >
                            <Send className="h-4 w-4" />
                            Kirim Approval
                        </button>
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
                        <div className="mt-5 space-y-4 text-sm leading-7 text-[#59667a]">
                            {proposalDraft.sections.slice(0, 2).map((section) => (
                                <p key={section.heading}>{section.body}</p>
                            ))}
                        </div>
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
