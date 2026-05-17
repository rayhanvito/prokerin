import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    CheckCircle2,
    Eye,
    FileText,
    Layers3,
    Pencil,
    Power,
    RotateCcw,
    X,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Breadcrumb from '@/Components/ui/Breadcrumb';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';

interface CertificateTemplate {
    id: number;
    name: string;
    description: string | null;
    templateHtml: string;
    signatureLabel: string | null;
    signatureName: string | null;
    isActive: boolean;
    issuedCount: number;
}

interface CertificateTemplatesProps {
    templates: CertificateTemplate[];
    canIssue: boolean;
    selectedTemplateId: number | null;
}

const DEFAULT_TEMPLATE =
    '<h1>Sertifikat Penghargaan</h1><p class="meta">Nomor: {{certificate_number}}</p><p>Diberikan kepada</p><p class="recipient">{{recipient_name}}</p><p>atas partisipasi dalam {{project_name}} yang diselenggarakan oleh {{organization_name}}.</p><div class="signature"><p>{{signature_label}}</p><strong>{{signature_name}}</strong></div><p class="meta">Verifikasi: {{verification_url}}</p>';

const PLACEHOLDERS = [
    { token: '{{recipient_name}}', sample: 'Contoh User' },
    { token: '{{certificate_number}}', sample: 'PRK-2026-BEMFT-0001' },
    { token: '{{project_name}}', sample: 'Seminar Karier Digital' },
    { token: '{{organization_name}}', sample: 'BEM Fakultas Teknologi' },
    { token: '{{meeting_title}}', sample: 'Technical Meeting Seminar Karier' },
    { token: '{{issued_at}}', sample: '17 Mei 2026' },
    { token: '{{signature_label}}', sample: 'Ketua Organisasi' },
    { token: '{{signature_name}}', sample: 'Dimas Aji' },
    { token: '{{verification_url}}', sample: 'https://prokerin.id/verify/demo' },
];

