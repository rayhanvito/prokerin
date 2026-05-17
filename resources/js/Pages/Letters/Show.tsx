import { Head, router } from '@inertiajs/react';
import { Download, Send, Signature } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface LetterDetailProps {
    letter: {
        id: number;
        letterNumber: string;
        subject: string;
        recipientName: string;
        recipientOrganization: string | null;
        statusLabel: string;
        canSign: boolean;
        canDownload: boolean;
        downloadUrl: string | null;
    };
    previewHtml: string;
}

export default function LetterShow({ letter, previewHtml }: LetterDetailProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M39 · Detail Surat
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {letter.subject}
                    </h1>
                </div>
            }
        >
            <Head title={letter.subject} />

            <div className="grid gap-6 xl:grid-cols-[1fr_340px]">
                <VihoCard title="Preview Surat">
                    <div
                        className="prose prose-sm max-w-none rounded-[4px] bg-white p-6 text-[#242934] ring-1 ring-[#e6edef]"
                        dangerouslySetInnerHTML={{ __html: previewHtml }}
                    />
                </VihoCard>
                <aside className="space-y-6">
                    <VihoCard title="Status">
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                            Nomor
                        </p>
                        <p className="mt-1 font-semibold text-[#242934]">
                            {letter.letterNumber}
                        </p>
                        <p className="mt-4 text-sm text-[#59667a]">
                            {letter.recipientName}
                            {letter.recipientOrganization
                                ? ` · ${letter.recipientOrganization}`
                                : ''}
                        </p>
                        <div className="mt-4">
                            <VihoStatusBadge>{letter.statusLabel}</VihoStatusBadge>
                        </div>
                    </VihoCard>
                    <VihoCard title="Aksi">
                        <div className="space-y-3">
                            <button
                                type="button"
                                onClick={() =>
                                    router.post(
                                        route('letters.submit', letter.id),
                                        {},
                                        { preserveScroll: true },
                                    )
                                }
                                className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                            >
                                <Send className="h-4 w-4" />
                                Submit for Signing
                            </button>
                            {letter.canSign ? (
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.post(
                                            route('letters.sign', letter.id),
                                            {},
                                            { preserveScroll: true },
                                        )
                                    }
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                                >
                                    <Signature className="h-4 w-4" />
                                    Sign & Generate PDF
                                </button>
                            ) : null}
                            {letter.canDownload && letter.downloadUrl ? (
                                <a
                                    href={letter.downloadUrl}
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                                >
                                    <Download className="h-4 w-4" />
                                    Download PDF
                                </a>
                            ) : null}
                        </div>
                    </VihoCard>
                </aside>
            </div>
        </AuthenticatedLayout>
    );
}
