import { Head, Link, router } from '@inertiajs/react';
import { Pencil, QrCode } from 'lucide-react';
import { useState } from 'react';

import LoanRequestModal from '@/Components/Inventory/LoanRequestModal';
import LoanReturnModal from '@/Components/Inventory/LoanReturnModal';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

import type { InventoryPayload } from './types';

export default function InventoryShow({
    item,
    loans,
    projects,
    canManage,
    options,
}: InventoryPayload) {
    const [loanOpen, setLoanOpen] = useState(false);
    const [returnLoanId, setReturnLoanId] = useState<number | null>(null);

    if (item === null) {
        return null;
    }

    const activeLoan = loans.find((loan) => loan.status === 'approved');

    return (
        <AuthenticatedLayout header={<h1>{item.name}</h1>}>
            <Head title={item.name} />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto]">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                {item.category} · {item.location ?? 'Lokasi belum diisi'}
                            </p>
                            <h2 className="mt-2 text-2xl font-semibold text-[#242934]">
                                {item.name}
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                {item.description ?? 'Belum ada deskripsi.'}
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                <VihoStatusBadge>{item.statusLabel}</VihoStatusBadge>
                                <VihoStatusBadge>{item.conditionLabel}</VihoStatusBadge>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2 lg:justify-end">
                            <button
                                type="button"
                                onClick={() => setLoanOpen(true)}
                                disabled={item.status !== 'available'}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                            >
                                Pinjam
                            </button>
                            {canManage && activeLoan && (
                                <button
                                    type="button"
                                    onClick={() => setReturnLoanId(activeLoan.id)}
                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                                >
                                    Catat Kembali
                                </button>
                            )}
                            {canManage && (
                                <Link
                                    href={route('inventory.edit', item.id)}
                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                                >
                                    <Pencil className="h-4 w-4" />
                                    Edit
                                </Link>
                            )}
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-6 lg:grid-cols-[1fr_320px]">
                    <VihoCard title="Riwayat Peminjaman">
                        {loans.length > 0 ? (
                            <div className="-m-5 divide-y divide-[#e6edef]">
                                {loans.map((loan) => (
                                    <div key={loan.id} className="p-5">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p className="font-semibold text-[#242934]">
                                                    {loan.borrowerName}
                                                </p>
                                                <p className="mt-1 text-sm text-[#59667a]">
                                                    {loan.projectName ?? 'Tanpa proker'} · kembali {loan.expectedReturnAt}
                                                </p>
                                            </div>
                                            <VihoStatusBadge>{loan.status}</VihoStatusBadge>
                                        </div>
                                        {canManage && loan.status === 'pending' && (
                                            <button
                                                type="button"
                                                onClick={() => router.patch(route('inventory.loans.approve', loan.id))}
                                                className="mt-3 rounded-[4px] bg-[#24695c] px-3 py-1.5 text-xs font-semibold text-white"
                                            >
                                                Approve
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-[#59667a]">
                                Belum ada riwayat peminjaman.
                            </p>
                        )}
                    </VihoCard>

                    <VihoCard title="QR Inventaris">
                        <div className="flex items-center gap-3 rounded-[4px] border border-dashed border-[#e6edef] p-4">
                            <QrCode className="h-8 w-8 text-[#24695c]" />
                            <div>
                                <p className="text-sm font-semibold text-[#242934]">
                                    {item.qrToken}
                                </p>
                                <p className="text-xs text-[#59667a]">
                                    Scan membuka {item.qrUrl}
                                </p>
                            </div>
                        </div>
                    </VihoCard>
                </section>
            </div>

            <LoanRequestModal
                itemId={item.id}
                projects={projects}
                open={loanOpen}
                onClose={() => setLoanOpen(false)}
            />
            {returnLoanId !== null && (
                <LoanReturnModal
                    loanId={returnLoanId}
                    options={options.returnConditions}
                    open
                    onClose={() => setReturnLoanId(null)}
                />
            )}
        </AuthenticatedLayout>
    );
}