export default function CertificateTemplates({
    templates,
    canIssue,
    selectedTemplateId,
}: CertificateTemplatesProps) {
    const selectedTemplate = useMemo(
        () => templates.find((template) => template.id === selectedTemplateId) ?? null,
        [selectedTemplateId, templates],
    );
    const [togglingTemplateId, setTogglingTemplateId] = useState<number | null>(null);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        description: '',
        template_html: DEFAULT_TEMPLATE,
        signature_label: '',
        signature_name: '',
        is_active: true as boolean,
    });

    useEffect(() => {
        if (selectedTemplate === null) {
            setData({
                name: '',
                description: '',
                template_html: DEFAULT_TEMPLATE,
                signature_label: '',
                signature_name: '',
                is_active: true,
            });

            return;
        }

        setData({
            name: selectedTemplate.name,
            description: selectedTemplate.description ?? '',
            template_html: selectedTemplate.templateHtml,
            signature_label: selectedTemplate.signatureLabel ?? '',
            signature_name: selectedTemplate.signatureName ?? '',
            is_active: selectedTemplate.isActive,
        });
    }, [selectedTemplate]);

    const submitTemplate = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        if (selectedTemplate !== null) {
            put(route('certificates.templates.update', selectedTemplate.id), {
                preserveScroll: true,
            });

            return;
        }

        post(route('certificates.templates.store'), {
            preserveScroll: true,
            onSuccess: () => reset('name', 'description', 'signature_label', 'signature_name'),
        });
    };

    const toggleTemplateStatus = (template: CertificateTemplate): void => {
        setTogglingTemplateId(template.id);

        router.put(
            route('certificates.templates.update', template.id),
            {
                name: template.name,
                description: template.description ?? '',
                template_html: template.templateHtml,
                signature_label: template.signatureLabel ?? '',
                signature_name: template.signatureName ?? '',
                is_active: !template.isActive,
            },
            {
                preserveScroll: true,
                onFinish: () => setTogglingTemplateId(null),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Certificate
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Template Sertifikat
                    </h1>
                </div>
            }
        >
            <Head title="Template Sertifikat" />

            <Breadcrumb
                items={[
                    { label: 'Dashboard', href: route('dashboard') },
                    { label: 'Sertifikat', href: route('certificates.index') },
                    { label: 'Templates' },
                ]}
            />

            <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <VihoCard title="Template Aktif" subtitle="Template tenant-scoped yang siap dipakai batch issue.">
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {templates.map((template) => (
                            <div
                                key={template.id}
                                className={cn(
                                    'p-5',
                                    selectedTemplate?.id === template.id
                                        ? 'bg-[rgba(36,105,92,0.04)]'
                                        : '',
                                )}
                            >
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div className="flex items-center gap-3">
                                        <span className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                            <Layers3 className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <p className="font-semibold text-[#242934]">
                                                {template.name}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {template.description ??
                                                    'Tanpa deskripsi'}
                                            </p>
                                        </div>
                                    </div>
                                    <VihoStatusBadge>
                                        {template.isActive
                                            ? 'active'
                                            : 'inactive'}
                                    </VihoStatusBadge>
                                </div>
                                <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <p className="text-xs font-semibold text-[#59667a]">
                                        {template.issuedCount} sertifikat diterbitkan
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        <Link
                                            href={route('certificates.templates.edit', template.id)}
                                            preserveScroll
                                            className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] px-3 py-2 text-xs font-semibold text-[#242934] transition hover:border-[#24695c] hover:text-[#24695c]"
                                        >
                                            <Pencil className="h-3.5 w-3.5" />
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => toggleTemplateStatus(template)}
                                            disabled={!canIssue || togglingTemplateId === template.id}
                                            className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] px-3 py-2 text-xs font-semibold text-[#242934] transition hover:border-[#24695c] hover:text-[#24695c] disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <Power className="h-3.5 w-3.5" />
                                            {template.isActive ? 'Nonaktifkan' : 'Aktifkan'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </VihoCard>

                <VihoCard
                    title={selectedTemplate === null ? 'Buat Template' : 'Edit Template'}
                    subtitle="Gunakan placeholder untuk nama penerima, nomor, proker, tanda tangan, dan URL verifikasi."
                >
                    <form onSubmit={submitTemplate} className="space-y-4">
                        {selectedTemplate !== null && (
                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]">
                                <div className="flex items-center gap-2 text-sm font-semibold text-[#24695c]">
                                    <CheckCircle2 className="h-4 w-4" />
                                    Mode edit: {selectedTemplate.name}
                                </div>
                                <Link
                                    href={route('certificates.templates')}
                                    preserveScroll
                                    className="inline-flex items-center gap-2 text-sm font-semibold text-[#59667a] transition hover:text-[#24695c]"
                                >
                                    <RotateCcw className="h-4 w-4" />
                                    Buat baru
                                </Link>
                            </div>
                        )}

                        <div>
                            <label className="text-sm font-semibold text-[#242934]">
                                Nama template
                            </label>
                            <TextInput
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                className="mt-2 block w-full"
                                disabled={!canIssue}
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div>
                            <label className="text-sm font-semibold text-[#242934]">
                                Deskripsi
                            </label>
                            <textarea
                                value={data.description}
                                onChange={(event) =>
                                    setData('description', event.target.value)
                                }
                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm leading-6 text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                disabled={!canIssue}
                            />
                            <InputError
                                message={errors.description}
                                className="mt-2"
                            />
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="text-sm font-semibold text-[#242934]">
                                    Label tanda tangan
                                </label>
                                <TextInput
                                    value={data.signature_label}
                                    onChange={(event) =>
                                        setData(
                                            'signature_label',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-2 block w-full"
                                    disabled={!canIssue}
                                />
                                <InputError
                                    message={errors.signature_label}
                                    className="mt-2"
                                />
                            </div>
                            <div>
                                <label className="text-sm font-semibold text-[#242934]">
                                    Nama penanda tangan
                                </label>
                                <TextInput
                                    value={data.signature_name}
                                    onChange={(event) =>
                                        setData(
                                            'signature_name',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-2 block w-full"
                                    disabled={!canIssue}
                                />
                                <InputError
                                    message={errors.signature_name}
                                    className="mt-2"
                                />
                            </div>
                        </div>

                        <div>
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <label className="text-sm font-semibold text-[#242934]">
                                    HTML template
                                </label>
                                <button
                                    type="button"
                                    onClick={() => setIsPreviewOpen(true)}
                                    className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                                >
                                    <Eye className="h-3.5 w-3.5" />
                                    Preview Visual
                                </button>
                            </div>
                            <div className="mt-2 grid gap-4 lg:grid-cols-[1fr_260px]">
                                <textarea
                                    value={data.template_html}
                                    onChange={(event) =>
                                        setData('template_html', event.target.value)
                                    }
                                    className="block min-h-64 w-full rounded-[4px] border-[#e6edef] font-mono text-xs leading-6 text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    disabled={!canIssue}
                                />
                                <PlaceholderReference />
                            </div>
                            <InputError
                                message={errors.template_html}
                                className="mt-2"
                            />
                        </div>

                        <div className="flex items-center justify-between gap-3 rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]">
                            <label className="flex items-center gap-3 text-sm font-semibold text-[#59667a]">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(event) => setData('is_active', event.target.checked)}
                                    className="rounded-[4px] border-[#e6edef] text-[#24695c] focus:ring-[#24695c]"
                                    disabled={!canIssue}
                                />
                                <span className="inline-flex items-center gap-2">
                                    <FileText className="h-4 w-4" />
                                    Template aktif dan bisa dipakai issue batch
                                </span>
                            </label>
                            <PrimaryButton disabled={processing || !canIssue}>
                                {selectedTemplate === null ? 'Simpan Template' : 'Update Template'}
                            </PrimaryButton>
                        </div>
                    </form>

                    <TemplatePreview
                        templateHtml={data.template_html}
                        signatureLabel={data.signature_label}
                        signatureName={data.signature_name}
                        isOpen={isPreviewOpen}
                        onOpenChange={setIsPreviewOpen}
                    />
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function TemplatePreview({
    templateHtml,
    signatureLabel,
    signatureName,
    isOpen,
    onOpenChange,
}: {
    templateHtml: string;
    signatureLabel: string;
    signatureName: string;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const previewDocument = buildPreviewDocument(
        templateHtml,
        signatureLabel,
        signatureName,
    );

    return (
        <>
            <div className="mt-6 rounded-[4px] border border-[#e6edef] bg-white p-5">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-sm font-semibold text-[#242934]">
                            Preview visual
                        </p>
                        <p className="mt-1 text-sm text-[#717171]">
                            Render HTML dengan data contoh sertifikat.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => onOpenChange(true)}
                        className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                    >
                        <Eye className="h-4 w-4" />
                        Buka Preview
                    </button>
                </div>
                <iframe
                    title="Certificate visual preview"
                    sandbox=""
                    srcDoc={previewDocument}
                    className="mt-4 h-72 w-full rounded-[4px] border border-[#e6edef] bg-white"
                />
            </div>

            {isOpen ? (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="certificate-preview-title"
                    className="fixed inset-0 z-50 flex items-center justify-center bg-[#242934]/70 p-4"
                >
                    <div className="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-[4px] bg-white shadow-xl">
                        <div className="flex items-center justify-between gap-4 border-b border-[#e6edef] px-5 py-4">
                            <div>
                                <h2
                                    id="certificate-preview-title"
                                    className="text-base font-semibold text-[#242934]"
                                >
                                    Preview Sertifikat
                                </h2>
                                <p className="mt-1 text-sm text-[#717171]">
                                    Data contoh digunakan untuk menggantikan placeholder.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                aria-label="Tutup preview"
                                className="inline-flex h-9 w-9 items-center justify-center rounded-[4px] border border-[#e6edef] text-[#59667a] transition hover:border-[#d22d3d] hover:text-[#d22d3d]"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                        <iframe
                            title="Certificate modal visual preview"
                            sandbox=""
                            srcDoc={previewDocument}
                            className="h-[72vh] w-full bg-white"
                        />
                    </div>
                </div>
            ) : null}
        </>
    );
}

function PlaceholderReference() {
    return (
        <div className="rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4">
            <p className="text-sm font-semibold text-[#242934]">
                Placeholder
            </p>
            <div className="mt-3 space-y-2">
                {PLACEHOLDERS.map((placeholder) => (
                    <div
                        key={placeholder.token}
                        className="rounded-[4px] bg-white px-3 py-2 ring-1 ring-[#e6edef]"
                    >
                        <code className="block text-xs font-semibold text-[#24695c]">
                            {placeholder.token}
                        </code>
                        <span className="mt-1 block text-xs text-[#717171]">
                            {placeholder.sample}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function buildPreviewDocument(
    templateHtml: string,
    signatureLabel: string,
    signatureName: string,
): string {
    const previewHtml = replaceCertificatePlaceholders(
        templateHtml,
        signatureLabel,
        signatureName,
    );

    return `<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f5f7fb;
            color: #242934;
            font-family: Georgia, "Times New Roman", serif;
        }
        .certificate-preview-sheet {
            width: min(100%, 1040px);
            aspect-ratio: 1.414 / 1;
            padding: 54px;
            background: #fffdf7;
            border: 12px solid #24695c;
            outline: 2px solid #ba895d;
            outline-offset: -28px;
            box-shadow: 0 18px 48px rgba(36, 41, 52, 0.16);
            text-align: center;
            overflow: hidden;
        }
        h1 {
            margin: 16px 0 10px;
            color: #24695c;
            font-size: clamp(32px, 5vw, 58px);
            letter-spacing: 0;
            text-transform: uppercase;
        }
        p {
            margin: 12px auto;
            max-width: 760px;
            font-size: clamp(15px, 2vw, 22px);
            line-height: 1.65;
        }
        .recipient {
            color: #ba895d;
            font-size: clamp(30px, 5vw, 54px);
            font-weight: 700;
            border-bottom: 2px solid #e6edef;
            padding-bottom: 8px;
        }
        .meta {
            color: #59667a;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .signature {
            margin-top: 38px;
            margin-left: auto;
            width: min(300px, 44%);
        }
        .signature strong {
            display: block;
            margin-top: 26px;
            border-top: 1px solid #59667a;
            padding-top: 8px;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }
        @media (max-width: 720px) {
            .certificate-preview-sheet {
                aspect-ratio: auto;
                min-height: 560px;
                padding: 34px;
                border-width: 8px;
                outline-offset: -20px;
            }
            .signature {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="certificate-preview-sheet">${previewHtml || '<p class="meta">Preview akan muncul setelah HTML template diisi.</p>'}</main>
</body>
</html>`;
}

function replaceCertificatePlaceholders(
    templateHtml: string,
    signatureLabel: string,
    signatureName: string,
): string {
    return templateHtml
        .replaceAll('{{certificate_number}}', 'PRK-2026-BEMFT-0001')
        .replaceAll('{{recipient_name}}', 'Contoh User')
        .replaceAll('{{project_name}}', 'Seminar Karier Digital')
        .replaceAll('{{organization_name}}', 'BEM Fakultas Teknologi')
        .replaceAll('{{meeting_title}}', 'Technical Meeting Seminar Karier')
        .replaceAll('{{issued_at}}', '17 Mei 2026')
        .replaceAll('{{signature_label}}', signatureLabel || 'Ketua Organisasi')
        .replaceAll('{{signature_name}}', signatureName || 'Dimas Aji')
        .replaceAll('{{verification_url}}', 'https://prokerin.id/verify/demo');
}
