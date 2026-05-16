import { Download, DownloadCloud } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { ExportQueuePlan } from '@/types/prokerin';
import { Head } from '@inertiajs/react';

interface ExportQueueRow {
    document: string;
    type: string;
    requested: string;
    queue: string;
    status: string;
    downloadUrl: string | null;
    plan: ExportQueuePlan;
}

interface ExportQueueProps {
    exportQueue: ExportQueueRow[];
}

export default function ExportQueue({ exportQueue }: ExportQueueProps) {
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
                subtitle="Export berjalan via queue worker. Status akan bergerak dari queued, processing, completed, atau failed."
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
                <div className="-m-5 overflow-x-auto">
                    <table className="min-w-full border-collapse text-sm">
                        <thead>
                            <tr className="border-b border-[#e6edef] bg-[#f5f7fb] text-left text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                                <th className="px-5 py-3">Document</th>
                                <th className="px-5 py-3">Type</th>
                                <th className="px-5 py-3">Requested By</th>
                                <th className="px-5 py-3">Queue</th>
                                <th className="px-5 py-3">Status</th>
                                <th className="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#e6edef] bg-white">
                            {exportQueue.map((item) => (
                                <tr key={`${item.document}-${item.plan.outputPath}`}>
                                    <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                        {item.document}
                                    </td>
                                    <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                        {item.type} · {item.plan.engine}
                                    </td>
                                    <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                        {item.requested}
                                    </td>
                                    <td className="whitespace-nowrap px-5 py-4 font-medium text-[#242934]">
                                        {item.queue}
                                    </td>
                                    <td className="whitespace-nowrap px-5 py-4">
                                        <VihoStatusBadge>
                                            {item.status}
                                        </VihoStatusBadge>
                                    </td>
                                    <td className="whitespace-nowrap px-5 py-4 text-right">
                                        {item.downloadUrl ? (
                                            <a
                                                href={item.downloadUrl}
                                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#24695c] px-3 py-2 text-xs font-semibold text-[#24695c] transition hover:bg-[#24695c] hover:text-white"
                                            >
                                                <Download className="h-4 w-4" />
                                                Download
                                            </a>
                                        ) : (
                                            <span className="text-xs font-semibold text-[#717171]">
                                                Waiting
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </VihoCard>
        </AuthenticatedLayout>
    );
}
