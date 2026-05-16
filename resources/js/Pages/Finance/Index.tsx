import { ReceiptText } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function FinanceIndex() {
    return (
        <ModuleOverview
            title="RAB & Finance"
            eyebrow="M07 · Finance"
            description="Pantau draft anggaran, realisasi, bukti transaksi, dan approval bendahara dengan pola tabel ringkas seperti dashboard Viho."
            actionLabel="Tambah RAB"
            actionHref={route('finance.budget-draft')}
            icon={ReceiptText}
            metrics={[
                { label: 'Draft RAB', value: 'Rp42,8jt', note: 'Total pengajuan aktif' },
                { label: 'Realization', value: 'Rp18,4jt', note: 'Sudah tercatat' },
                { label: 'Approval', value: '3', note: 'Menunggu bendahara' },
            ]}
            items={[
                {
                    title: 'Konsumsi peserta seminar',
                    meta: 'Rp6.500.000 · 250 pax',
                    status: 'Approval',
                    progress: 65,
                    href: route('finance.approval'),
                },
                {
                    title: 'Sewa aula dan sound system',
                    meta: 'Rp8.250.000 · Vendor eksternal',
                    status: 'Draft',
                    progress: 35,
                    href: route('finance.budget-draft'),
                },
                {
                    title: 'Publikasi dan printing',
                    meta: 'Rp1.750.000 · Media kit',
                    status: 'Approved',
                    progress: 100,
                    href: route('finance.realization'),
                },
            ]}
            focus={[
                'Pisahkan RAB planning dan finance realization.',
                'File bukti transaksi harus lewat storage private dan signed URL.',
                'Semua angka finance wajib dihitung server-side.',
            ]}
        />
    );
}
