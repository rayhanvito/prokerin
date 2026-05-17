import { Head, Link, router } from '@inertiajs/react';
import { Search, Trash2, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

import ConfirmDialog from '@/Components/ui/ConfirmDialog';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface MemberMetric {
    label: string;
    value: string;
    note: string;
}

interface MemberRow {
    id: number;
    name: string;
    email: string;
    role: string;
    joinedAt: string | null;
    status: string;
}

interface RoleBreakdown {
    role: string;
    total: number;
}

interface MembersIndexProps {
    canManage: boolean;
    metrics: MemberMetric[];
    members: MemberRow[];
    roleBreakdown: RoleBreakdown[];
}

export default function MembersIndex({
    canManage,
    metrics,
    members,
    roleBreakdown,
}: MembersIndexProps) {
    const [search, setSearch] = useState('');
    const [role, setRole] = useState('all');
    const [selectedMember, setSelectedMember] = useState<MemberRow | null>(
        null,
    );
    const roles = useMemo(
        () => ['all', ...roleBreakdown.map((item) => item.role)],
        [roleBreakdown],
    );
    const filteredMembers = useMemo(
        () =>
            members.filter((member) => {
                const matchesRole = role === 'all' || member.role === role;
                const query = search.trim().toLowerCase();
                const matchesSearch =
                    query === '' ||
                    member.name.toLowerCase().includes(query) ||
                    member.email.toLowerCase().includes(query);

                return matchesRole && matchesSearch;
            }),
        [members, role, search],
    );

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M03 · Access
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Member & Role Management
                    </h1>
                </div>
            }
        >
            <Head title="Member & Role Management" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <Users className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Member & Role Management
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Kelola anggota organisasi, invitation, role,
                                    dan akses per proyek.
                                </p>
                            </div>
                        </div>
                        <Link
                            href={route('members.invites')}
                            className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                        >
                            Invite Member
                        </Link>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-4">
                    {metrics.map((metric) => (
                        <VihoCard key={metric.label}>
                            <p className="text-sm font-medium text-[#59667a]">
                                {metric.label}
                            </p>
                            <p className="mt-3 text-3xl font-semibold text-[#242934]">
                                {metric.value}
                            </p>
                            <p className="mt-3 text-sm text-[#717171]">
                                {metric.note}
                            </p>
                        </VihoCard>
                    ))}
                </section>

                <VihoCard
                    title="Daftar Anggota"
                    subtitle="Data anggota aktif dari organisasi yang sedang dipilih."
                >
                    <div className="mb-5 grid gap-3 md:grid-cols-[1fr_220px]">
                        <label className="relative block">
                            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#717171]" />
                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] pl-9 text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                placeholder="Cari nama atau email"
                            />
                        </label>
                        <select
                            value={role}
                            onChange={(event) => setRole(event.target.value)}
                            className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                        >
                            {roles.map((roleOption) => (
                                <option key={roleOption} value={roleOption}>
                                    {roleOption}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="-m-5 overflow-x-auto">
                        <table className="min-w-full border-collapse text-sm">
                            <thead>
                                <tr className="border-b border-[#e6edef] bg-[#f5f7fb] text-left text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                                    <th className="px-5 py-3">Name</th>
                                    <th className="px-5 py-3">Email</th>
                                    <th className="px-5 py-3">Role</th>
                                    <th className="px-5 py-3">Joined</th>
                                    <th className="px-5 py-3">Status</th>
                                    {canManage ? (
                                        <th className="px-5 py-3 text-right">
                                            Action
                                        </th>
                                    ) : null}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#e6edef] bg-white">
                                {filteredMembers.map((member) => (
                                    <tr key={member.id}>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {member.name}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 text-[#59667a]">
                                            {member.email}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                            {member.role}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 text-[#59667a]">
                                            {member.joinedAt ?? '-'}
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4">
                                            <VihoStatusBadge tone="success">
                                                {member.status}
                                            </VihoStatusBadge>
                                        </td>
                                        {canManage ? (
                                            <td className="whitespace-nowrap px-5 py-4 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setSelectedMember(member)
                                                    }
                                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#d22d3d] transition hover:bg-[#fff5f6]"
                                                >
                                                    <Trash2 className="h-3.5 w-3.5" />
                                                    Hapus
                                                </button>
                                            </td>
                                        ) : null}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </VihoCard>
            </div>

            <ConfirmDialog
                open={selectedMember !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedMember(null);
                    }
                }}
                title="Hapus anggota"
                description={
                    selectedMember
                        ? `${selectedMember.name} akan kehilangan akses ke organisasi aktif.`
                        : ''
                }
                confirmLabel="Hapus Anggota"
                confirmTone="danger"
                requireTypedPhrase={selectedMember?.name}
                onConfirm={() => {
                    if (selectedMember) {
                        router.delete(
                            route('organization.members.destroy', {
                                member: selectedMember.id,
                            }),
                            {
                                preserveScroll: true,
                                onSuccess: () => setSelectedMember(null),
                            },
                        );
                    }
                }}
            />
        </AuthenticatedLayout>
    );
}
