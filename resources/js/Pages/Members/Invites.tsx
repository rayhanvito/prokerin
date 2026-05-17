import { MailPlus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

import EmptyState from '@/Components/ui/EmptyState';
import FormField from '@/Components/ui/FormField';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface InvitationRow {
    id: number;
    email: string;
    role: string;
    organization: string;
    sent: string;
    expiresAt: string | null;
    status: string;
}

interface MemberInvitesProps {
    canManage: boolean;
    invitations: InvitationRow[];
}

interface InvitationFormData {
    email: string;
    role:
        | 'secretary'
        | 'treasurer'
        | 'project_lead'
        | 'division_coordinator'
        | 'member'
        | 'viewer';
}

const roleOptions: InvitationFormData['role'][] = [
    'secretary',
    'treasurer',
    'project_lead',
    'division_coordinator',
    'member',
    'viewer',
];

export default function MemberInvites({
    canManage,
    invitations,
}: MemberInvitesProps) {
    const [showForm, setShowForm] = useState(false);
    const form = useForm<InvitationFormData>({
        email: '',
        role: 'member',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        form.post(route('organization.invitations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setShowForm(false);
            },
        });
    };

    const rows = invitations.map((invitation) => ({
        email: invitation.email,
        role: invitation.role,
        organization: invitation.organization,
        sent: invitation.sent,
        status: invitation.status,
    }));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M03 · Invites
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Member Invites
                    </h1>
                </div>
            }
        >
            <Head title="Member Invites" />

            <VihoCard
                title="Invitation Queue"
                subtitle="Invite anggota dengan token, expiry, dan status accept/decline."
                action={
                    canManage ? (
                        <button
                            type="button"
                            onClick={() => setShowForm((current) => !current)}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <MailPlus className="h-4 w-4" />
                            Invite
                        </button>
                    ) : null
                }
            >
                {showForm ? (
                    <form
                        onSubmit={submit}
                        className="mb-5 rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-5"
                    >
                        <div className="grid gap-4 md:grid-cols-[1.4fr_0.8fr]">
                            <FormField
                                label="Email"
                                htmlFor="invite-email"
                                required
                                error={form.errors.email}
                            >
                                <input
                                    id="invite-email"
                                    type="email"
                                    value={form.data.email}
                                    onChange={(event) =>
                                        form.setData(
                                            'email',
                                            event.target.value,
                                        )
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </FormField>
                            <FormField
                                label="Role"
                                htmlFor="invite-role"
                                required
                                error={form.errors.role}
                            >
                                <select
                                    id="invite-role"
                                    value={form.data.role}
                                    onChange={(event) =>
                                        form.setData(
                                            'role',
                                            event.target
                                                .value as InvitationFormData['role'],
                                        )
                                    }
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                >
                                    {roleOptions.map((role) => (
                                        <option key={role} value={role}>
                                            {role}
                                        </option>
                                    ))}
                                </select>
                            </FormField>
                        </div>
                        <div className="mt-5 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => setShowForm(false)}
                                className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                            >
                                Kirim Invite
                            </button>
                        </div>
                    </form>
                ) : null}

                {rows.length === 0 ? (
                    <EmptyState
                        icon={MailPlus}
                        title="Belum ada invitation"
                        description="Invitation yang dikirim ke calon anggota akan muncul di antrean ini."
                        action={
                            canManage
                                ? {
                                      label: 'Invite Member',
                                      onClick: () => setShowForm(true),
                                  }
                                : undefined
                        }
                    />
                ) : (
                    <VihoDataTable
                        columns={[
                            { key: 'email', label: 'Email' },
                            { key: 'role', label: 'Role' },
                            { key: 'organization', label: 'Organization' },
                            { key: 'sent', label: 'Sent' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={rows}
                        statusKey="status"
                    />
                )}
            </VihoCard>
        </AuthenticatedLayout>
    );
}
