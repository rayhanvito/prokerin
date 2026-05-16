import FinanceSummaryWidget from '@/Components/Dashboard/Widgets/FinanceSummaryWidget';
import KpiCardGrid from '@/Components/Dashboard/Widgets/KpiCardGrid';
import SimpleListWidget from '@/Components/Dashboard/Widgets/SimpleListWidget';
import { QuickActions } from '@/Components/Dashboard/Variants/PimpinanDashboard';
import type { BendaharaPayload } from '@/types/dashboard';

export default function BendaharaDashboard({
    payload,
}: {
    payload: BendaharaPayload;
}) {
    return (
        <div className="space-y-6">
            <QuickActions
                actions={[
                    ['Lihat Semua Transaksi', route('finance.index')],
                    ['Export Laporan Keuangan', route('reports.export-queue')],
                    ['Tambah Transaksi', route('finance.realization')],
                ]}
            />
            <KpiCardGrid metrics={payload.kpiMetrics} />
            <div className="grid gap-6 xl:grid-cols-2">
                <SimpleListWidget
                    title="Antrian Approval Transaksi"
                    emptyText="Tidak ada transaksi menunggu approval."
                    items={payload.pendingTransactions}
                />
                <FinanceSummaryWidget
                    title="RAB vs Realisasi per Proker"
                    items={payload.rabVsRealisasiChart}
                />
                <FinanceSummaryWidget
                    title="Proker Mendekati Over Budget"
                    items={payload.overBudgetProjects}
                />
                <SimpleListWidget
                    title="Riwayat Transaksi Terbaru"
                    emptyText="Belum ada transaksi."
                    items={payload.recentTransactions}
                />
            </div>
        </div>
    );
}
