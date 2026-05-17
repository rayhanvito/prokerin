import {
    CalendarClock,
    CheckCircle2,
    CheckSquare,
    CircleAlert,
    UserCheck,
} from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import TaskQuickAdd, {
    type TaskProjectOption,
} from '@/Components/Task/TaskQuickAdd';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';

interface TaskMetric {
    label: string;
    value: string;
    note: string;
    tone: 'danger' | 'primary' | 'success';
}

interface UrgentTask {
    id: number;
    title: string;
    project: string;
    pic: string;
    dueAt: string | null;
    status: string;
    isOverdue: boolean;
}

interface TaskIndexProps {
    metrics: TaskMetric[];
    urgentTasks: UrgentTask[];
    projects: TaskProjectOption[];
}

const metricIcons = [CheckSquare, CircleAlert, CheckCircle2];

export default function TaskIndex({
    metrics,
    urgentTasks,
    projects,
}: TaskIndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M06 · Execution
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Timeline & Task
                    </h1>
                </div>
            }
        >
            <Head title="Timeline & Task" />

            <div className="space-y-5">
                <div className="grid gap-4 md:grid-cols-3">
                    {metrics.map((metric, index) => {
                        const Icon = metricIcons[index] ?? CheckSquare;

                        return (
                            <VihoCard key={metric.label}>
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                            {metric.label}
                                        </p>
                                        <p className="mt-2 text-2xl font-semibold text-[#242934]">
                                            {metric.value}
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

                <VihoCard
                    title="Quick Add"
                    subtitle="Buat task baru langsung ke proker aktif."
                >
                    {projects.length > 0 ? (
                        <TaskQuickAdd projects={projects} />
                    ) : (
                        <EmptyState
                            icon={CalendarClock}
                            title="Belum ada proker aktif"
                            description="Task baru membutuhkan proker aktif sebagai tempat eksekusi."
                            action={{
                                label: 'Buat Proker',
                                href: route('proker.create'),
                            }}
                        />
                    )}
                </VihoCard>

                <VihoCard
                    title="Task Urgent"
                    subtitle="Lima task terdekat dari organisasi aktif."
                    action={
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href={route('tasks.kanban')}
                                className="inline-flex items-center justify-center rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                            >
                                Kanban
                            </Link>
                            <Link
                                href={route('tasks.assignments')}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                            >
                                <UserCheck className="h-4 w-4" />
                                PIC
                            </Link>
                        </div>
                    }
                >
                    {urgentTasks.length > 0 ? (
                        <div className="divide-y divide-[#e6edef]">
                            {urgentTasks.map((task) => (
                                <div
                                    key={task.id}
                                    className="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 md:flex-row md:items-center md:justify-between"
                                >
                                    <div>
                                        <p className="font-semibold text-[#242934]">
                                            {task.title}
                                        </p>
                                        <p className="mt-1 text-xs font-medium text-[#717171]">
                                            {task.project} · {task.pic}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <VihoStatusBadge
                                            tone={
                                                task.isOverdue
                                                    ? 'danger'
                                                    : 'muted'
                                            }
                                        >
                                            {task.dueAt ?? 'Tanpa deadline'}
                                        </VihoStatusBadge>
                                        <VihoStatusBadge tone="secondary">
                                            {task.status.replace('_', ' ')}
                                        </VihoStatusBadge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon={CheckCircle2}
                            title="Tidak ada task urgent"
                            description="Semua task aktif sedang aman atau belum ada deadline baru."
                        />
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
