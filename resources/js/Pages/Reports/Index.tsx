import { FileText } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function ReportsIndex() {
    return (
        <ModuleOverview
            title="Proposal & LPJ"
            eyebrow="M08 · M10 · Documents"
            description="Siapkan generator proposal dan LPJ dari data proker, RAB, dokumentasi, dan checklist pertanggungjawaban."
            actionLabel="Buat Dokumen"
            actionHref={route('reports.proposal-editor')}
            icon={FileText}
            metrics={[
                { label: 'Proposal Draft', value: '5', note: 'Belum final' },
                { label: 'LPJ Pending', value: '2', note: 'Butuh upload bukti' },
                { label: 'Export Queue', value: '0', note: 'PDF/DOCX idle' },
            ]}
            items={[
                {
                    title: 'Proposal Seminar Karier Digital',
                    meta: 'Sekretaris · Draft v2',
                    status: 'Review',
                    progress: 76,
                    href: route('reports.proposal-editor'),
                },
                {
                    title: 'LPJ Workshop UI/UX HMIF',
                    meta: 'Bendahara · Bukti transaksi',
                    status: 'Pending',
                    progress: 48,
                    href: route('reports.lpj-checklist'),
                },
                {
                    title: 'Proposal Makrab Angkatan 2026',
                    meta: 'Ketua Pelaksana · Initial draft',
                    status: 'Draft',
                    progress: 30,
                    href: route('reports.export-queue'),
                },
            ]}
            focus={[
                'Export PDF/DOCX harus masuk queue.',
                'Editor proposal memakai data Inertia, bukan REST terpisah.',
                'Approval dokumen wajib lewat policy dan role organisasi.',
            ]}
        />
    );
}
