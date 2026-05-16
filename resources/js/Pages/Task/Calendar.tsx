import { CalendarDays } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { Head } from '@inertiajs/react';

interface CalendarDay {
    id: number;
    date: string;
    title: string;
    project: string;
    status: string;
}

interface TaskCalendarProps {
    days: CalendarDay[];
}

export default function TaskCalendar({ days }: TaskCalendarProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M06 · Calendar
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Task Calendar
                    </h1>
                </div>
            }
        >
            <Head title="Task Calendar" />

            <VihoCard
                title="Agenda Mei - Juni"
                subtitle="Kalender deadline task dan milestone proker."
            >
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {days.map((day) => (
                        <div
                            key={`${day.date}-${day.title}`}
                            className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4"
                        >
                            <div className="flex items-start gap-4">
                                <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-white text-xl font-semibold text-[#24695c] shadow-sm">
                                    {day.date}
                                </span>
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2">
                                        <CalendarDays className="h-4 w-4 text-[#717171]" />
                                        <VihoStatusBadge>
                                            {humanizeStatus(day.status)}
                                        </VihoStatusBadge>
                                    </div>
                                    <p className="mt-3 font-semibold text-[#242934]">
                                        {day.title}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {day.project}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
