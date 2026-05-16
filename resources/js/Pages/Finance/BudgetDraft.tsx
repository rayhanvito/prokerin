import { ReceiptText } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import { budgetItems } from '@/Data/workspaceMock';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head } from '@inertiajs/react';

export default function BudgetDraft() {
    const rows = budgetItems.map((item) => ({
        amount: formatRupiah(item.amount),
        category: item.category,
        item: item.item,
        owner: item.owner,
        status: humanizeStatus(item.status),
    }));

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

            <VihoCard
                title="RAB Planning"
                subtitle="Tabel draft anggaran sebelum approval bendahara."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <ReceiptText className="h-4 w-4" />
                        Tambah Item
                    </button>
                }
            >
                <VihoDataTable
                    columns={[
                        { key: 'item', label: 'Item' },
                        { key: 'category', label: 'Category' },
                        { key: 'amount', label: 'Amount', align: 'right' },
                        { key: 'owner', label: 'Owner' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
