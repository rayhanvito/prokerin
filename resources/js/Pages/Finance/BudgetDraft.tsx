import { Head } from '@inertiajs/react';
import { ReceiptText } from 'lucide-react';
import { useMemo, useState } from 'react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah } from '@/lib/format';

import BudgetLineCreateForm from './Partials/BudgetLineCreateForm';
import BudgetLineRow from './Partials/BudgetLineRow';

export interface BudgetLineItem {
    id: number;
    projectId: number;
    projectName: string;
    name: string;
    category: string;
    plannedAmount: number;
    realizedAmount: number;
    status: string;
    isEditable: boolean;
    isDeletable: boolean;
}

interface BudgetSummary {
    plannedTotal: number;
    realizedTotal: number;
    remainingBudget: number;
    realizationProgress: number;
    hasOverspend: boolean;
    lineCount: number;
    approvedLineCount: number;
}

export interface BudgetStatusOption {
    value: string;
    label: string;
}

export interface BudgetProjectOption {
    id: number;
    name: string;
}

interface BudgetDraftProps {
    activeOrganizationId: number;
    canManage: boolean;
    summary: BudgetSummary;
    statusOptions: BudgetStatusOption[];
    projects: BudgetProjectOption[];
    lines: BudgetLineItem[];
}

export default function BudgetDraft({
    canManage,
    summary,
    statusOptions,
    projects,
    lines,
}: BudgetDraftProps) {
    const [showCreateForm, setShowCreateForm] = useState(false);

    const liveTotals = useMemo(() => {
        const planned = lines.reduce((acc, line) => acc + line.plannedAmount, 0);
        const realized = lines.reduce(
            (acc, line) => acc + line.realizedAmount,
            0,
        );
        return { planned, realized };
    }, [lines]);

    const overspend = summary.hasOverspend;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M07 · Budget Draft
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Budget Draft
                    </h1>
                </div>
            }
        >
            <Head title="Budget Draft" />

            <div className="space-y-6">
                <section className="grid gap-4 md:grid-cols-4">
                    <SummaryCard
                        label="Planned (Approved)"
                        value={formatRupiah(summary.plannedTotal)}
                        note={`${summary.approvedLineCount} line approved`}
                    />
                    <SummaryCard
                        label="Realisasi"
                        value={formatRupiah(summary.realizedTotal)}
                        note="Transaksi verified"
                    />
                    <SummaryCard
                        label="Sisa Anggaran"
                        value={formatRupiah(summary.remainingBudget)}
                        note={`${summary.realizationProgress}% terpakai`}
                        tone={overspend ? 'danger' : 'success'}
                    />
                    <SummaryCard
                        label="Total Line"
                        value={String(summary.lineCount)}
                        note={`Plan total ${formatRupiah(liveTotals.planned)}`}
                    />
                </section>

                {overspend ? (
                    <div className="rounded-[4px] border border-[#d22d3d] bg-[#d22d3d]/10 p-4 text-sm font-semibold text-[#d22d3d]">
                        Realisasi sudah melampaui RAB approved. Cek line yang
                        over-budget.
                    </div>
                ) : null}

                {canManage && showCreateForm ? (
                    <BudgetLineCreateForm
                        projects={projects}
                        onClose={() => setShowCreateForm(false)}
                    />
                ) : null}

                <VihoCard
                    title="Daftar Budget Line"
                    subtitle="Inline edit per row. Realisasi diisi via halaman Realization."
                    action={
                        canManage ? (
                            <button
                                type="button"
                                onClick={() => setShowCreateForm((v) => !v)}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43]"
                            >
                                <ReceiptText className="h-4 w-4" />
                                {showCreateForm ? 'Tutup form' : 'Tambah Item'}
                            </button>
                        ) : undefined
                    }
                >
                    {lines.length === 0 ? (
                        <EmptyState
                            icon={ReceiptText}
                            title="Belum ada budget line"
                            description={
                                canManage
                                    ? 'Mulai dengan menambah line per kategori (konsumsi, venue, publikasi).'
                                    : 'Bendahara organisasi belum menambahkan budget line.'
                            }
                            action={
                                canManage
                                    ? {
                                          label: 'Tambah Line Pertama',
                                          onClick: () =>
                                              setShowCreateForm(true),
                                      }
                                    : undefined
                            }
                        />
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[720px] text-sm">
                                <thead>
                                    <tr className="border-b border-[#e6edef] text-left text-xs font-semibold uppercase tracking-wider text-[#59667a]">
                                        <th className="py-2 pr-3">Item</th>
                                        <th className="py-2 pr-3">Project</th>
                                        <th className="py-2 pr-3">Kategori</th>
                                        <th className="py-2 pr-3 text-right">
                                            Planned
                                        </th>
                                        <th className="py-2 pr-3 text-right">
                                            Realisasi
                                        </th>
                                        <th className="py-2 pr-3">Status</th>
                                        {canManage ? (
                                            <th className="py-2 pr-3 text-right">
                                                Aksi
                                            </th>
                                        ) : null}
                                    </tr>
                                </thead>
                                <tbody>
                                    {lines.map((line) => (
                                        <BudgetLineRow
                                            key={line.id}
                                            line={line}
                                            statusOptions={statusOptions}
                                            canManage={canManage}
                                        />
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr className="border-t border-[#e6edef] text-sm font-semibold text-[#242934]">
                                        <td className="py-3" colSpan={3}>
                                            Total semua line
                                        </td>
                                        <td className="py-3 pr-3 text-right">
                                            {formatRupiah(liveTotals.planned)}
                                        </td>
                                        <td className="py-3 pr-3 text-right">
                                            {formatRupiah(liveTotals.realized)}
                                        </td>
                                        <td colSpan={canManage ? 2 : 1} />
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

interface SummaryCardProps {
    label: string;
    value: string;
    note: string;
    tone?: 'default' | 'danger' | 'success';
}

function SummaryCard({ label, value, note, tone = 'default' }: SummaryCardProps) {
    const valueColor =
        tone === 'danger'
            ? 'text-[#d22d3d]'
            : tone === 'success'
              ? 'text-[#24695c]'
              : 'text-[#242934]';

    return (
        <VihoCard>
            <p className="text-sm font-medium text-[#59667a]">{label}</p>
            <p className={`mt-3 text-2xl font-semibold ${valueColor}`}>
                {value}
            </p>
            <p className="mt-3 text-xs text-[#717171]">{note}</p>
        </VihoCard>
    );
}
