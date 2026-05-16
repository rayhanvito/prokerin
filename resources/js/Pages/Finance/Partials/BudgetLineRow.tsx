import { router, useForm } from '@inertiajs/react';
import { Pencil, Save, Trash2, X } from 'lucide-react';
import { useState } from 'react';

import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import { formatRupiah, humanizeStatus } from '@/lib/format';

import type { BudgetLineItem, BudgetStatusOption } from '../BudgetDraft';

interface Props {
    line: BudgetLineItem;
    statusOptions: BudgetStatusOption[];
    canManage: boolean;
}

interface EditForm {
    name: string;
    category: string;
    planned_amount: string;
}

export default function BudgetLineRow({ line, statusOptions, canManage }: Props) {
    const [isEditing, setIsEditing] = useState(false);
    const overBudget = line.realizedAmount > line.plannedAmount;

    const form = useForm<EditForm>({
        name: line.name,
        category: line.category,
        planned_amount: String(line.plannedAmount),
    });

    const submit = () => {
        form.transform((data) => ({
            name: data.name,
            category: data.category,
            planned_amount: Number(data.planned_amount),
        }));

        form.patch(route('finance.budget-lines.update', { budgetLine: line.id }), {
            preserveScroll: true,
            onSuccess: () => setIsEditing(false),
        });
    };

    const cancelEdit = () => {
        form.reset();
        form.clearErrors();
        setIsEditing(false);
    };

    const handleDelete = () => {
        if (
            !window.confirm(
                `Hapus budget line "${line.name}"? Tindakan ini tidak bisa dibatalkan.`,
            )
        ) {
            return;
        }

        router.delete(route('finance.budget-lines.destroy', { budgetLine: line.id }), {
            preserveScroll: true,
        });
    };

    if (isEditing && canManage) {
        return (
            <tr className="border-b border-[#e6edef] bg-[#f5f7fb]">
                <td className="py-2 pr-3">
                    <input
                        type="text"
                        value={form.data.name}
                        onChange={(event) =>
                            form.setData('name', event.target.value)
                        }
                        className="w-full rounded-[4px] border border-[#e6edef] px-2 py-1 text-sm"
                    />
                    {form.errors.name ? (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.name}
                        </p>
                    ) : null}
                </td>
                <td className="py-2 pr-3 text-[#59667a]">{line.projectName}</td>
                <td className="py-2 pr-3">
                    <input
                        type="text"
                        value={form.data.category}
                        onChange={(event) =>
                            form.setData('category', event.target.value)
                        }
                        className="w-full rounded-[4px] border border-[#e6edef] px-2 py-1 text-sm"
                    />
                </td>
                <td className="py-2 pr-3 text-right">
                    <input
                        type="number"
                        min="0"
                        step="1000"
                        value={form.data.planned_amount}
                        onChange={(event) =>
                            form.setData('planned_amount', event.target.value)
                        }
                        className="w-full rounded-[4px] border border-[#e6edef] px-2 py-1 text-right text-sm"
                    />
                </td>
                <td className="py-2 pr-3 text-right text-[#59667a]">
                    {formatRupiah(line.realizedAmount)}
                </td>
                <td className="py-2 pr-3">
                    <VihoStatusBadge>{humanizeStatus(line.status)}</VihoStatusBadge>
                </td>
                <td className="py-2 pr-3">
                    <div className="flex items-center justify-end gap-1">
                        <button
                            type="button"
                            onClick={submit}
                            disabled={form.processing}
                            className="rounded-[4px] p-1.5 text-[#24695c] hover:bg-white"
                            aria-label="Simpan"
                        >
                            <Save className="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            onClick={cancelEdit}
                            className="rounded-[4px] p-1.5 text-[#59667a] hover:bg-white"
                            aria-label="Batal"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                </td>
            </tr>
        );
    }

    return (
        <tr className="border-b border-[#e6edef]">
            <td className="py-2 pr-3 font-medium text-[#242934]">{line.name}</td>
            <td className="py-2 pr-3 text-[#59667a]">{line.projectName}</td>
            <td className="py-2 pr-3 text-[#59667a]">{line.category}</td>
            <td className="py-2 pr-3 text-right text-[#242934]">
                {formatRupiah(line.plannedAmount)}
            </td>
            <td
                className={`py-2 pr-3 text-right ${overBudget ? 'font-semibold text-[#d22d3d]' : 'text-[#242934]'}`}
            >
                {formatRupiah(line.realizedAmount)}
            </td>
            <td className="py-2 pr-3">
                <VihoStatusBadge>{humanizeStatus(line.status)}</VihoStatusBadge>
            </td>
            {canManage ? (
                <td className="py-2 pr-3">
                    <div className="flex items-center justify-end gap-1">
                        {line.isEditable ? (
                            <button
                                type="button"
                                onClick={() => setIsEditing(true)}
                                className="rounded-[4px] p-1.5 text-[#24695c] hover:bg-[#f5f7fb]"
                                aria-label="Edit"
                            >
                                <Pencil className="h-4 w-4" />
                            </button>
                        ) : null}
                        {line.isDeletable ? (
                            <button
                                type="button"
                                onClick={handleDelete}
                                className="rounded-[4px] p-1.5 text-[#d22d3d] hover:bg-[#f5f7fb]"
                                aria-label="Hapus"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        ) : null}
                    </div>
                </td>
            ) : null}
        </tr>
    );
}
