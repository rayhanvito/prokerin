import { CheckSquare } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function TaskIndex() {
    return (
        <ModuleOverview
            title="Timeline & Task"
            eyebrow="M06 · Execution"
            description="Susun milestone, task lintas divisi, deadline, dan PIC agar panitia bisa melihat pekerjaan yang perlu dituntaskan."
            actionLabel="Tambah Task"
            actionHref={route('tasks.assignments')}
            icon={CheckSquare}
            metrics={[
                { label: 'Open Task', value: '86', note: '11 deadline minggu ini' },
                { label: 'Overdue', value: '7', note: 'Butuh eskalasi PIC' },
                { label: 'Done', value: '124', note: 'Selesai periode ini' },
            ]}
            items={[
                {
                    title: 'Finalisasi rundown seminar',
                    meta: 'Divisi Acara · Seminar Karier Digital',
                    status: 'Today',
                    progress: 80,
                    href: route('tasks.kanban'),
                },
                {
                    title: 'Konfirmasi vendor konsumsi',
                    meta: 'Divisi Konsumsi · Workshop UI/UX',
                    status: 'Overdue',
                    progress: 45,
                    href: route('tasks.calendar'),
                },
                {
                    title: 'Briefing LO pembicara',
                    meta: 'Divisi Humas · Seminar Karier Digital',
                    status: 'Next',
                    progress: 60,
                    href: route('tasks.assignments'),
                },
            ]}
            focus={[
                'Bangun view list terlebih dahulu sebelum kanban/calendar.',
                'Pisahkan task, subtasks, dan assignment PIC di model berbeda.',
                'Siapkan notifikasi deadline sebagai job queue, bukan request sync.',
            ]}
        />
    );
}
