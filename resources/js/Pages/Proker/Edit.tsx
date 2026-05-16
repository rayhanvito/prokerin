import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

interface ProjectDetail {
    name: string;
    slug: string;
    description: string | null;
    startsAt: string | null;
    endsAt: string | null;
    templateType: string | null;
    organization: string;
    lead: string | null;
}

interface ProkerEditProps {
    project: ProjectDetail;
}

export default function ProkerEdit({ project }: ProkerEditProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: project.name,
        template_type: project.templateType ?? 'seminar',
        starts_at: project.startsAt ?? '',
        ends_at: project.endsAt ?? '',
        description: project.description ?? '',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        patch(route('proker.update', project.slug));
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Edit Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {project.name}
                    </h1>
                </div>
            }
        >
            <Head title={`Edit ${project.name}`} />

            <div className="grid gap-6 xl:grid-cols-[1fr_340px]">
                <VihoCard
                    title="Edit Data Proker"
                    subtitle="Perubahan tetap discoped ke organisasi aktif dari membership user."
                    action={
                        <button
                            type="submit"
                            form="edit-proker-form"
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                        >
                            <Save className="h-4 w-4" />
                            Simpan Perubahan
                        </button>
                    }
                >
                    <form
                        id="edit-proker-form"
                        className="space-y-5"
                        onSubmit={submit}
                    >
                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Nama Proker
                                </span>
                                <input
                                    type="text"
                                    value={data.name}
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    onChange={(event) =>
                                        setData('name', event.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </label>
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Template
                                </span>
                                <select
                                    value={data.template_type}
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    onChange={(event) =>
                                        setData(
                                            'template_type',
                                            event.target.value,
                                        )
                                    }
                                >
                                    <option value="seminar">Seminar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="competition">Lomba</option>
                                    <option value="makrab">Makrab</option>
                                </select>
                                <InputError
                                    message={errors.template_type}
                                    className="mt-2"
                                />
                            </label>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Tanggal Mulai
                                </span>
                                <input
                                    type="date"
                                    value={data.starts_at}
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    onChange={(event) =>
                                        setData('starts_at', event.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.starts_at}
                                    className="mt-2"
                                />
                            </label>
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Tanggal Selesai
                                </span>
                                <input
                                    type="date"
                                    value={data.ends_at}
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                    onChange={(event) =>
                                        setData('ends_at', event.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.ends_at}
                                    className="mt-2"
                                />
                            </label>
                        </div>

                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Deskripsi
                            </span>
                            <textarea
                                value={data.description}
                                rows={5}
                                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm leading-6 text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                onChange={(event) =>
                                    setData('description', event.target.value)
                                }
                            />
                            <InputError
                                message={errors.description}
                                className="mt-2"
                            />
                        </label>
                    </form>
                </VihoCard>

                <VihoCard title="Project Scope">
                    <div className="space-y-4">
                        <Link
                            href={route('proker.detail', project.slug)}
                            className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Kembali ke detail
                        </Link>
                        <div className="rounded-[4px] bg-[#f5f7fb] p-4">
                            <p className="text-sm font-semibold text-[#242934]">
                                {project.organization}
                            </p>
                            <p className="mt-1 text-sm text-[#717171]">
                                PIC saat ini: {project.lead ?? '-'}
                            </p>
                        </div>
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
