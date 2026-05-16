import KpiCardGrid from '@/Components/Dashboard/Widgets/KpiCardGrid';
import SimpleListWidget from '@/Components/Dashboard/Widgets/SimpleListWidget';
import { QuickActions } from '@/Components/Dashboard/Variants/PimpinanDashboard';
import type { SekretarisPayload } from '@/types/dashboard';

export default function SekretarisDashboard({
    payload,
}: {
    payload: SekretarisPayload;
}) {
    return (
        <div className="space-y-6">
            <QuickActions
                actions={[
                    ['Buat Notulen', route('meetings.index')],
                    ['Upload Dokumen', route('documents.upload-center')],
                    ['Jadwalkan Rapat', route('meetings.index')],
                ]}
            />
            <KpiCardGrid metrics={payload.kpiMetrics} />
            <div className="grid gap-6 xl:grid-cols-2">
                <SimpleListWidget
                    title="Status Proposal"
                    emptyText="Belum ada proposal."
                    items={payload.proposalStatusOverview}
                />
                <SimpleListWidget
                    title="Checklist LPJ"
                    emptyText="Belum ada checklist LPJ."
                    items={payload.lpjChecklistOverview.map((item) => ({
                        ...item,
                        title: String(item.projectName ?? item.name ?? '-'),
                        meta: `${item.completed ?? 0}/${item.total ?? 0} item lengkap`,
                    }))}
                />
                <SimpleListWidget
                    title="Rapat & Notulen Pending"
                    emptyText="Semua notulen sudah tercatat."
                    items={payload.meetingsWithoutMinutes}
                />
                <SimpleListWidget
                    title="Undangan Anggota Pending"
                    emptyText="Tidak ada undangan pending."
                    items={payload.pendingInvitations}
                />
            </div>
            <SimpleListWidget
                title="Dokumen Terbaru"
                emptyText="Belum ada dokumen terbaru."
                items={payload.recentDocuments}
            />
        </div>
    );
}
