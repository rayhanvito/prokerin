import { Archive, CalendarDays, FileText, Globe2, ReceiptText, Users } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import Breadcrumb from '@/Components/ui/Breadcrumb';
import ConfirmDialog from '@/Components/ui/ConfirmDialog';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { Head, Link, router, useForm } from '@inertiajs/react';

interface ProjectDetail {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    progress: number;
    startsAt: string | null;
    endsAt: string | null;
    organization: string;
    lead: string | null;
}

interface ProjectMetric {
    label: string;
    value: string;
}

interface ProjectTaskRow extends Record<string, string> {
    task: string;
    pic: string;
    due: string;
    status: string;
}

interface ProkerShowProps {
    project: ProjectDetail;
    metrics: ProjectMetric[];
    tasks: ProjectTaskRow[];
    nextStatuses: Array<{
        value: string;
        label: string;
    }>;
    projectMembers: Array<{
        id: number;
        userId: number;
        name: string;
        email: string;
        role: string;
    }>;
    availableMembers: Array<{
        id: number;
        name: string;
        email: string;
    }>;
}

const metricIcons = [CalendarDays, Users, ReceiptText, FileText];

export default function ProkerShow({
    project,
    metrics,
    tasks,
    nextStatuses,
    projectMembers,
    availableMembers,
}: ProkerShowProps) {
    const [archiveOpen, setArchiveOpen] = useState(false);
    const [statusOpen, setStatusOpen] = useState(false);
    const [memberToRemove, setMemberToRemove] = useState<
        ProkerShowProps['projectMembers'][number] | null
    >(null);
    const { delete: destroy, processing } = useForm();
    const statusForm = useForm<{ status: string }>({
        status: nextStatuses[0]?.value ?? '',
    });
    const memberForm = useForm<{
        user_id: number | '';
        role: 'division_coordinator' | 'committee_member' | 'viewer';
    }>({
        user_id: availableMembers[0]?.id ?? '',
        role: 'committee_member',
    });

    const archiveProject = (): void => {
        destroy(route('proker.destroy', project.slug), {
            preserveScroll: true,
            onFinish: () => setArchiveOpen(false),
        });
    };
    const submitStatus: FormEventHandler = (event) => {
        event.preventDefault();

        if (!statusForm.data.status) {
            return;
        }

        setStatusOpen(true);
    };
    const confirmStatusChange = (): void => {
        statusForm.patch(route('proker.status.update', project.slug), {
            preserveScroll: true,
            onFinish: () => setStatusOpen(false),
        });
    };
    const submitMember: FormEventHandler = (event) => {
        event.preventDefault();

        if (memberForm.data.user_id === '') {
            return;
        }

        memberForm.post(route('proker.members.store', project.slug), {
            preserveScroll: true,
        });
    };
    const removeMember = (): void => {
        if (!memberToRemove) {
            return;
        }

        router.delete(
            route('proker.members.destroy', {
                project: project.slug,
                member: memberToRemove.id,
            }),
            {
                preserveScroll: true,
                onFinish: () => setMemberToRemove(null),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Detail Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {project.name}
                    </h1>
                </div>
            }
        >
            <Head title={project.name} />

            <Breadcrumb
                items={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Proker', href: route('proker.index') },
                    { label: project.name },
                ]}
            />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <div className="flex flex-wrap items-center gap-3">
                                <h2 className="text-2xl font-semibold text-[#242934]">
                                    {project.name}
                                </h2>
                                <VihoStatusBadge>
                                    {humanizeStatus(project.status)}
                                </VihoStatusBadge>
                            </div>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                {project.description ??
                                    'Draft proker belum memiliki deskripsi.'}
                            </p>
                            <p className="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                {project.organization} · PIC{' '}
                                {project.lead ?? '-'} · Progress{' '}
                                {project.progress}%
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {nextStatuses.length > 0 ? (
                                <form
                                    onSubmit={submitStatus}
                                    className="flex flex-wrap gap-2"
                                >
                                    <select
                                        value={statusForm.data.status}
                                        onChange={(event) =>
                                            statusForm.setData(
                                                'status',
                                                event.target.value,
                                            )
                                        }
                                        className="rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                    >
                                        {nextStatuses.map((status) => (
                                            <option
                                                key={status.value}
                                                value={status.value}
                                            >
                                                {status.label}
                                            </option>
                                        ))}
                                    </select>
                                    <button
                                        type="submit"
                                        disabled={statusForm.processing}
                                        className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c] transition hover:bg-[#f5f7fb] disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        Ubah Status
                                    </button>
                                </form>
                            ) : null}
                            <Link
                                href={route('proker.edit', project.slug)}
                                className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                            >
                                Edit Proker
                            </Link>
                            <Link
                                href={route('proker.microsite.edit', project.slug)}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c] transition hover:bg-[#f5f7fb]"
                            >
                                <Globe2 className="h-4 w-4" />
                                Microsite
                            </Link>
                            <button
                                type="button"
                                disabled={
                                    processing || project.status === 'archived'
                                }
                                onClick={() => setArchiveOpen(true)}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#d22d3d] hover:text-[#d22d3d] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Archive className="h-4 w-4" />
                                Arsipkan
                            </button>
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((metric, index) => {
                        const Icon = metricIcons[index] ?? CalendarDays;

                        return (
                            <VihoCard key={metric.label}>
                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <p className="mt-4 text-sm text-[#717171]">
                                    {metric.label}
                                </p>
                                <p className="mt-1 text-2xl font-semibold text-[#242934]">
                                    {metric.value}
                                </p>
                            </VihoCard>
                        );
                    })}
                </section>

                <VihoCard
                    title="Task Terdekat"
                    subtitle="Task ringkas yang tersambung ke execution view."
                >
                    <VihoDataTable
                        columns={[
                            { key: 'task', label: 'Task' },
                            { key: 'pic', label: 'PIC' },
                            { key: 'due', label: 'Due' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={tasks}
                        statusKey="status"
                    />
                </VihoCard>

                <VihoCard
                    title="Anggota Tim"
                    subtitle="PIC dan anggota pelaksana yang terhubung ke proker ini."
                >
                    <form
                        onSubmit={submitMember}
                        className="mb-5 grid gap-3 md:grid-cols-[1fr_220px_auto]"
                    >
                        <select
                            value={memberForm.data.user_id}
                            onChange={(event) =>
                                memberForm.setData(
                                    'user_id',
                                    Number(event.target.value),
                                )
                            }
                            className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                        >
                            {availableMembers.map((member) => (
                                <option key={member.id} value={member.id}>
                                    {member.name} · {member.email}
                                </option>
                            ))}
                        </select>
                        <select
                            value={memberForm.data.role}
                            onChange={(event) =>
                                memberForm.setData(
                                    'role',
                                    event.target
                                        .value as typeof memberForm.data.role,
                                )
                            }
                            className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                        >
                            <option value="division_coordinator">
                                Division Coordinator
                            </option>
                            <option value="committee_member">
                                Committee Member
                            </option>
                            <option value="viewer">Viewer</option>
                        </select>
                        <button
                            type="submit"
                            disabled={memberForm.processing}
                            className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                        >
                            Tambah Anggota
                        </button>
                    </form>

                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full border-collapse text-sm">
                            <thead>
                                <tr className="border-b border-[#e6edef] bg-[#f5f7fb] text-left text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                                    <th className="px-5 py-3">Name</th>
                                    <th className="px-5 py-3">Email</th>
                                    <th className="px-5 py-3">Role</th>
                                    <th className="px-5 py-3 text-right">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#e6edef] bg-white">
                                {projectMembers.map((member) => (
                                    <tr key={member.id}>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {member.name}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 text-[#59667a]">
                                            {member.email}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4">
                                            <VihoStatusBadge>
                                                {member.role}
                                            </VihoStatusBadge>
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 text-right">
                                            {member.role === 'project_lead' ? (
                                                <span className="text-xs font-semibold text-[#717171]">
                                                    Lead
                                                </span>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setMemberToRemove(
                                                            member,
                                                        )
                                                    }
                                                    className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#d22d3d] transition hover:bg-[#fff5f6]"
                                                >
                                                    Hapus
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </VihoCard>
            </div>
            <ConfirmDialog
                open={statusOpen}
                onOpenChange={setStatusOpen}
                title="Ubah status proker?"
                description="Status proker akan diperbarui mengikuti workflow yang berlaku."
                confirmLabel="Ubah Status"
                onConfirm={confirmStatusChange}
            />
            <ConfirmDialog
                open={archiveOpen}
                onOpenChange={setArchiveOpen}
                title="Arsipkan proker?"
                description="Data task, dokumen, RAB, proposal, dan LPJ tetap tersimpan, tetapi proker akan masuk status archived."
                confirmLabel="Arsipkan"
                confirmTone="danger"
                requireTypedPhrase={project.name}
                onConfirm={archiveProject}
            />
            <ConfirmDialog
                open={memberToRemove !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setMemberToRemove(null);
                    }
                }}
                title="Hapus anggota proker?"
                description={
                    memberToRemove
                        ? `${memberToRemove.name} akan dilepas dari tim proker ini.`
                        : 'Anggota akan dilepas dari tim proker ini.'
                }
                confirmLabel="Hapus Anggota"
                confirmTone="danger"
                requireTypedPhrase={memberToRemove?.name}
                onConfirm={removeMember}
            />
        </AuthenticatedLayout>
    );
}
