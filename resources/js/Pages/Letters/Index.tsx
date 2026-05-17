import { Head, Link } from '@inertiajs/react';
import { FileText, Plus, Settings } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface LetterRow {
    id: number;
    letterNumber: string;
    subject: string;
    typeLabel: string;
    recipientName: string;
    statusLabel: string;
    projectName: string | null;
    createdAt: string;
}

interface LettersIndexProps {
    metrics: {
        total: number;
        draft: number;
        submitted: number;
        signed: number;
    };
    letters: LetterRow[];
}

export default function LettersIndex({ metrics, letters }: LettersIndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M39 · Surat Menyurat
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Surat Menyurat
                    </h1>
                </div>
            }
        >
            <Head title="Surat Menyurat" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <h2 className="text-xl font-semibold text-[#242934]">
                                Generator Surat Organisasi
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                Buat surat resmi dengan nomor otomatis, template
                                standar, proses tanda tangan, dan PDF siap kirim.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href={route('letters.templates.index')}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                            >
                                <Settings className="h-4 w-4" />
                                Template
                            </Link>
                            <Link
                                href={route('letters.create')}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                            >
                                <Plus className="h-4 w-4" />
                                Buat Surat
                            </Link>
                        </div>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-4">
                    {[
                        ['Total', metrics.total],
                        ['Draft', metrics.draft],
                        ['Menunggu TTD', metrics.submitted],
                        ['Signed/Sent', metrics.signed],
                    ].map(([label, value]) => (
                        <div
                            key={label}
                            className="rounded-[4px] border border-[#e6edef] bg-white p-4 shadow-sm"
                        >
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                                {label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold text-[#242934]">
                                {value}
                            </p>
                        </div>
                    ))}
                </section>

                <VihoCard title="Daftar Surat">
                    {letters.length > 0 ? (
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {letters.map((letter) => (
                                <Link
                                    key={letter.id}
                                    href={route('letters.show', letter.id)}
                                    className="grid gap-4 p-5 transition hover:bg-[#f8fafb] lg:grid-cols-[1fr_160px] lg:items-center"
                                >
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                            {letter.letterNumber} · {letter.typeLabel}
                                        </p>
                                        <h2 className="mt-2 font-semibold text-[#242934]">
                                            {letter.subject}
                                        </h2>
                                        <p className="mt-1 text-sm text-[#59667a]">
                                            {letter.recipientName} ·{' '}
                                            {letter.projectName ?? 'Tanpa proker'} ·{' '}
                                            {letter.createdAt}
                                        </p>
                                    </div>
                                    <VihoStatusBadge>
                                        {letter.statusLabel}
                                    </VihoStatusBadge>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon={FileText}
                            title="Belum ada surat"
                            description="Mulai dari template default, lalu buat draft surat pertama untuk proker aktif."
                            action={{
                                label: 'Buat Surat',
                                href: route('letters.create'),
                            }}
                        />
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
