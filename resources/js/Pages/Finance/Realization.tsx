import { ReceiptText, UploadCloud } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

interface BudgetLineOption {
    id: number;
    name: string;
    projectName: string;
    plannedAmount: number;
    realizedAmount: number;
    status: string;
}

interface BudgetTransaction {
    name: string;
    budget: string;
    spent: number;
    receipt: string;
    status: string;
}

interface FinanceRealizationProps {
    budgetLines: BudgetLineOption[];
    transactions: BudgetTransaction[];
}

interface RealizationForm {
    budget_line_id: string;
    name: string;
    amount: string;
    receipt: File | null;
    budget_line: string;
}

export default function FinanceRealization({
    budgetLines,
    transactions,
}: FinanceRealizationProps) {
    const readyBudgetLines = budgetLines.filter((line) =>
        ['approved', 'realized'].includes(line.status),
    );
    const firstBudgetLineId = readyBudgetLines[0]?.id.toString() ?? '';
    const { data, setData, post, processing, errors, reset } =
        useForm<RealizationForm>({
            budget_line_id: firstBudgetLineId,
            name: '',
            amount: '',
            receipt: null,
            budget_line: '',
        });

    const rows = transactions.map((transaction) => ({
        budget: transaction.budget,
        receipt: transaction.receipt,
        spent: formatRupiah(transaction.spent),
        status: humanizeStatus(transaction.status),
        transaction: transaction.name,
    }));

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (data.budget_line_id === '') {
            return;
        }

        post(
            route('finance.realizations.store', {
                budgetLine: data.budget_line_id,
            }),
            {
                forceFormData: true,
                onSuccess: () => reset('name', 'amount', 'receipt'),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M07 · Realization
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Finance Realization
                    </h1>
                </div>
            }
        >
            <Head title="Finance Realization" />

            <div className="grid gap-6 xl:grid-cols-[360px_1fr]">
                <VihoCard
                    title="Catat Realisasi"
                    subtitle="Upload receipt untuk budget line yang sudah approved."
                    action={
                        <button
                            type="submit"
                            form="realization-form"
                            disabled={
                                processing || readyBudgetLines.length === 0
                            }
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                        >
                            <UploadCloud className="h-4 w-4" />
                            Simpan
                        </button>
                    }
                >
                    <form
                        id="realization-form"
                        className="space-y-5"
                        onSubmit={submit}
                    >
                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Budget Line
                            </span>
                            <select
                                value={data.budget_line_id}
                                disabled={readyBudgetLines.length === 0}
                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c] disabled:bg-[#f5f7fb]"
                                onChange={(event) =>
                                    setData(
                                        'budget_line_id',
                                        event.target.value,
                                    )
                                }
                            >
                                {readyBudgetLines.map((line) => (
                                    <option key={line.id} value={line.id}>
                                        {line.name} ·{' '}
                                        {formatRupiah(line.realizedAmount)} /{' '}
                                        {formatRupiah(line.plannedAmount)}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={errors.budget_line}
                                className="mt-2"
                            />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Nama Transaksi
                            </span>
                            <input
                                type="text"
                                value={data.name}
                                placeholder="Cetak poster tambahan"
                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Nominal Realisasi
                            </span>
                            <input
                                type="number"
                                min="1"
                                value={data.amount}
                                placeholder="250000"
                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                onChange={(event) =>
                                    setData('amount', event.target.value)
                                }
                            />
                            <InputError
                                message={errors.amount}
                                className="mt-2"
                            />
                        </label>

                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Receipt
                            </span>
                            <input
                                type="file"
                                accept="application/pdf,image/jpeg,image/png"
                                className="mt-2 block w-full rounded-[4px] border border-[#e6edef] text-sm text-[#242934] file:mr-4 file:border-0 file:bg-[#f5f7fb] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#24695c]"
                                onChange={(event) =>
                                    setData(
                                        'receipt',
                                        event.target.files?.[0] ?? null,
                                    )
                                }
                            />
                            <InputError
                                message={errors.receipt}
                                className="mt-2"
                            />
                        </label>
                    </form>
                </VihoCard>

                <VihoCard
                    title="RAB Siap Direalisasi"
                    subtitle="Budget line approved dan realized yang bisa menerima transaksi."
                >
                    <div className="grid gap-3 md:grid-cols-2">
                        {readyBudgetLines.map((line) => (
                            <div
                                key={line.id}
                                className="rounded-[4px] border border-[#e6edef] p-4"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-semibold text-[#242934]">
                                            {line.name}
                                        </p>
                                        <p className="mt-1 text-xs font-medium text-[#59667a]">
                                            {line.projectName}
                                        </p>
                                    </div>
                                    <ReceiptText className="h-4 w-4 text-[#24695c]" />
                                </div>
                                <div className="mt-4 h-2 rounded-full bg-[#e6edef]">
                                    <div
                                        className="h-2 rounded-full bg-[#24695c]"
                                        style={{
                                            width: `${Math.min(
                                                100,
                                                Math.round(
                                                    (line.realizedAmount /
                                                        line.plannedAmount) *
                                                        100,
                                                ),
                                            )}%`,
                                        }}
                                    />
                                </div>
                                <p className="mt-3 text-xs font-semibold text-[#59667a]">
                                    {formatRupiah(line.realizedAmount)} dari{' '}
                                    {formatRupiah(line.plannedAmount)}
                                </p>
                            </div>
                        ))}
                    </div>
                </VihoCard>
            </div>

            <VihoCard
                title="Realisasi Anggaran"
                subtitle="Tabel awal untuk transaksi, bukti, dan validasi realisasi."
                className="mt-6"
            >
                <VihoDataTable
                    columns={[
                        { key: 'transaction', label: 'Transaction' },
                        { key: 'budget', label: 'Budget Item' },
                        { key: 'spent', label: 'Spent', align: 'right' },
                        { key: 'receipt', label: 'Receipt' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
