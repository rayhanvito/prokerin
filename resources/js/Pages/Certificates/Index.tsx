import { Head, Link } from '@inertiajs/react';
import { Award, CheckCircle2, Download, ExternalLink, FileText } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface CertificateMetric {
    label: string;
    value: string;
    note: string;
}

interface CertificateItem {
    id: number;
    certificateNumber: string;
    recipientName: string;
    recipientEmail: string | null;
    templateName: string;
    projectName: string | null;
    meetingTitle: string | null;
    issuedAt: string;
    hasPdf: boolean;
    verifyUrl: string;
    downloadUrl: string;
}

interface CertificateIndexProps {
    metrics: CertificateMetric[];
    certificates: CertificateItem[];
    canIssue: boolean;
}

export default function CertificateIndex({
    metrics,
    certificates,
    canIssue,
}: CertificateIndexProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Credential
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Sertifikat Digital
                    </h1>
                </div>
            }
        >
            <Head title="Sertifikat Digital" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div className="flex gap-4">
                            <span className="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <Award className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-xl font-semibold text-[#242934]">
                                    Sertifikat terverifikasi untuk proker dan rapat
                                </h2>
                                <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                    Terbitkan sertifikat batch, simpan PDF di
                                    storage privat, dan bagikan URL verifikasi
                                    publik tanpa membuka data internal.
                                </p>
                            </div>
                        </div>

                        {canIssue && (
                            <Link
                                href={route('certificates.issue')}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white shadow-sm"
                            >
                                <FileText className="h-4 w-4" />
                                Issue Batch
                            </Link>
                        )}
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-3">
                    {metrics.map((metric) => (
                        <VihoCard key={metric.label}>
                            <p className="text-sm font-medium text-[#59667a]">
                                {metric.label}
                            </p>
                            <p className="mt-3 text-3xl font-semibold text-[#242934]">
                                {metric.value}
                            </p>
                            <p className="mt-3 text-sm text-[#717171]">
                                {metric.note}
                            </p>
                        </VihoCard>
                    ))}
                </section>

                <VihoCard
                    title="Sertifikat Terbit"
                    subtitle="Daftar terbaru dengan status PDF dan tautan verifikasi publik."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {certificates.map((certificate) => (
                            <div
                                key={certificate.id}
                                className="grid gap-4 p-5 xl:grid-cols-[1fr_auto] xl:items-center"
                            >
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="font-semibold text-[#242934]">
                                            {certificate.recipientName}
                                        </p>
                                        <VihoStatusBadge>
                                            {certificate.hasPdf
                                                ? 'PDF ready'
                                                : 'queued'}
                                        </VihoStatusBadge>
                                    </div>
                                    <p className="mt-2 text-sm text-[#717171]">
                                        {certificate.certificateNumber} ·{' '}
                                        {certificate.templateName} ·{' '}
                                        {certificate.issuedAt}
                                    </p>
                                    <p className="mt-2 text-sm text-[#59667a]">
                                        {certificate.projectName ??
                                            'Tanpa proker'}{' '}
                                        ·{' '}
                                        {certificate.meetingTitle ??
                                            'Tanpa rapat'}
                                    </p>
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    <Link
                                        href={certificate.verifyUrl}
                                        className="inline-flex h-9 items-center justify-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 text-sm font-semibold text-[#59667a] ring-1 ring-[#e6edef]"
                                    >
                                        <ExternalLink className="h-4 w-4" />
                                        Verify
                                    </Link>
                                    {certificate.hasPdf && (
                                        <Link
                                            href={certificate.downloadUrl}
                                            className="inline-flex h-9 items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-3 text-sm font-semibold text-white"
                                        >
                                            <Download className="h-4 w-4" />
                                            PDF
                                        </Link>
                                    )}
                                </div>
                            </div>
                        ))}

                        {certificates.length === 0 && (
                            <div className="flex items-center gap-3 p-5 text-sm font-medium text-[#59667a]">
                                <CheckCircle2 className="h-4 w-4 text-[#24695c]" />
                                Belum ada sertifikat yang diterbitkan.
                            </div>
                        )}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
