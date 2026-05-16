import { Head, Link, router, useForm } from '@inertiajs/react';
import { CheckCircle2, FileText, Layers3, Pencil, Power, RotateCcw } from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
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
                            <label className="text-sm font-semibold text-[#242934]">
                                HTML template
                            </label>
                            <textarea
                                value={data.template_html}
                                onChange={(event) =>
                                    setData('template_html', event.target.value)
                                }
                                className="mt-2 block min-h-56 w-full rounded-[4px] border-[#e6edef] font-mono text-xs leading-6 text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                disabled={!canIssue}
                            />
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
}: {
    templateHtml: string;
    signatureLabel: string;
    signatureName: string;
}) {
    const previewText = templateHtml
        .replaceAll('{{certificate_number}}', 'PRK-2026-DEMO-0001')
        .replaceAll('{{recipient_name}}', 'Nama Penerima')
        .replaceAll('{{project_name}}', 'Nama Proker')
        .replaceAll('{{organization_name}}', 'Nama Organisasi')
        .replaceAll('{{meeting_title}}', 'Judul Rapat')
        .replaceAll('{{issued_at}}', '2026-05-16')
        .replaceAll('{{signature_label}}', signatureLabel || 'Ketua Organisasi')
        .replaceAll('{{signature_name}}', signatureName || 'Nama Penanda Tangan')
        .replaceAll('{{verification_url}}', 'https://prokerin.id/verify/demo')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    return (
        <div className="mt-6 rounded-[4px] border border-[#e6edef] bg-white p-5">
            <p className="text-sm font-semibold text-[#242934]">Preview konten</p>
            <p className="mt-3 max-h-28 overflow-auto rounded-[4px] bg-[#f5f7fb] p-4 text-sm leading-7 text-[#59667a]">
                {previewText || 'Preview akan muncul setelah HTML template diisi.'}
            </p>
        </div>
    );
}
