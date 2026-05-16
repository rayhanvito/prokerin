import { Building2, ImagePlus, Save, ShieldCheck } from 'lucide-react';
import { FormEventHandler, useRef } from 'react';

import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

interface OrganizationSetupPayload {
    id: number;
    name: string;
    type: string;
    periodName: string;
    periodStart: string;
    periodEnd: string;
    hasLogo: boolean;
    memberCount: number;
}

interface OrganizationSetupProps {
    organization: OrganizationSetupPayload | null;
}

const periods = [
    {
        title: 'BEM Fakultas Teknologi',
        meta: 'Periode 2026 · 238 anggota',
        status: 'Active',
    },
    {
        title: 'HIMA Informatika',
        meta: 'Periode 2026 · 86 anggota',
        status: 'Setup',
    },
    {
        title: 'UKM Kreatif',
        meta: 'Periode 2025/2026 · 64 anggota',
        status: 'Active',
    },
];

export default function OrganizationSetup({ organization }: OrganizationSetupProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const { data, setData, post, processing, errors, reset } = useForm<{
        logo: File | null;
    }>({
        logo: null,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('organization.logo.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('logo');

                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M02 · Organization
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Organization Setup
                    </h1>
                </div>
            }
        >
            <Head title="Organization Setup" />

            <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <VihoCard
                    title="Profil Organisasi"
                    subtitle="Profil aktif, periode berjalan, dan upload logo private/S3-compatible."
                >
                    <form className="space-y-5" onSubmit={submit}>
                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Nama Organisasi
                                </span>
                                <input
                                    type="text"
                                    value={
                                        organization?.name ??
                                        'Belum ada organisasi aktif'
                                    }
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Tipe Organisasi
                                </span>
                                <input
                                    type="text"
                                    value={organization?.type ?? '-'}
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </label>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Periode Mulai
                                </span>
                                <input
                                    type="text"
                                    value={organization?.periodStart ?? '-'}
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </label>

                            <label className="block">
                                <span className="text-sm font-semibold text-[#242934]">
                                    Periode Selesai
                                </span>
                                <input
                                    type="text"
                                    value={organization?.periodEnd ?? '-'}
                                    readOnly
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </label>
                        </div>

                        <div className="rounded-[4px] border border-dashed border-[#e6edef] bg-[#f5f7fb] p-5">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                                <span className="inline-flex h-14 w-14 items-center justify-center rounded-[4px] bg-white text-[#24695c] shadow-sm">
                                    <ImagePlus className="h-6 w-6" />
                                </span>
                                <div className="flex-1">
                                    <p className="font-semibold text-[#242934]">
                                        Logo organisasi
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {data.logo
                                            ? data.logo.name
                                            : organization?.hasLogo
                                              ? 'Logo sudah tersimpan di storage private.'
                                              : 'JPG, PNG, atau WEBP maksimal 2 MB.'}
                                    </p>
                                </div>
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    className="sr-only"
                                    onChange={(event) =>
                                        setData(
                                            'logo',
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                />
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                                >
                                    Pilih File
                                </button>
                            </div>
                            <InputError
                                message={errors.logo}
                                className="mt-3"
                            />
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing || !data.logo}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                            >
                                <Save className="h-4 w-4" />
                                Simpan Logo
                            </button>
                        </div>
                    </form>
                </VihoCard>

                <div className="space-y-6">
                    <VihoCard title="Organization Scope">
                        <div className="flex gap-4">
                            <span className="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                <ShieldCheck className="h-6 w-6" />
                            </span>
                            <div>
                                <p className="font-semibold text-[#242934]">
                                    Multi-organization ready
                                </p>
                                    <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                    Logo ini dipakai untuk proposal, LPJ, dan
                                    dokumen resmi organisasi aktif.
                                </p>
                            </div>
                        </div>
                    </VihoCard>

                    <VihoCard title="Periode Aktif">
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {periods.map((period) => (
                                <div
                                    key={period.title}
                                    className="flex items-center gap-4 p-5"
                                >
                                    <span className="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                        <Building2 className="h-5 w-5" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-semibold text-[#242934]">
                                            {period.title}
                                        </p>
                                        <p className="mt-1 text-sm text-[#717171]">
                                            {period.meta}
                                        </p>
                                    </div>
                                    <span className="rounded-[4px] bg-[rgba(36,105,92,0.1)] px-3 py-1 text-xs font-semibold text-[#24695c]">
                                        {period.status}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </VihoCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
