import { router } from '@inertiajs/react';
import { Download } from 'lucide-react';

import type { MeetingItem } from '../Index';

interface Props {
    meeting: MeetingItem;
}

export default function MeetingExportSection({ meeting }: Props) {
    if (meeting.minutes === null || meeting.minutes.publishedAt === null) {
        return (
            <div className="rounded-[4px] bg-white p-4 text-sm text-[#717171] ring-1 ring-[#e6edef]">
                Export tersedia setelah notulen dipublish.
            </div>
        );
    }

    const queueExport = (format: 'pdf' | 'docx') => {
        router.post(
            route('meetings.exports.store', { meeting: meeting.id }),
            { format },
            { preserveScroll: true },
        );
    };

    return (
        <div className="rounded-[4px] bg-white p-4 ring-1 ring-[#e6edef]">
            <p className="text-sm font-semibold text-[#242934]">Export Notulen</p>
            <p className="mt-1 text-xs text-[#717171]">
                File akan di-generate di antrean dan tersedia di Document Exports.
            </p>
            <div className="mt-3 flex gap-2">
                <button
                    type="button"
                    onClick={() => queueExport('pdf')}
                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#1b4c43]"
                >
                    <Download className="h-3.5 w-3.5" /> Export PDF
                </button>
                <button
                    type="button"
                    onClick={() => queueExport('docx')}
                    className="inline-flex items-center gap-2 rounded-[4px] bg-white px-3 py-2 text-xs font-semibold text-[#24695c] ring-1 ring-[#24695c] hover:bg-[rgba(36,105,92,0.05)]"
                >
                    <Download className="h-3.5 w-3.5" /> Export DOCX
                </button>
            </div>
        </div>
    );
}
