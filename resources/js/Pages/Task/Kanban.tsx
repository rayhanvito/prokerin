import { CheckSquare, Clock3, GripVertical } from 'lucide-react';

import TaskQuickAdd, {
    type TaskProjectOption,
} from '@/Components/Task/TaskQuickAdd';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { isOverdue } from '@/lib/dates';
import { cn } from '@/lib/utils';
import type { TaskStatus } from '@/types/prokerin';
import { Head, router } from '@inertiajs/react';

interface KanbanTask {
    id: number;
    title: string;
    project: string;
    pic: string;
    dueAt: string | null;
    status: TaskStatus;
    isOverdue: boolean;
}

interface KanbanColumn {
    status: TaskStatus;
    title: string;
    tasks: KanbanTask[];
}

interface TaskKanbanProps {
    columns: KanbanColumn[];
    projects: TaskProjectOption[];
}

const quickStatuses: TaskStatus[] = ['backlog', 'in_progress', 'review', 'done'];

export default function TaskKanban({ columns, projects }: TaskKanbanProps) {
    const updateStatus = (taskId: number, status: TaskStatus): void => {
        router.patch(
            route('tasks.status.update', taskId),
            { status },
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M06 · Kanban
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Task Kanban
                    </h1>
                </div>
            }
        >
            <Head title="Task Kanban" />

            <div className="mb-4">
                <VihoCard
                    title="Quick Add"
                    subtitle="Task baru masuk ke kolom Backlog."
                >
                    <TaskQuickAdd projects={projects} />
                </VihoCard>
            </div>

            <div className="grid gap-4 xl:grid-cols-3 2xl:grid-cols-6">
                {columns.map((column) => (
                    <VihoCard key={column.title} className="min-h-[520px]">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="font-semibold text-[#242934]">
                                {column.title}
                            </h2>
                            <VihoStatusBadge tone="muted">
                                {String(column.tasks.length)}
                            </VihoStatusBadge>
                        </div>

                        <div className="space-y-3">
                            {column.tasks.map((task, index) => (
                                <article
                                    key={task.id}
                                    className={cn(
                                        'rounded-[4px] border bg-[#f5f7fb] p-4',
                                        task.isOverdue || isOverdue(task.dueAt)
                                            ? 'border-[#d22d3d]/40'
                                            : 'border-[#e6edef]',
                                    )}
                                >
                                    <div className="flex items-start gap-3">
                                        <GripVertical className="mt-0.5 h-4 w-4 shrink-0 text-[#717171]" />
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-[#242934]">
                                                {task.title}
                                            </p>
                                            <p className="mt-1 text-xs font-medium text-[#717171]">
                                                {task.project}
                                            </p>
                                            <div className="mt-3 flex flex-wrap items-center gap-2 text-xs text-[#717171]">
                                                <span
                                                    className={cn(
                                                        'inline-flex items-center gap-1',
                                                        (task.isOverdue ||
                                                            isOverdue(
                                                                task.dueAt,
                                                            )) &&
                                                            'font-semibold text-[#d22d3d]',
                                                    )}
                                                >
                                                    <Clock3 className="h-3.5 w-3.5" />
                                                    {task.dueAt ?? `${index + 1} hari`}
                                                </span>
                                                <span className="inline-flex items-center gap-1">
                                                    <CheckSquare className="h-3.5 w-3.5" />
                                                    {task.pic}
                                                </span>
                                            </div>
                                            {task.isOverdue ||
                                            isOverdue(task.dueAt) ? (
                                                <div className="mt-3">
                                                    <VihoStatusBadge tone="danger">
                                                        Overdue
                                                    </VihoStatusBadge>
                                                </div>
                                            ) : null}
                                            <div className="mt-4 flex flex-wrap gap-1.5">
                                                {quickStatuses.map((status) => (
                                                    <button
                                                        key={status}
                                                        type="button"
                                                        disabled={
                                                            task.status ===
                                                            status
                                                        }
                                                        onClick={() =>
                                                            updateStatus(
                                                                task.id,
                                                                status,
                                                            )
                                                        }
                                                        className="rounded-[4px] border border-[#e6edef] bg-white px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c] disabled:cursor-not-allowed disabled:bg-[rgba(36,105,92,0.08)] disabled:text-[#24695c]"
                                                    >
                                                        {status.replace(
                                                            '_',
                                                            ' ',
                                                        )}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </VihoCard>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
