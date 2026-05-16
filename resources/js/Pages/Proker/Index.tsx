import { ListChecks } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';
import { projects } from '@/Data/workspaceMock';
import { humanizeStatus } from '@/lib/format';

export default function ProkerIndex() {
    return (
        <ModuleOverview
            title="Proker Management"
            eyebrow="M04 · Proker"
            description="Kelola daftar program kerja organisasi, status approval, PIC, dan kesiapan eksekusi dalam satu panel bergaya Viho."
            actionLabel="Buat Proker"
            actionHref={route('proker.create')}
            icon={ListChecks}
            metrics={[
                {
                    label: 'Total Proker',
                    value: String(projects.length),
                    note: 'Dalam periode aktif',
                },
                {
                    label: 'Review',
                    value: String(
                        projects.filter(
                            (project) => project.status === 'proposal_review',
                        ).length,
                    ),
                    note: 'Menunggu admin organisasi',
                },
                {
                    label: 'Ready',
                    value: String(
                        projects.filter((project) => project.progress >= 50)
                            .length,
                    ),
                    note: 'Siap masuk timeline',
                },
            ]}
            items={projects.map((project) => ({
                title: project.name,
                meta: `${project.organization} · ${project.owner} · ${project.deadline}`,
                status: humanizeStatus(project.status),
                progress: project.progress,
                href: route('proker.show'),
            }))}
            focus={[
                'Definisikan status flow proker dari draft sampai selesai.',
                'Siapkan form create/edit proker berbasis Form Request Laravel.',
                'Tambahkan scope organization_id saat query sudah memakai database.',
            ]}
        />
    );
}
