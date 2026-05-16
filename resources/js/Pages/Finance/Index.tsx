import { BarChart3, CircleAlert, ReceiptText, WalletCards } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';

interface FinanceMetric {
    label: string;
    value: number;
    note: string;
    tone: 'danger' | 'primary' | 'secondary' | 'success';
}

interface MonthlyRealization {
    label: string;
    amount: number;
}

interface TopCategory {
    category: string;
    plannedAmount: number;
    realizedAmount: number;
}

interface ReviewLine {
    id: number;
    name: string;
    projectName: string;
    category: string;
    amount: number;
    status: string;
}

interface FinanceIndexProps {
    metrics: FinanceMetric[];
    monthlyRealization: MonthlyRealization[];
    topCategories: TopCategory[];
    reviewLines: ReviewLine[];
}

const metricIcons = [ReceiptText, WalletCards, BarChart3, CircleAlert];

export default function FinanceIndex({
    metrics,
    monthlyRealization,
    topCategories,
    reviewLines,
}: FinanceIndexProps) {
    const maxMonthlyAmount = Math.max(
        1,
        ...monthlyRealization.map((month) => month.amount),
    );
    const maxCategoryAmount = Math.max(
        1,
        ...topCategories.map((category) => category.plannedAmount),
    );

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M07 · Finance
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        RAB & Finance
                    </h1>
                </div>
            }
        >
            <Head title="RAB & Finance" />

            <div className="space-y-5">
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((metric, index) => {
                        const Icon = metricIcons[index] ?? ReceiptText;

                        return (
                            <VihoCard key={metric.label}>
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                            {metric.label}
                                        </p>
                                        <p className="mt-2 text-xl font-semibold text-[#242934]">
                                            {metric.label === 'Review'
                                                ? String(metric.value)
                                                : formatRupiah(metric.value)}
                                        </p>
                                        <p className="mt-1 text-xs text-[#59667a]">
                                            {metric.note}
                                        </p>
                                    </div>
                                    <div
                                        className={cn(
                                            'flex h-10 w-10 items-center justify-center rounded-[4px]',
                                            metric.tone === 'danger'
                                                ? 'bg-[#d22d3d]/10 text-[#d22d3d]'
                                                : 'bg-[#24695c]/10 text-[#24695c]',
                                        )}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </div>
                                </div>
                            </VihoCard>
                        );
                    })}
                </div>

                <div className="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
                    <VihoCard
                        title="RAB vs Realisasi"
                        subtitle="Planned vs realized per kategori belanja."
                    >
                        {topCategories.length > 0 ? (
                            <div className="space-y-4">
                                {topCategories.map((category) => {
                                    const plannedRatio = Math.max(
                                        4,
                                        Math.round(
                                            (category.plannedAmount /
                                                maxCategoryAmount) *
                                                100,
                                        ),
                                    );
                                    const realizedRatio =
                                        category.plannedAmount === 0
                                            ? 0
                                            : Math.min(
                                                  100,
                                                  Math.round(
                                                      (category.realizedAmount /
                                                          category.plannedAmount) *
                                                          100,
                                                  ),
                                              );
                                    const overBudget =
                                        category.realizedAmount >
                                        category.plannedAmount;

                                    return (
                                        <div key={category.category}>
                                            <div className="mb-2 flex items-center justify-between gap-3 text-sm">
                                                <span className="font-semibold text-[#242934]">
                                                    {category.category}
                                                </span>
                                                <span
                                                    className={cn(
                                                        'text-xs font-medium',
                                                        overBudget
                                                            ? 'text-[#d22d3d]'
                                                            : 'text-[#59667a]',
                                                    )}
                                                >
                                                    {formatRupiah(
                                                        category.realizedAmount,
                                                    )}{' '}
                                                    /{' '}
                                                    {formatRupiah(
                                                        category.plannedAmount,
                                                    )}{' '}
                                                    ({realizedRatio}%)
                                                </span>
                                            </div>
                                            <div className="relative h-3 rounded-[4px] bg-[#f5f7fb]">
                                                <div
                                                    className="absolute left-0 top-0 h-3 rounded-[4px] bg-[#e6edef]"
                                                    style={{
                                                        width: `${plannedRatio}%`,
                                                    }}
                                                    aria-label="Planned"
                                                />
                                                <div
                                                    className={cn(
                                                        'absolute left-0 top-0 h-3 rounded-[4px]',
                                                        overBudget
                                                            ? 'bg-[#d22d3d]'
                                                            : 'bg-[#24695c]',
                                                    )}
                                                    style={{
                                                        width: `${Math.max(2, Math.round((category.realizedAmount / maxCategoryAmount) * 100))}%`,
                                                    }}
                                                    aria-label="Realized"
                                                />
                                            </div>
                                        </div>
                                    );
                                })}
                                <p className="mt-3 flex items-center gap-3 text-xs text-[#59667a]">
                                    <span className="inline-flex items-center gap-2">
                                        <span className="inline-block h-2 w-3 rounded-[2px] bg-[#e6edef]" />
                                        Planned
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <span className="inline-block h-2 w-3 rounded-[2px] bg-[#24695c]" />
                                        Realized
                                    </span>
                                    <span className="inline-flex items-center gap-2">
                                        <span className="inline-block h-2 w-3 rounded-[2px] bg-[#d22d3d]" />
                                        Over budget
                                    </span>
                                </p>
                            </div>
                        ) : (
                            <EmptyState
                                icon={ReceiptText}
                                title="Belum ada RAB"
                                description="Item RAB yang dibuat dari budget draft akan diringkas di sini."
                                action={{
                                    label: 'Buka Budget Draft',
                                    href: route('finance.budget-draft'),
                                }}
                            />
                        )}
                    </VihoCard>

                    <VihoCard
                        title="Realisasi Bulanan"
                        subtitle="Transaksi receipt yang sudah verified."
                    >
                        {monthlyRealization.length > 0 ? (
                            <div className="space-y-4">
                                {monthlyRealization.map((month) => (
                                    <div
                                        key={month.label}
                                        className="grid grid-cols-[80px_1fr] items-center gap-3 text-sm"
                                    >
                                        <span className="font-semibold text-[#242934]">
                                            {month.label}
                                        </span>
                                        <div>
                                            <div className="h-2 rounded-[4px] bg-[#f5f7fb]">
                                                <div
                                                    className="h-2 rounded-[4px] bg-[#ba895d]"
                                                    style={{
                                                        width: `${Math.max(4, Math.round((month.amount / maxMonthlyAmount) * 100))}%`,
                                                    }}
                                                />
                                            </div>
                                            <p className="mt-1 text-xs font-medium text-[#59667a]">
                                                {formatRupiah(month.amount)}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <EmptyState
                                icon={WalletCards}
                                title="Belum ada realisasi"
                                description="Receipt yang sudah verified akan muncul sebagai grafik bulanan."
                            />
                        )}
                    </VihoCard>
                </div>

                <VihoCard
                    title="Menunggu Approval"
                    subtitle="RAB review yang butuh keputusan finance."
                    action={
                        <Link
                            href={route('finance.approval')}
                            className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                        >
                            Approval Queue
                        </Link>
                    }
                >
                    {reviewLines.length > 0 ? (
                        <div className="divide-y divide-[#e6edef]">
                            {reviewLines.map((line) => (
                                <div
                                    key={line.id}
                                    className="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 md:flex-row md:items-center md:justify-between"
                                >
                                    <div>
                                        <p className="font-semibold text-[#242934]">
                                            {line.name}
                                        </p>
                                        <p className="mt-1 text-xs font-medium text-[#717171]">
                                            {line.projectName} · {line.category}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="text-sm font-semibold text-[#242934]">
                                            {formatRupiah(line.amount)}
                                        </span>
                                        <VihoStatusBadge tone="secondary">
                                            {humanizeStatus(line.status)}
                                        </VihoStatusBadge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon={CircleAlert}
                            title="Approval kosong"
                            description="Tidak ada RAB yang sedang menunggu keputusan."
                        />
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
