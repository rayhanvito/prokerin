import { Head, useForm } from '@inertiajs/react';
import { Award, SendHorizonal, UserPlus } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface OptionItem {
    id: number;
    name: string;
}

interface MeetingOption {
    id: number;
    title: string;
}

interface RecipientOption {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface CertificateTemplate {
    id: number;
    name: string;
    description: string | null;
    isActive: boolean;
    issuedCount: number;
}

interface IssueRecipient {
    user_id: number | null;
    recipient_name: string;
    recipient_email: string;
}

interface CertificateIssueForm {
    template_id: number | '';
    project_id: number | '';
    meeting_id: number | '';
    recipients: IssueRecipient[];
}

interface CertificateIssueProps {
    templates: CertificateTemplate[];
    recipients: RecipientOption[];
    projects: OptionItem[];
    meetings: MeetingOption[];
    canIssue: boolean;
}

export default function CertificateIssue({
    templates,
    recipients,
    projects,
    meetings,
    canIssue,
}: CertificateIssueProps) {
    const activeTemplates = templates.filter((template) => template.isActive);
    const [manualName, setManualName] = useState('');
    const [manualEmail, setManualEmail] = useState('');
    const { data, setData, post, processing, errors, reset } =
        useForm<CertificateIssueForm>({
        template_id: activeTemplates[0]?.id ?? '',
        project_id: projects[0]?.id ?? '',
        meeting_id: '',
        recipients: [] as IssueRecipient[],
    });

    const addMember = (recipient: RecipientOption): void => {
        if (
            data.recipients.some(
                (selected) => selected.user_id === recipient.id,
            )
        ) {
            return;
        }

        setData('recipients', [
            ...data.recipients,
            {
                user_id: recipient.id,
                recipient_name: recipient.name,
                recipient_email: recipient.email,
            },
        ]);
    };

    const addManualRecipient = (): void => {
        if (manualName.trim() === '') {
            return;
        }

        setData('recipients', [
            ...data.recipients,
            {
                user_id: null,
                recipient_name: manualName,
                recipient_email: manualEmail,
            },
        ]);
        setManualName('');
        setManualEmail('');
    };

    const submitIssue = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        post(route('certificates.issue.store'), {
            preserveScroll: true,
            onSuccess: () => reset('recipients'),
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
                        Issue Sertifikat
                    </h1>
                </div>
            }
        >
            <Head title="Issue Sertifikat" />

            <form onSubmit={submitIssue} className="grid gap-6 xl:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <VihoCard
                        title="Detail Batch"
                        subtitle="Pilih template, konteks proker, dan rapat opsional."
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <div>
                                <label className="text-sm font-semibold text-[#242934]">
                                    Template
                                </label>
                                <select
                                    value={data.template_id}
                                    onChange={(event) =>
                                        setData(
                                            'template_id',
                                            Number(event.target.value),
                                        )
                                    }
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm"
                                    disabled={!canIssue}
                                >
                                    {activeTemplates.map((template) => (
                                        <option key={template.id} value={template.id}>
                                            {template.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.template_id}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <label className="text-sm font-semibold text-[#242934]">
                                    Proker
                                </label>
                                <select
                                    value={data.project_id}
                                    onChange={(event) =>
                                        setData(
                                            'project_id',
                                            event.target.value === ''
                                                ? ''
                                                : Number(event.target.value),
                                        )
                                    }
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm"
                                    disabled={!canIssue}
                                >
                                    <option value="">Tanpa proker</option>
                                    {projects.map((project) => (
                                        <option key={project.id} value={project.id}>
                                            {project.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.project_id}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <label className="text-sm font-semibold text-[#242934]">
                                    Rapat
                                </label>
                                <select
                                    value={data.meeting_id}
                                    onChange={(event) =>
                                        setData(
                                            'meeting_id',
                                            event.target.value === ''
                                                ? ''
                                                : Number(event.target.value),
                                        )
                                    }
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm"
                                    disabled={!canIssue}
                                >
                                    <option value="">Tanpa rapat</option>
                                    {meetings.map((meeting) => (
                                        <option key={meeting.id} value={meeting.id}>
                                            {meeting.title}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.meeting_id}
                                    className="mt-2"
                                />
                            </div>
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Penerima dari Anggota"
                        subtitle="Tambahkan anggota organisasi ke batch sertifikat."
                    >
                        <div className="grid gap-3 md:grid-cols-2">
                            {recipients.map((recipient) => (
                                <button
                                    key={recipient.id}
                                    type="button"
                                    onClick={() => addMember(recipient)}
                                    disabled={!canIssue}
                                    className="flex items-center justify-between gap-3 rounded-[4px] bg-[#f5f7fb] p-4 text-left ring-1 ring-[#e6edef] transition hover:bg-white"
                                >
                                    <span>
                                        <span className="block font-semibold text-[#242934]">
                                            {recipient.name}
                                        </span>
                                        <span className="mt-1 block text-sm text-[#717171]">
                                            {recipient.email} · {recipient.role}
                                        </span>
                                    </span>
                                    <UserPlus className="h-4 w-4 text-[#24695c]" />
                                </button>
                            ))}
                        </div>
                    </VihoCard>
                </div>

                <div className="space-y-6">
                    <VihoCard
                        title="Penerima Manual"
                        subtitle="Untuk peserta eksternal tanpa akun."
                    >
                        <div className="space-y-3">
                            <TextInput
                                value={manualName}
                                onChange={(event) =>
                                    setManualName(event.target.value)
                                }
                                placeholder="Nama penerima"
                                className="block w-full"
                                disabled={!canIssue}
                            />
                            <TextInput
                                value={manualEmail}
                                onChange={(event) =>
                                    setManualEmail(event.target.value)
                                }
                                placeholder="Email opsional"
                                className="block w-full"
                                disabled={!canIssue}
                            />
                            <button
                                type="button"
                                onClick={addManualRecipient}
                                disabled={!canIssue}
                                className="inline-flex h-9 w-full items-center justify-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 text-sm font-semibold text-[#59667a] ring-1 ring-[#e6edef]"
                            >
                                <UserPlus className="h-4 w-4" />
                                Tambah Manual
                            </button>
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Batch Preview"
                        subtitle="Sertifikat akan dibuat dan job PDF dikirim ke queue."
                    >
                        <div className="space-y-3">
                            {data.recipients.map((recipient) => (
                                <div
                                    key={`${recipient.user_id}-${recipient.recipient_name}`}
                                    className="rounded-[4px] bg-[#f5f7fb] p-3"
                                >
                                    <p className="font-semibold text-[#242934]">
                                        {recipient.recipient_name}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {recipient.recipient_email ||
                                            'Email tidak diisi'}
                                    </p>
                                </div>
                            ))}
                            <InputError
                                message={errors.recipients}
                                className="mt-2"
                            />

                            <div className="flex items-center justify-between gap-3 rounded-[4px] bg-[rgba(36,105,92,0.1)] p-4">
                                <div className="flex items-center gap-2 text-sm font-semibold text-[#24695c]">
                                    <Award className="h-4 w-4" />
                                    {data.recipients.length} penerima
                                </div>
                                <PrimaryButton
                                    disabled={
                                        processing ||
                                        !canIssue ||
                                        data.recipients.length === 0
                                    }
                                >
                                    <SendHorizonal className="mr-2 h-4 w-4" />
                                    Terbitkan
                                </PrimaryButton>
                            </div>
                        </div>
                    </VihoCard>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
