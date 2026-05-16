import KpiCardGrid from '@/Components/Dashboard/Widgets/KpiCardGrid';
import ProjectListWidget from '@/Components/Dashboard/Widgets/ProjectListWidget';
import SimpleListWidget from '@/Components/Dashboard/Widgets/SimpleListWidget';
import { QuickActions } from '@/Components/Dashboard/Variants/PimpinanDashboard';
import type { OperasionalPayload } from '@/types/dashboard';

export default function OperasionalDashboard({
    payload,
}: {
    payload: OperasionalPayload;
}) {
    const firstProject = payload.myProjects[0];

    return (
        <div className="space-y-6">
            <QuickActions
                actions={[
                    ['Tambah Task', route('tasks.kanban')],
                    [
                        'Update Status Proker',
                        firstProject
                            ? route('proker.edit', firstProject.slug)
                            : route('proker.index'),
                    ],
                    ['Lihat Kanban Tim', route('tasks.kanban')],
                ]}
            />
            <KpiCardGrid metrics={payload.kpiMetrics} />
            <div className="grid gap-6 xl:grid-cols-2">
                <ProjectListWidget
                    title="Proker Saya"
                    projects={payload.myProjects}
                />
                <SimpleListWidget
                    title="Task Perlu Perhatian"
                    emptyText="Tidak ada task urgent."
                    items={payload.urgentTasks}
                />
                <SimpleListWidget
                    title="Deadline Mendatang"
                    emptyText="Belum ada milestone."
                    items={payload.upcomingMilestones}
                />
                <SimpleListWidget
                    title="Ringkasan Tim"
                    emptyText="Belum ada data tim."
                    items={payload.teamSummary.map((item) => ({
                        ...item,
                        title: String(item.memberName ?? '-'),
                        meta: `${item.projectName ?? '-'} · ${item.doneTasks ?? 0} selesai / ${item.openTasks ?? 0} terbuka`,
                    }))}
                />
            </div>
        </div>
    );
}
