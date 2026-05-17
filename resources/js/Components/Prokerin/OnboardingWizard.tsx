import { Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, CheckCircle2, Circle, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { PageProps } from '@/types';

export default function OnboardingWizard() {
    const { onboarding } = usePage<PageProps>().props;
    const [dismissed, setDismissed] = useState(false);
    const [activeStep, setActiveStep] = useState(1);

    useEffect(() => {
        if (onboarding?.show) {
            setActiveStep(Math.max(1, Math.min(5, onboarding.currentStep)));
        }
    }, [onboarding?.currentStep, onboarding?.show]);

    if (!onboarding?.show || dismissed) {
        return null;
    }

    const completedCount = onboarding.steps.filter(
        (step) => step.complete,
    ).length;
    const totalCount = onboarding.steps.length;
    const percent = Math.round((completedCount / Math.max(totalCount, 1)) * 100);
    const current = onboarding.steps[activeStep - 1] ?? onboarding.steps[0];
    const allComplete = completedCount >= Math.max(totalCount - 1, 1);

    const finish = () => {
        router.post(
            onboarding.completeUrl,
            {},
            { preserveScroll: true, onSuccess: () => setDismissed(true) },
        );
    };
    const skip = () => {
        router.post(
            onboarding.skipUrl,
            {},
            { preserveScroll: true, onSuccess: () => setDismissed(true) },
        );
    };
    const completeCurrentStep = () => {
        const url = onboarding.stepCompleteUrl.replace(
            '__STEP__',
            String(activeStep),
        );

        router.post(url, {}, { preserveScroll: true });
    };
    const moveStep = (direction: -1 | 1) => {
        setActiveStep((step) =>
            Math.max(1, Math.min(totalCount, step + direction)),
        );
    };

    return (
        <div className="fixed inset-0 z-40 flex items-end justify-center bg-black/30 px-4 pb-4 sm:items-center sm:px-6">
            <div className="w-full max-w-4xl rounded-[6px] bg-white shadow-xl ring-1 ring-[#e6edef]">
                <div className="flex items-start justify-between gap-3 border-b border-[#e6edef] p-4">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                            Onboarding
                        </p>
                        <h2 className="mt-1 text-base font-semibold text-[#242934]">
                            Setup awal {onboarding.organizationName ?? 'organisasi'}{' '}
                            <span className="ml-2 text-xs font-medium text-[#59667a]">
                                {completedCount}/{totalCount} selesai · {percent}%
                            </span>
                        </h2>
                    </div>
                    <button
                        type="button"
                        onClick={() => setDismissed(true)}
                        aria-label="Tutup wizard"
                        className="text-[#59667a] hover:text-[#242934]"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>

                <ol className="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-5">
                    {onboarding.steps.map((step, index) => (
                        <li
                            key={step.key}
                            className={`rounded-[4px] border p-3 text-left text-sm ${
                                activeStep === index + 1
                                    ? 'border-[#24695c] bg-white shadow-sm'
                                    : step.complete
                                      ? 'border-[#24695c] bg-[rgba(36,105,92,0.05)]'
                                      : 'border-[#e6edef] bg-[#f5f7fb]'
                            }`}
                        >
                            <button
                                type="button"
                                onClick={() => setActiveStep(index + 1)}
                                className="block w-full text-left"
                            >
                                <div className="flex items-center gap-2">
                                    {step.complete ? (
                                        <CheckCircle2 className="h-4 w-4 text-[#24695c]" />
                                    ) : (
                                        <Circle className="h-4 w-4 text-[#59667a]" />
                                    )}
                                    <span className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                                        Step {index + 1}
                                    </span>
                                </div>
                                <p className="mt-2 font-semibold text-[#242934]">
                                    {step.label}
                                </p>
                            </button>
                        </li>
                    ))}
                </ol>

                <div className="mx-4 mb-4 rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4">
                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-[#24695c]">
                        Step {activeStep} dari {totalCount}
                    </p>
                    <h3 className="mt-2 text-lg font-semibold text-[#242934]">
                        {current.label}
                    </h3>
                    <p className="mt-2 text-sm leading-6 text-[#59667a]">
                        {stepDescription(current.key)}
                    </p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        {current.href && (
                            <Link
                                href={current.href}
                                className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1b4c43]"
                            >
                                {current.complete ? 'Buka halaman' : 'Kerjakan step'}
                                <ArrowRight className="ml-2 h-3.5 w-3.5" />
                            </Link>
                        )}
                        {!current.complete && (
                            <button
                                type="button"
                                onClick={completeCurrentStep}
                                className="inline-flex items-center justify-center rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#24695c] ring-1 ring-[#24695c] hover:bg-[rgba(36,105,92,0.05)]"
                            >
                                Tandai selesai
                            </button>
                        )}
                    </div>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-2 border-t border-[#e6edef] bg-[#f5f7fb] p-3">
                    <button
                        type="button"
                        onClick={skip}
                        className="inline-flex items-center justify-center rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] ring-1 ring-[#e6edef] hover:bg-[#f5f7fb]"
                    >
                        Skip onboarding
                    </button>

                    <div className="flex flex-wrap items-center justify-end gap-2">
                        <button
                            type="button"
                            onClick={() => moveStep(-1)}
                            disabled={activeStep === 1}
                            className="inline-flex items-center justify-center rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] ring-1 ring-[#e6edef] hover:bg-[#f5f7fb] disabled:opacity-50"
                        >
                            <ArrowLeft className="mr-2 h-3.5 w-3.5" />
                            Prev
                        </button>
                        <button
                            type="button"
                            onClick={() => moveStep(1)}
                            disabled={activeStep === totalCount}
                            className="inline-flex items-center justify-center rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] ring-1 ring-[#e6edef] hover:bg-[#f5f7fb] disabled:opacity-50"
                        >
                            Next
                            <ArrowRight className="ml-2 h-3.5 w-3.5" />
                        </button>
                        <button
                            type="button"
                            onClick={finish}
                            disabled={!allComplete}
                            className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1b4c43] disabled:opacity-60"
                        >
                            Selesai setup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

function stepDescription(key: string): string {
    const descriptions: Record<string, string> = {
        period:
            'Pastikan periode kepengurusan aktif sudah dibuat agar semua proker, laporan, dan handover punya konteks waktu yang jelas.',
        invite:
            'Undang pengurus inti supaya sekretaris, bendahara, ketua pelaksana, dan anggota bisa langsung bekerja di workspace yang sama.',
        project:
            'Buat proker pertama dari template agar timeline, task, proposal, dan kebutuhan dasar langsung tersusun.',
        budget:
            'Lengkapi RAB awal supaya bendahara bisa melihat rencana biaya, approval, dan realisasi sejak awal.',
        preview:
            'Cek dashboard untuk memastikan semua setup sudah muncul sebagai ringkasan kerja organisasi.',
    };

    return descriptions[key] ?? 'Lengkapi step ini untuk mempercepat setup organisasi.';
}
