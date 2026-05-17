import { Head, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

import InputError from '@/Components/InputError';
import PlaceholderHelpPanel from '@/Components/Letters/PlaceholderHelpPanel';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface TemplateOption {
    id: number;
    name: string;
}

interface ProjectOption {
    id: number;
    name: string;
}

interface LettersCreateProps {
    templates: TemplateOption[];
    projects: ProjectOption[];
    placeholders: string[];
}

export default function LettersCreate({
    templates,
    projects,
    placeholders,
}: LettersCreateProps) {
    const form = useForm({
        template_id: templates[0] ? String(templates[0].id) : '',
        project_id: projects[0] ? String(projects[0].id) : '',
        subject: '',
        recipient_name: '',
        recipient_organization: '',
        body_data: {
            event_location: '',
            contact_person: '',
        },
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            template_id: Number(data.template_id),
            project_id: data.project_id === '' ? null : Number(data.project_id),
            recipient_organization:
                data.recipient_organization === ''
                    ? null
                    : data.recipient_organization,
        }));
        form.post(route('letters.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M39 · Draft Surat
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Buat Surat
                    </h1>
                </div>
            }
        >
            <Head title="Buat Surat" />
            <div className="grid gap-6 xl:grid-cols-[1fr_360px]">
                <VihoCard title="Draft Surat">
                    <form onSubmit={submit} className="space-y-5">
                        <Field label="Template" error={form.errors.template_id}>
                            <select
                                value={form.data.template_id}
                                onChange={(event) =>
                                    form.setData('template_id', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                {templates.map((template) => (
                                    <option key={template.id} value={String(template.id)}>
                                        {template.name}
                                    </option>
                                ))}
                            </select>
                        </Field>
                        <Field label="Proker" error={form.errors.project_id}>
                            <select
                                value={form.data.project_id}
                                onChange={(event) =>
                                    form.setData('project_id', event.target.value)
                                }
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                <option value="">Tanpa proker</option>
                                {projects.map((project) => (
                                    <option key={project.id} value={String(project.id)}>
                                        {project.name}
                                    </option>
                                ))}
                            </select>
                        </Field>
                        <Field label="Perihal" error={form.errors.subject}>
                            <Input value={form.data.subject} onChange={(value) => form.setData('subject', value)} />
                        </Field>
                        <Field label="Penerima" error={form.errors.recipient_name}>
                            <Input value={form.data.recipient_name} onChange={(value) => form.setData('recipient_name', value)} />
                        </Field>
                        <Field label="Instansi penerima" error={form.errors.recipient_organization}>
                            <Input value={form.data.recipient_organization} onChange={(value) => form.setData('recipient_organization', value)} />
                        </Field>
                        <Field label="Lokasi event">
                            <Input value={form.data.body_data.event_location} onChange={(value) => form.setData('body_data', { ...form.data.body_data, event_location: value })} />
                        </Field>
                        <Field label="Kontak panitia">
                            <Input value={form.data.body_data.contact_person} onChange={(value) => form.setData('body_data', { ...form.data.body_data, contact_person: value })} />
                        </Field>
                        <button type="submit" disabled={form.processing || templates.length === 0} className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                            <Save className="h-4 w-4" />
                            Save Draft
                        </button>
                    </form>
                </VihoCard>
                <PlaceholderHelpPanel placeholders={placeholders} />
            </div>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: ReactNode }) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">{label}</span>
            <div className="mt-2">{children}</div>
            <InputError message={error} className="mt-2" />
        </label>
    );
}

function Input({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    return <input value={value} onChange={(event) => onChange(event.target.value)} className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]" />;
}
