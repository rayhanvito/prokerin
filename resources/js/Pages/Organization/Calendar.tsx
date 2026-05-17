import { CalendarDays } from 'lucide-react';
import { Head, Link, router } from '@inertiajs/react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';

interface CalendarMetric {
    label: string;
    value: string;
    note: string;
}

interface CalendarEvent {
    id: number;
    type: 'project' | 'meeting' | 'attendance';
    title: string;
    startsAt: string;
    endsAt: string | null;
    link: string;
}

interface CalendarDay {
    date: string;
    dayNumber: number;
    inMonth: boolean;
}

interface OrganizationCalendarProps {
    month: string;
    metrics: CalendarMetric[];
    events: CalendarEvent[];
    focus: string[];
}

export default function OrganizationCalendar({
    month,
    metrics,
    events,
    focus,
}: OrganizationCalendarProps) {
    const days = buildCalendarDays(month);
    const monthTitle = formatMonthTitle(month);

    function visitMonth(nextMonth: string): void {
        router.get(
            route('organization.calendar'),
            { month: nextMonth },
            { preserveScroll: true, preserveState: true },
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Organization
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Period Calendar
                    </h1>
                </div>
            }
        >
            <Head title="Period Calendar" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <CalendarDays className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    {monthTitle}
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Kalender gabungan proker, meeting, dan sesi
                                    presensi untuk organisasi aktif.
                                </p>
                            </div>
                        </div>

                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={() => visitMonth(shiftMonth(month, -1))}
                                className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#59667a]"
                            >
                                Prev
                            </button>
                            <button
                                type="button"
                                onClick={() => visitMonth(shiftMonth(month, 1))}
                                className="rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#59667a]"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
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

                <section className="grid gap-6 xl:grid-cols-[1.45fr_0.85fr]">
                    <VihoCard
                        title="Kalender Bulanan"
                        subtitle="Dot event ditampilkan pada tanggal mulai agenda."
                    >
                        <div className="grid grid-cols-7 border-l border-t border-[#e6edef] text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                            {['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].map(
                                (day) => (
                                    <div
                                        key={day}
                                        className="border-b border-r border-[#e6edef] bg-[#f5f7fb] p-2 text-center"
                                    >
                                        {day}
                                    </div>
                                ),
                            )}
                        </div>
                        <div className="grid grid-cols-7 border-l border-[#e6edef]">
                            {days.map((day) => {
                                const dayEvents = events.filter(
                                    (event) =>
                                        event.startsAt.slice(0, 10) ===
                                        day.date,
                                );

                                return (
                                    <div
                                        key={day.date}
                                        className={cn(
                                            'min-h-28 border-b border-r border-[#e6edef] p-2',
                                            day.inMonth
                                                ? 'bg-white'
                                                : 'bg-[#f8fafb]',
                                        )}
                                    >
                                        <p
                                            className={cn(
                                                'text-sm font-semibold',
                                                day.inMonth
                                                    ? 'text-[#242934]'
                                                    : 'text-[#a0a7b1]',
                                            )}
                                        >
                                            {day.dayNumber}
                                        </p>
                                        <div className="mt-2 space-y-1">
                                            {dayEvents.slice(0, 3).map((event) => (
                                                <Link
                                                    key={`${event.type}-${event.id}`}
                                                    href={event.link}
                                                    title={event.title}
                                                    className={cn(
                                                        'block truncate rounded-[4px] px-2 py-1 text-[11px] font-semibold',
                                                        event.type === 'project' &&
                                                            'bg-[#24695c]/10 text-[#24695c]',
                                                        event.type === 'meeting' &&
                                                            'bg-[#ba895d]/10 text-[#8a633f]',
                                                        event.type === 'attendance' &&
                                                            'bg-[#1b4c43]/10 text-[#1b4c43]',
                                                    )}
                                                >
                                                    {event.title}
                                                </Link>
                                            ))}
                                            {dayEvents.length > 3 ? (
                                                <span className="block text-[11px] font-semibold text-[#717171]">
                                                    +{dayEvents.length - 3} lagi
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </VihoCard>

                    <div className="space-y-6">
                        <VihoCard title="Agenda Bulan Ini">
                            {events.length === 0 ? (
                                <EmptyState
                                    icon={CalendarDays}
                                    title="Belum ada agenda"
                                    description="Proker, meeting, dan presensi yang terjadwal akan muncul otomatis di kalender organisasi."
                                />
                            ) : (
                                <div className="-m-5 divide-y divide-[#e6edef]">
                                    {events.map((event) => (
                                        <Link
                                            key={`${event.type}-${event.id}`}
                                            href={event.link}
                                            className="block p-5 transition hover:bg-[#f8fafb]"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <p className="font-semibold text-[#242934]">
                                                        {event.title}
                                                    </p>
                                                    <p className="mt-1 text-sm text-[#717171]">
                                                        {formatEventDate(event)}
                                                    </p>
                                                </div>
                                                <VihoStatusBadge>
                                                    {event.type}
                                                </VihoStatusBadge>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </VihoCard>

                        <VihoCard title="Fokus Berikutnya">
                            <div className="space-y-3">
                                {focus.map((item, index) => (
                                    <div
                                        key={item}
                                        className="flex gap-3 rounded-[4px] bg-[#f5f7fb] p-3"
                                    >
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-[#24695c] ring-1 ring-[#e6edef]">
                                            {index + 1}
                                        </span>
                                        <p className="text-sm font-medium leading-6 text-[#59667a]">
                                            {item}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </VihoCard>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function buildCalendarDays(month: string): CalendarDay[] {
    const [year, monthNumber] = month.split('-').map(Number);
    const firstDay = new Date(year, monthNumber - 1, 1);
    const gridStart = new Date(firstDay);
    gridStart.setDate(firstDay.getDate() - firstDay.getDay());

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(gridStart);
        date.setDate(gridStart.getDate() + index);

        return {
            date: toDateKey(date),
            dayNumber: date.getDate(),
            inMonth: date.getMonth() === monthNumber - 1,
        };
    });
}

function shiftMonth(month: string, delta: number): string {
    const [year, monthNumber] = month.split('-').map(Number);
    const date = new Date(year, monthNumber - 1 + delta, 1);

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

function formatMonthTitle(month: string): string {
    const [year, monthNumber] = month.split('-').map(Number);

    return new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric',
    }).format(new Date(year, monthNumber - 1, 1));
}

function formatEventDate(event: CalendarEvent): string {
    const start = event.startsAt.slice(0, 10);
    const end = event.endsAt?.slice(0, 10);

    return end && end !== start ? `${start} - ${end}` : start;
}

function toDateKey(date: Date): string {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}
