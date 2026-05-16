import { CalendarDays } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function OrganizationCalendar() {
    return (
        <ModuleOverview
            title="Period Calendar"
            eyebrow="Organization"
            description="Lihat kalender periode kepengurusan, jadwal proker, deadline proposal, dan milestone LPJ dalam satu kalender organisasi."
            actionLabel="Tambah Agenda"
            actionHref={route('organization.periods')}
            icon={CalendarDays}
            metrics={[
                { label: 'Agenda', value: '19', note: 'Bulan ini' },
                { label: 'Deadline', value: '6', note: 'Proposal dan LPJ' },
                { label: 'Conflict', value: '2', note: 'Jadwal berdekatan' },
            ]}
            items={[
                {
                    title: 'Deadline proposal Seminar Karier',
                    meta: '22 Mei · Sekretaris',
                    status: 'Deadline',
                    href: route('reports.proposal-editor'),
                },
                {
                    title: 'Technical meeting Workshop UI/UX',
                    meta: '26 Mei · Divisi Acara',
                    status: 'Meeting',
                    href: route('tasks.calendar'),
                },
                {
                    title: 'Upload LPJ kegiatan UKM Kreatif',
                    meta: '2 Jun · Ketua Pelaksana',
                    status: 'LPJ',
                    href: route('reports.lpj-checklist'),
                },
            ]}
            focus={[
                'Calendar view bisa menyusul setelah list agenda stabil.',
                'Cegah bentrok jadwal antar proker organisasi.',
                'Deadline generator harus muncul otomatis dari status flow.',
            ]}
        />
    );
}
