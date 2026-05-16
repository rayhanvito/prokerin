import { Bell, Mail, Send, Smartphone } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import type { NotificationChannel, NotificationEvent } from '@/types/prokerin';
import { Head } from '@inertiajs/react';

const channels = [
    {
        title: 'In-app',
        description: 'Deadline, approval, assignment, dan mention.',
        icon: Bell,
        status: 'Ready UI',
    },
    {
        title: 'Email',
        description: 'Reminder proposal, LPJ, dan undangan anggota.',
        icon: Mail,
        status: 'Queue later',
    },
    {
        title: 'Push',
        description: 'Cadangan untuk PWA notification post-MVP.',
        icon: Smartphone,
        status: 'Post-MVP',
    },
];

interface NotificationRulePayload {
    event: NotificationEvent;
    label: string;
    audience: string;
    channels: NotificationChannel[];
    trigger: string;
    status: 'planned' | 'active';
}

interface NotificationsIndexProps {
    notificationRules: NotificationRulePayload[];
}

export default function NotificationsIndex({
    notificationRules,
}: NotificationsIndexProps) {
    const rows = notificationRules.map((rule) => ({
        audience: rule.audience,
        channel: rule.channels.map((channel) => humanizeStatus(channel)).join(' + '),
        event: rule.label,
        status: humanizeStatus(rule.status),
        trigger: rule.trigger,
    }));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M12 · Notification Basic
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Notifications
                    </h1>
                </div>
            }
        >
            <Head title="Notifications" />

            <div className="space-y-6">
                <section className="grid gap-4 md:grid-cols-3">
                    {channels.map((channel) => {
                        const Icon = channel.icon;

                        return (
                            <VihoCard key={channel.title}>
                                <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-6 w-6" />
                                </span>
                                <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                                    {channel.title}
                                </h2>
                                <p className="mt-2 min-h-12 text-sm leading-6 text-[#59667a]">
                                    {channel.description}
                                </p>
                                <div className="mt-5">
                                    <VihoStatusBadge>
                                        {channel.status}
                                    </VihoStatusBadge>
                                </div>
                            </VihoCard>
                        );
                    })}
                </section>

                <VihoCard
                    title="Notification Rules"
                    subtitle="Rule UI awal. Delivery backend nanti memakai Laravel notifications dan queue."
                    action={
                        <button
                            type="button"
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <Send className="h-4 w-4" />
                            Simulate
                        </button>
                    }
                >
                    <VihoDataTable
                        columns={[
                            { key: 'event', label: 'Event' },
                            { key: 'audience', label: 'Audience' },
                            { key: 'channel', label: 'Channel' },
                            { key: 'trigger', label: 'Trigger' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={rows}
                        statusKey="status"
                    />
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
