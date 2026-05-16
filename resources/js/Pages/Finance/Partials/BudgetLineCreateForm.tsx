import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';

import type { BudgetProjectOption } from '../BudgetDraft';

interface Props {
    projects: BudgetProjectOption[];
    onClose: () => void;
}

interface CreateForm {
    project_id: string;
    name: string;
    category: string;
    planned_amount: string;
}

export default function BudgetLineCreateForm({ projects, onClose }: Props) {
    const form = useForm<CreateForm>({
        project_id: projects[0] ? String(projects[0].id) : '',
        name: '',
        category: '',
        planned_amount: '0',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            project_id: Number(data.project_id),
            name: data.name,
            category: data.category,
            planned_amount: Number(data.planned_amount),
        }));

        form.post(route('finance.budget-lines.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    return (
        <VihoCard
            title="Tambah Budget Line"
            subtitle="Pilih project, isi nama, kategori, dan rencana anggaran."
        >
            <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Project
                    </label>
                    <select
                        value={form.data.project_id}
                        onChange={(event) =>
                            form.setData('project_id', event.target.value)
                        }
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        required
                    >
                        {projects.length === 0 ? (
                            <option value="">— Tidak ada project —</option>
                        ) : null}
                        {projects.map((project) => (
                            <option key={project.id} value={project.id}>
                                {project.name}
                            </option>
                        ))}
                    </select>
                    {form.errors.project_id ? (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.project_id}
                        </p>
                    ) : null}
                </div>

                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Nama Item
                    </label>
                    <input
                        type="text"
                        value={form.data.name}
                        onChange={(event) =>
                            form.setData('name', event.target.value)
                        }
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        required
                    />
                    {form.errors.name ? (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.name}
                        </p>
                    ) : null}
                </div>

                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Kategori
                    </label>
                    <input
                        type="text"
                        value={form.data.category}
                        onChange={(event) =>
                            form.setData('category', event.target.value)
                        }
                        placeholder="Konsumsi, Venue, Publikasi, dll"
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        required
                    />
                    {form.errors.category ? (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.category}
                        </p>
                    ) : null}
                </div>

                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Planned Amount (IDR)
                    </label>
                    <input
                        type="number"
                        min="0"
                        step="1000"
                        value={form.data.planned_amount}
                        onChange={(event) =>
                            form.setData('planned_amount', event.target.value)
                        }
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        required
                    />
                    {form.errors.planned_amount ? (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.planned_amount}
                        </p>
                    ) : null}
                </div>

                <div className="flex justify-end gap-2 sm:col-span-2">
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-[4px] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] ring-1 ring-[#e6edef] hover:bg-[#f5f7fb]"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43] disabled:opacity-60"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </VihoCard>
    );
}
