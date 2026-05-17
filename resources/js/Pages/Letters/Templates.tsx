import { Head, router, useForm } from '@inertiajs/react';
import { Save, Trash2 } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface LetterTemplate {
    id: number;
    name: string;
    letterTypeLabel: string;
    numberingPattern: string;
    signatoryName: string | null;
}

interface MemberOption {
    id: number;
    name: string;
    role: string;
}

interface TypeOption {
    value: string;
    label: string;
}

interface TemplatesProps {
    templates: LetterTemplate[];
    members: MemberOption[];
    types: TypeOption[];
}

export default function Templates({ templates, members, types }: TemplatesProps) {
    const form = useForm({
        name: '',
        letter_type: types[0]?.value ?? 'custom',
        template_html: '<p>Nomor: {{letter_number}}</p><p>{{letter_subject}}</p><p>Yth. {{recipient_name}}</p>',
        numbering_pattern: 'B.{seq}/{type_code}/{roman_month}/{year}',
        signatory_user_id: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            signatory_user_id:
                data.signatory_user_id === ''
                    ? null
                    : Number(data.signatory_user_id),
        }));
        form.post(route('letters.templates.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset('name'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M39 · Template Surat
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Template Surat
                    </h1>
                </div>
            }
        >
            <Head title="Template Surat" />

            <div className="grid gap-6 xl:grid-cols-[420px_1fr]">
                <VihoCard title="Template Baru" subtitle="Gunakan placeholder standar M39.">
                    <form onSubmit={submit} className="space-y-4">
                        <Field label="Nama" error={form.errors.name}>
                            <input
                                value={form.data.name}
                                onChange={(event) =>
                                    form.setData('name', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            />
                        </Field>
                        <Field label="Jenis" error={form.errors.letter_type}>
                            <select
                                value={form.data.letter_type}
                                onChange={(event) =>
                                    form.setData('letter_type', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                {types.map((type) => (
                                    <option key={type.value} value={type.value}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                        </Field>
                        <Field label="Penandatangan" error={form.errors.signatory_user_id}>
                            <select
                                value={form.data.signatory_user_id}
                                onChange={(event) =>
                                    form.setData('signatory_user_id', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                <option value="">Belum diset</option>
                                {members.map((member) => (
                                    <option key={member.id} value={String(member.id)}>
                                        {member.name} · {member.role}
                                    </option>
                                ))}
                            </select>
                        </Field>
                        <Field label="Pattern nomor" error={form.errors.numbering_pattern}>
                            <input
                                value={form.data.numbering_pattern}
                                onChange={(event) =>
                                    form.setData('numbering_pattern', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            />
                        </Field>
                        <Field label="Template HTML" error={form.errors.template_html}>
                            <textarea
                                value={form.data.template_html}
                                onChange={(event) =>
                                    form.setData('template_html', event.target.value)
                                }
                                rows={8}
                                className="block w-full rounded-[4px] border-[#e6edef] font-mono text-xs shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            />
                        </Field>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
                        >
                            <Save className="h-4 w-4" />
                            Simpan Template
                        </button>
                    </form>
                </VihoCard>

                <VihoCard title="Template Aktif">
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {templates.map((template) => (
                            <div
                                key={template.id}
                                className="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-center"
                            >
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                        {template.letterTypeLabel} · {template.numberingPattern}
                                    </p>
                                    <h2 className="mt-2 font-semibold text-[#242934]">
                                        {template.name}
                                    </h2>
                                    <p className="mt-1 text-sm text-[#59667a]">
                                        Penandatangan: {template.signatoryName ?? 'Belum diset'}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.delete(
                                            route('letters.templates.destroy', template.id),
                                            { preserveScroll: true },
                                        )
                                    }
                                    className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#d22d3d]"
                                >
                                    <Trash2 className="h-4 w-4" />
                                    Arsipkan
                                </button>
                            </div>
                        ))}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">{label}</span>
            <div className="mt-2">{children}</div>
            <InputError message={error} className="mt-2" />
        </label>
    );
}
