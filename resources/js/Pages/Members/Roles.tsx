import { ShieldCheck } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { RolePermissionSummary } from '@/types/prokerin';
import { Head } from '@inertiajs/react';

interface MemberRolesProps {
    rolePermissions: RolePermissionSummary[];
}

export default function MemberRoles({ rolePermissions }: MemberRolesProps) {
    const rows = rolePermissions.map((role) => ({
        access: role.permissions.length.toString(),
        role: role.role,
        scope: role.scope === 'organization' ? 'Organization' : 'Project',
        status: role.isSystemRole ? 'System' : 'Active',
    }));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M03 · Roles
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Role Matrix
                    </h1>
                </div>
            }
        >
            <Head title="Role Matrix" />

            <VihoCard
                title="Roles & Permissions"
                subtitle="Role mengikuti CLAUDE.md. Enforcement backend nanti via Policy + Spatie Permission."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                    >
                        <ShieldCheck className="h-4 w-4" />
                        Policy First
                    </button>
                }
            >
                <VihoDataTable
                    columns={[
                        { key: 'role', label: 'Role' },
                        { key: 'scope', label: 'Scope' },
                        { key: 'access', label: 'Permissions', align: 'right' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
