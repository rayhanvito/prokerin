import { Users } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function MembersIndex() {
    return (
        <ModuleOverview
            title="Member & Role Management"
            eyebrow="M03 · Access"
            description="Kelola anggota organisasi, invitation, role, dan akses per proyek agar data lintas organisasi tetap aman."
            actionLabel="Invite Member"
            actionHref={route('members.invites')}
            icon={Users}
            metrics={[
                { label: 'Members', value: '238', note: 'Aktif periode ini' },
                { label: 'Invites', value: '12', note: 'Belum diterima' },
                { label: 'Roles', value: '8', note: 'Mengikuti CLAUDE.md' },
            ]}
            items={[
                {
                    title: 'Nadia Putri',
                    meta: 'Secretary · BEM Fakultas',
                    status: 'Active',
                    href: route('members.roles'),
                },
                {
                    title: 'Raka Pratama',
                    meta: 'Treasurer · HIMA Informatika',
                    status: 'Active',
                    href: route('members.roles'),
                },
                {
                    title: 'Dimas Aji',
                    meta: 'Project Lead · Seminar Karier',
                    status: 'Project',
                    href: route('members.invites'),
                },
            ]}
            focus={[
                'Role organisasi dan role proyek dipisah.',
                'Semua akses dicek via Policy + Spatie Permission.',
                'Invitation harus punya expiry dan status accept.',
            ]}
        />
    );
}
