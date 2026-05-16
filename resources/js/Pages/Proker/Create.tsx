import { CalendarPlus, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ProkerCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        template_type: 'seminar',
        starts_at: '',
        ends_at: '',
        description: '',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('proker.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M04 · Create Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Create Proker
                    </h1>
                </div>
            }
        >
            <Head title="Create Proker" />

            <div className="grid gap-6 xl:grid-cols-[1fr_340px]">
                <VihoCard
                    title="Data Proker"
                    subtitle="Draft akan masuk ke organisasi dan periode aktif user yang sedang login."
                    action={
                        <button
                            type="submit"
                            form="create-proker-form"
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                        >
                            <Save className="h-4 w-4" />
                            Simpan Draft
                        </button>
                    }
                >
                    <form
                        id="create-proker-form"
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
                                    placeholder="Seminar Karier Digital"
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
                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    PIC
                                </span>
                                <input
                                    type="text"
                                    value="User login"
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] bg-[#f5f7fb] text-sm text-[#59667a] shadow-none"
                                />
                            </label>
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Status
                                </span>
                                <input
                                    type="text"
                                    value="Draft"
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] bg-[#f5f7fb] text-sm text-[#59667a] shadow-none"
                                />
                            </label>
                        </div>
                        <label className="block">
                            <span className="text-sm font-semibold text-[#242934]">
                                Deskripsi
                            </span>
                            <textarea
                                value={data.description}
                                placeholder="Kegiatan seminar untuk mempertemukan mahasiswa dengan praktisi industri digital."
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

                <VihoCard title="Auto-generated Setup">
                    <div className="space-y-3">
                        {[
                            'Draft proposal structure',
                            'Default task checklist',
                            'Budget item template',
                            'LPJ checklist',
                        ].map((item) => (
                            <div
                                key={item}
                                className="flex items-center gap-3 rounded-[4px] bg-[#f5f7fb] p-3"
                            >
                                <CalendarPlus className="h-4 w-4 text-[#24695c]" />
                                <p className="text-sm font-medium text-[#59667a]">
                                    {item}
                                </p>
                            </div>
                        ))}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
