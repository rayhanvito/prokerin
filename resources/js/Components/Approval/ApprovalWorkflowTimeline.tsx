import { CheckCircle2, Clock3, RotateCcw, XCircle } from 'lucide-react';

import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import { cn } from '@/lib/utils';
import type { ApprovalWorkflowTimeline as ApprovalWorkflowTimelineData } from '@/types/prokerin';

interface ApprovalWorkflowTimelineProps {
    timeline: ApprovalWorkflowTimelineData;
}

const decisionIcon = {
    approved: CheckCircle2,
    rejected: XCircle,
    revision_requested: RotateCcw,
    pending: Clock3,
};

const decisionTone = {
    approved: 'text-[#24695c]',
    rejected: 'text-[#d22d3d]',
    revision_requested: 'text-[#ba895d]',
    pending: 'text-[#717171]',
};

export default function ApprovalWorkflowTimeline({
    timeline,
}: ApprovalWorkflowTimelineProps) {
    if (timeline.id === null || timeline.steps.length === 0) {
        return (
            <div className="rounded-[4px] border border-dashed border-[#e6edef] bg-white p-4 text-sm text-[#717171]">
                Belum ada workflow approval untuk item ini.
            </div>
        );
    }

    return (
        <div className="rounded-[4px] border border-[#e6edef] bg-white">
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-[#e6edef] px-4 py-3">
                <div>
                    <p className="text-sm font-semibold text-[#242934]">
                        Timeline Approval
                    </p>
                    <p className="text-xs text-[#717171]">
                        Step aktif {timeline.currentStep} ·{' '}
                        {timeline.workflowType ?? 'workflow'}
                    </p>
                </div>
                <VihoStatusBadge>{timeline.status ?? 'pending'}</VihoStatusBadge>
            </div>
            <div className="divide-y divide-[#e6edef]">
                {timeline.steps.map((step) => {
                    const Icon = decisionIcon[step.decision];

                    return (
                        <div
                            key={step.stepOrder}
                            className="flex gap-3 px-4 py-3"
                        >
                            <span className="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[4px] bg-[#f5f7fb]">
                                <Icon
                                    className={cn(
                                        'h-4 w-4',
                                        decisionTone[step.decision],
                                    )}
                                />
                            </span>
                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="text-sm font-semibold text-[#242934]">
                                        Step {step.stepOrder} ·{' '}
                                        {step.approverName}
                                    </p>
                                    <VihoStatusBadge>
                                        {step.decision}
                                    </VihoStatusBadge>
                                </div>
                                {step.note !== null && (
                                    <p className="mt-1 text-sm text-[#59667a]">
                                        {step.note}
                                    </p>
                                )}
                                {step.decidedAt !== null && (
                                    <p className="mt-1 text-xs text-[#717171]">
                                        Diputuskan {step.decidedAt}
                                    </p>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
