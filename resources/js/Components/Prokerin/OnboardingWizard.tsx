import { Link, router, usePage } from '@inertiajs/react';
import { CheckCircle2, Circle, X } from 'lucide-react';
import { useState } from 'react';

import { PageProps } from '@/types';

export default function OnboardingWizard() {
    const { onboarding } = usePage<PageProps>().props;
    const [dismissed, setDismissed] = useState(false);

    if (!onboarding?.show || dismissed) {
        return null;
    }

    const completedCount = onboarding.steps.filter(
        (step) => step.complete,
    ).length;
    const totalCount = onboarding.steps.length;
    const percent = Math.round((completedCount / Math.max(totalCount, 1)) * 100);
    const allComplete = completedCount >= Math.max(totalCount - 1, 1); // step "preview" is always incomplete; all real steps complete = allow finish

    const finish = () => {
        router.post(
            onboarding.completeUrl,
            {},
            { preserveScroll: true, onSuccess: () => setDismissed(true) },
        );
    };

    return (
        <div className="fixed inset-x-0 bottom-0 z-40 flex justify-center px-4 pb-4 sm:px-6">
            <div className="w-full max-w-3xl rounded-t-[6px] bg-white shadow-xl ring-1 ring-[#e6edef] sm:rounded-[6px]">
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
                            className={`rounded-[4px] border p-3 text-sm ${
                                step.complete
                                    ? 'border-[#24695c] bg-[rgba(36,105,92,0.05)]'
                                    : 'border-[#e6edef] bg-[#f5f7fb]'
                            }`}
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
                            {step.href && (
                                <Link
                                    href={step.href}
                                    className="mt-2 inline-flex text-xs font-semibold text-[#24695c] hover:underline"
                                >
                                    {step.complete ? 'Lihat lagi →' : 'Mulai →'}
                                </Link>
                            )}
                        </li>
                    ))}
                </ol>

                <div className="flex items-center justify-end gap-2 border-t border-[#e6edef] bg-[#f5f7fb] p-3">
                    <button
                        type="button"
                        onClick={() => setDismissed(true)}
                        className="inline-flex items-center justify-center rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] ring-1 ring-[#e6edef] hover:bg-[#f5f7fb]"
                    >
                        Tutup sementara
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
    );
}
