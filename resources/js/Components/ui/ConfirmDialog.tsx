import { useEffect, useState } from 'react';

import { cn } from '@/lib/utils';

interface ConfirmDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: string;
    confirmLabel: string;
    confirmTone?: 'default' | 'danger';
    requireTypedPhrase?: string;
    onConfirm: () => void;
}

export default function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel,
    confirmTone = 'default',
    requireTypedPhrase,
    onConfirm,
}: ConfirmDialogProps) {
    const [typedPhrase, setTypedPhrase] = useState('');
    const canConfirm =
        !requireTypedPhrase || typedPhrase === requireTypedPhrase;

    useEffect(() => {
        if (!open) {
            setTypedPhrase('');
        }
    }, [open]);

    if (!open) {
        return null;
    }

    function handleConfirm(): void {
        if (!canConfirm) {
            return;
        }

        onConfirm();
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#242934]/45 px-4 py-6">
            <div
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="confirm-dialog-title"
                aria-describedby="confirm-dialog-description"
                className="w-full max-w-md rounded-[4px] border border-[#e6edef] bg-white p-6 shadow-xl"
            >
                <h2
                    id="confirm-dialog-title"
                    className="text-lg font-semibold text-[#242934]"
                >
                    {title}
                </h2>
                <p
                    id="confirm-dialog-description"
                    className="mt-2 text-sm leading-6 text-[#59667a]"
                >
                    {description}
                </p>

                {requireTypedPhrase ? (
                    <label className="mt-5 block">
                        <span className="text-sm font-medium text-[#242934]">
                            Ketik {requireTypedPhrase} untuk melanjutkan
                        </span>
                        <input
                            type="text"
                            value={typedPhrase}
                            onChange={(event) =>
                                setTypedPhrase(event.target.value)
                            }
                            className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                        />
                    </label>
                ) : null}

                <div className="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#242934] transition hover:bg-[#f5f7fb]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        onClick={handleConfirm}
                        disabled={!canConfirm}
                        className={cn(
                            'rounded-[4px] px-4 py-2 text-sm font-semibold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-60',
                            confirmTone === 'danger'
                                ? 'bg-[#d22d3d] hover:bg-[#b82432]'
                                : 'bg-[#24695c] hover:bg-[#1b4c43]',
                        )}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
