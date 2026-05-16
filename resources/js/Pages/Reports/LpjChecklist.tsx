import { CheckCircle2, Circle } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

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
    checklistItems: LpjChecklistItem[];
    readiness: LpjReadiness;
}

export default function LpjChecklist({
    checklistItems,
    readiness,
}: LpjChecklistProps) {
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

            <VihoCard
                title="Checklist Pertanggungjawaban"
                subtitle={`${readiness.completionProgress}% lengkap · ${readiness.missingRequiredItems.length} item wajib belum lengkap.`}
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
        </AuthenticatedLayout>
    );
}
