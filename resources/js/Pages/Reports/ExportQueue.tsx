import { DownloadCloud } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { ExportQueuePlan } from '@/types/prokerin';
import { Head } from '@inertiajs/react';

interface ExportQueueRow {
    document: string;
    type: string;
    requested: string;
    queue: string;
    status: string;
    plan: ExportQueuePlan;
}

interface ExportQueueProps {
    exportQueue: ExportQueueRow[];
}

export default function ExportQueue({ exportQueue }: ExportQueueProps) {
    const rows = exportQueue.map((item) => ({
        document: item.document,
        queue: item.queue,
        requested: item.requested,
        status: item.status,
        type: `${item.type} · ${item.plan.engine}`,
    }));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M08/M10 · Export
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Export Queue
                    </h1>
                </div>
            }
        >
            <Head title="Export Queue" />

            <VihoCard
                title="PDF / DOCX Queue"
                subtitle="Export nantinya wajib berjalan via queue worker, bukan request synchronous."
                action={
                    <button
                        type="button"
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                    >
                        <DownloadCloud className="h-4 w-4" />
                        Queue Export
                    </button>
                }
            >
                <VihoDataTable
                    columns={[
                        { key: 'document', label: 'Document' },
                        { key: 'type', label: 'Type' },
                        { key: 'requested', label: 'Requested By' },
                        { key: 'queue', label: 'Queue' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={rows}
                    statusKey="status"
                />
            </VihoCard>
        </AuthenticatedLayout>
    );
}
