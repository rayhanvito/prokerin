import { useForm } from '@inertiajs/react';
import { RotateCcw, X } from 'lucide-react';
import type { FormEvent } from 'react';

interface Option {
    value: string;
    label: string;
}

interface Props {
    loanId: number;
    options: Option[];
    open: boolean;
    onClose: () => void;
}

interface ReturnFormData {
    return_condition: string;
    notes: string;
}

export default function LoanReturnModal({
    loanId,
    options,
    open,
    onClose,
}: Props) {
    const form = useForm<ReturnFormData>({
        return_condition: 'same',
        notes: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        form.patch(route('inventory.loans.return', loanId), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <form
                onSubmit={submit}
                className="w-full max-w-lg rounded-[4px] bg-white p-5 shadow-lg"
            >
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2 font-semibold text-[#242934]">
                        <RotateCcw className="h-4 w-4 text-[#24695c]" />
                        Catat Pengembalian
                    </div>
                    <button type="button" onClick={onClose} aria-label="Tutup">
                        <X className="h-5 w-5 text-[#59667a]" />
                    </button>
                </div>

                <label className="mt-5 block text-sm font-semibold text-[#242934]">
                    Kondisi kembali
                    <select
                        value={form.data.return_condition}
                        onChange={(event) =>
                            form.setData('return_condition', event.target.value)
                        }
                        className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
                    >
                        {options.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </label>

                <label className="mt-4 block text-sm font-semibold text-[#242934]">
                    Catatan
                    <textarea
                        value={form.data.notes}
                        onChange={(event) =>
                            form.setData('notes', event.target.value)
                        }
                        className="mt-1 min-h-24 w-full rounded-[4px] border-[#e6edef] text-sm"
                    />
                </label>

                <button
                    type="submit"
                    disabled={form.processing}
                    className="mt-5 inline-flex w-full items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                >
                    Simpan Pengembalian
                </button>
            </form>
        </div>
    );
}
