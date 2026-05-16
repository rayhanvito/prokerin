import { Head, useForm } from '@inertiajs/react';
import { FileText, Layers3 } from 'lucide-react';
import type { FormEvent } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface CertificateTemplate {
    id: number;
    name: string;
    description: string | null;
    isActive: boolean;
    issuedCount: number;
}

interface CertificateTemplatesProps {
    templates: CertificateTemplate[];
    canIssue: boolean;
}

const DEFAULT_TEMPLATE =
    '<h1>Sertifikat Penghargaan</h1><p class="meta">Nomor: {{certificate_number}}</p><p>Diberikan kepada</p><p class="recipient">{{recipient_name}}</p><p>atas partisipasi dalam {{project_name}} yang diselenggarakan oleh {{organization_name}}.</p><div class="signature"><p>{{signature_label}}</p><strong>{{signature_name}}</strong></div><p class="meta">Verifikasi: {{verification_url}}</p>';

export default function CertificateTemplates({
    templates,
    canIssue,
}: CertificateTemplatesProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
        template_html: DEFAULT_TEMPLATE,
        signature_label: '',
        signature_name: '',
        is_active: true as boolean,
    });

    const submitTemplate = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        post(route('certificates.templates.store'), {
            preserveScroll: true,
            onSuccess: () => reset('name', 'description', 'signature_label', 'signature_name'),
        });
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
                <VihoCard
                    title="Template Aktif"
                    subtitle="Template tenant-scoped yang siap dipakai batch issue."
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {templates.map((template) => (
                            <div key={template.id} className="p-5">
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
                                <p className="mt-4 text-xs font-semibold text-[#59667a]">
                                    {template.issuedCount} sertifikat diterbitkan
                                </p>
                            </div>
                        ))}
                    </div>
                </VihoCard>

                <VihoCard
                    title="Buat Template"
                    subtitle="Gunakan placeholder untuk nama penerima, nomor, proker, tanda tangan, dan URL verifikasi."
                >
                    <form onSubmit={submitTemplate} className="space-y-4">
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
                            <div className="flex items-center gap-2 text-sm font-semibold text-[#59667a]">
                                <FileText className="h-4 w-4" />
                                Template aktif setelah dibuat
                            </div>
                            <PrimaryButton disabled={processing || !canIssue}>
                                Simpan Template
                            </PrimaryButton>
                        </div>
                    </form>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
