import { Link } from '@inertiajs/react';

import FinanceSummaryWidget from '@/Components/Dashboard/Widgets/FinanceSummaryWidget';
import KpiCardGrid from '@/Components/Dashboard/Widgets/KpiCardGrid';
import ProjectListWidget from '@/Components/Dashboard/Widgets/ProjectListWidget';
import SimpleListWidget from '@/Components/Dashboard/Widgets/SimpleListWidget';
import type { PimpinanPayload } from '@/types/dashboard';

export default function PimpinanDashboard({
    payload,
}: {
    payload: PimpinanPayload;
}) {
    return (
        <div className="space-y-6">
            <QuickActions
                actions={[
                    ['Buat Proker Baru', route('proker.create')],
                    ['Undang Anggota', route('members.invites')],
                    ['Lihat Semua Approval', route('finance.approval')],
                ]}
            />
            <KpiCardGrid metrics={payload.kpiMetrics} />
            <SimpleListWidget
                title="Antrian Approval"
                emptyText="Tidak ada approval aktif."
                items={payload.approvalQueue.map((item) => ({
                    id: item.id,
                    title: item.type,
                    projectName: item.prokerName,
                    meta: `Diajukan oleh ${item.submittedBy}`,
                    status: 'pending',
                }))}
            />
            <div className="grid gap-6 xl:grid-cols-2">
                <ProjectListWidget
                    title="Proker Prioritas"
                    projects={payload.priorityProjects}
                />
                <SimpleListWidget
                    title="Aktivitas Anggota"
                    emptyText="Belum ada aktivitas anggota."
                    items={payload.memberActivity}
                />
                <FinanceSummaryWidget
                    title="Ringkasan Keuangan"
                    items={payload.financeSummary}
                />
                <SimpleListWidget
                    title="Agenda Rapat Mendatang"
                    emptyText="Belum ada rapat mendatang."
                    items={payload.upcomingMeetings}
                />
            </div>
        </div>
    );
}

export function QuickActions({
    actions,
}: {
    actions: Array<[string, string]>;
}) {
    return (
        <div className="flex flex-wrap gap-2">
            {actions.map(([label, href]) => (
                <Link
                    key={label}
                    href={href}
                    className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                >
                    {label}
                </Link>
            ))}
        </div>
    );
}
