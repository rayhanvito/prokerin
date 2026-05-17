import { useForm } from '@inertiajs/react';
import { CalendarClock, X } from 'lucide-react';
import type { FormEvent } from 'react';

interface ProjectOption {
    id: number;
    name: string;
}

interface Props {
    itemId: number;
    projects: ProjectOption[];
    open: boolean;
    onClose: () => void;
}

interface LoanFormData {
    project_id: string;
    expected_return_at: string;
    notes: string;
}

export default function LoanRequestModal({
    itemId,
    projects,
    open,
    onClose,
}: Props) {
    const form = useForm<LoanFormData>({
        project_id: '',
        expected_return_at: '',
        notes: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        form.post(route('inventory.loans.store', itemId), {
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
                        <CalendarClock className="h-4 w-4 text-[#24695c]" />
                        Ajukan Peminjaman
                    </div>
                    <button type="button" onClick={onClose} aria-label="Tutup">
                        <X className="h-5 w-5 text-[#59667a]" />
                    </button>
                </div>

                <div className="mt-5 space-y-4">
                    <label className="block text-sm font-semibold text-[#242934]">
                        Proker terkait
                        <select
                            value={form.data.project_id}
                            onChange={(event) =>
                                form.setData('project_id', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
                        >
                            <option value="">Tanpa proker</option>
                            {projects.map((project) => (
                                <option key={project.id} value={project.id}>
                                    {project.name}
                                </option>
                            ))}
                        </select>
                    </label>

                    <label className="block text-sm font-semibold text-[#242934]">
                        Target kembali
                        <input
                            type="datetime-local"
                            value={form.data.expected_return_at}
                            onChange={(event) =>
                                form.setData(
                                    'expected_return_at',
                                    event.target.value,
                                )
                            }
                            className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
                            required
                        />
                    </label>

                    <label className="block text-sm font-semibold text-[#242934]">
                        Catatan
                        <textarea
                            value={form.data.notes}
                            onChange={(event) =>
                                form.setData('notes', event.target.value)
                            }
                            className="mt-1 min-h-24 w-full rounded-[4px] border-[#e6edef] text-sm"
                        />
                    </label>
                </div>

                <button
                    type="submit"
                    disabled={form.processing}
                    className="mt-5 inline-flex w-full items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                >
                    Kirim Permintaan
                </button>
            </form>
        </div>
    );
}
