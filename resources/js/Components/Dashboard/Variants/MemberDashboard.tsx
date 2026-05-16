import KpiCardGrid from '@/Components/Dashboard/Widgets/KpiCardGrid';
import ProjectListWidget from '@/Components/Dashboard/Widgets/ProjectListWidget';
import SimpleListWidget from '@/Components/Dashboard/Widgets/SimpleListWidget';
import { QuickActions } from '@/Components/Dashboard/Variants/PimpinanDashboard';
import type { MemberPayload } from '@/types/dashboard';

export default function MemberDashboard({
    payload,
    isViewer = false,
}: {
    payload: MemberPayload;
    isViewer?: boolean;
}) {
    return (
        <div className="space-y-6">
            {isViewer ? (
                <div className="rounded-[4px] border border-[#e6edef] bg-white p-4 text-sm font-semibold text-[#59667a]">
                    Kamu memiliki akses viewer di organisasi ini — hanya dapat
                    melihat data.
                </div>
            ) : (
                <QuickActions
                    actions={[
                        ['Lihat Semua Task Saya', route('tasks.kanban')],
                        ['Lihat Proker', route('proker.index')],
                    ]}
                />
            )}
            <KpiCardGrid metrics={payload.kpiMetrics} />
            <SimpleListWidget
                title="Task Saya"
                emptyText="Belum ada task untuk kamu."
                items={payload.myTasks}
            />
            <div className="grid gap-6 xl:grid-cols-2">
                <ProjectListWidget
                    title="Proker yang Aku Ikuti"
                    projects={payload.myProjects}
                />
                <SimpleListWidget
                    title="Notifikasi Terbaru"
                    emptyText="Belum ada notifikasi."
                    items={payload.recentNotifications}
                />
            </div>
        </div>
    );
}
