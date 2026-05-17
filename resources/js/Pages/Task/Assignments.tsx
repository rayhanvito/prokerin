import { UserCheck } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

interface AssignmentTask {
    id: number;
    title: string;
    project: string;
    status: string;
    dueAt: string | null;
    picUserId: number | null;
    picName: string;
}

interface AssignmentMember {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface TaskAssignmentsProps {
    tasks: AssignmentTask[];
    members: AssignmentMember[];
}

export default function TaskAssignments({
    tasks,
    members,
}: TaskAssignmentsProps) {
    const assignPic = (taskId: number, picUserId: string): void => {
        router.patch(
            route('tasks.pic.update', taskId),
            { pic_user_id: picUserId },
            { preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M06 · PIC Assignment
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        PIC Assignment
                    </h1>
                </div>
            }
        >
            <Head title="PIC Assignment" />

            <VihoCard
                title="Assignment Matrix"
                subtitle="Tabel task organisasi aktif dengan dropdown PIC."
                action={
                    <div className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c]/10 px-3 py-2 text-sm font-semibold text-[#24695c]">
                        <UserCheck className="h-4 w-4" />
                        {String(members.length)} member tersedia
                    </div>
                }
            >
                {tasks.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-[#e6edef] text-sm">
                            <thead>
                                <tr className="text-left text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                    <th className="px-3 py-3">Task</th>
                                    <th className="px-3 py-3">Proker</th>
                                    <th className="px-3 py-3">Status</th>
                                    <th className="px-3 py-3">Deadline</th>
                                    <th className="px-3 py-3">PIC</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#e6edef]">
                                {tasks.map((task) => (
                                    <tr key={task.id}>
                                        <td className="px-3 py-3 font-semibold text-[#242934]">
                                            {task.title}
                                        </td>
                                        <td className="px-3 py-3 text-[#59667a]">
                                            {task.project}
                                        </td>
                                        <td className="px-3 py-3">
                                            <VihoStatusBadge tone="secondary">
                                                {task.status.replace('_', ' ')}
                                            </VihoStatusBadge>
                                        </td>
                                        <td className="px-3 py-3 text-[#59667a]">
                                            {task.dueAt ?? '-'}
                                        </td>
                                        <td className="px-3 py-3">
                                            <select
                                                value={task.picUserId ?? ''}
                                                onChange={(event) =>
                                                    assignPic(
                                                        task.id,
                                                        event.target.value,
                                                    )
                                                }
                                                className="h-9 min-w-[220px] rounded-[4px] border border-[#e6edef] bg-white px-2 text-sm font-medium text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                                            >
                                                <option value="" disabled>
                                                    Pilih PIC
                                                </option>
                                                {members.map((member) => (
                                                    <option
                                                        key={member.id}
                                                        value={member.id}
                                                    >
                                                        {member.name} ·{' '}
                                                        {member.role.replace(
                                                            '_',
                                                            ' ',
                                                        )}
                                                    </option>
                                                ))}
                                            </select>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <EmptyState
                        icon={UserCheck}
                        title="Belum ada task"
                        description="Task yang dibuat dari quick-add atau proker akan muncul di sini untuk assign PIC."
                    />
                )}
            </VihoCard>
        </AuthenticatedLayout>
    );
}
