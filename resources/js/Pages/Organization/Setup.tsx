import { Building2, ImagePlus, Save, ShieldCheck } from 'lucide-react';
import { FormEventHandler, useRef } from 'react';
import { Head, useForm } from '@inertiajs/react';

import FormField from '@/Components/ui/FormField';
import InputError from '@/Components/InputError';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface OrganizationSetupPayload {
    id: number;
    name: string;
    description: string;
    type: string;
    periodName: string;
    periodStart: string;
    periodEnd: string;
    hasLogo: boolean;
    memberCount: number;
    canManage: boolean;
}

interface OrganizationSetupProps {
    organization: OrganizationSetupPayload | null;
}

export default function OrganizationSetup({ organization }: OrganizationSetupProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const logoForm = useForm<{
        logo: File | null;
    }>({
        logo: null,
    });
    const profileForm = useForm<{ name: string; description: string }>({
        name: organization?.name ?? '',
        description: organization?.description ?? '',
    });

    const submitLogo: FormEventHandler = (event) => {
        event.preventDefault();

        logoForm.post(route('organization.logo.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                logoForm.reset('logo');

                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    };

    const submitProfile: FormEventHandler = (event) => {
        event.preventDefault();

        profileForm.patch(route('organization.update'), {
            preserveScroll: true,
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
                    <form className="space-y-5" onSubmit={submitProfile}>
                        <div className="grid gap-5 md:grid-cols-2">
                            <FormField
                                label="Nama Organisasi"
                                htmlFor="organization-name"
                                required
                                error={profileForm.errors.name}
                            >
                                <input
                                    id="organization-name"
                                    type="text"
                                    value={profileForm.data.name}
                                    readOnly={!organization?.canManage}
                                    onChange={(event) =>
                                        profileForm.setData(
                                            'name',
                                            event.target.value,
                                        )
                                    }
                                    className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </FormField>

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
                        <FormField
                            label="Deskripsi Organisasi"
                            htmlFor="organization-description"
                            error={profileForm.errors.description}
                            hint="Ringkasan singkat untuk dokumen resmi dan konteks workspace."
                        >
                            <textarea
                                id="organization-description"
                                value={profileForm.data.description}
                                readOnly={!organization?.canManage}
                                onChange={(event) =>
                                    profileForm.setData(
                                        'description',
                                        event.target.value,
                                    )
                                }
                                rows={4}
                                className="block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                            />
                        </FormField>
                        {organization?.canManage ? (
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={profileForm.processing}
                                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                >
                                    <Save className="h-4 w-4" />
                                    Simpan Profil
                                </button>
                            </div>
                        ) : null}
                    </form>

                    <form className="mt-6 space-y-5" onSubmit={submitLogo}>

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
                                        {logoForm.data.logo
                                            ? logoForm.data.logo.name
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
                                        logoForm.setData(
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
                                message={logoForm.errors.logo}
                                className="mt-3"
                            />
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={logoForm.processing || !logoForm.data.logo}
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
                            <div className="flex items-center gap-4 p-5">
                                <span className="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                    <Building2 className="h-5 w-5" />
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-semibold text-[#242934]">
                                        {organization?.name ??
                                            'Belum ada organisasi aktif'}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        Periode {organization?.periodName ?? '-'} ·{' '}
                                        {organization?.memberCount ?? 0} anggota
                                    </p>
                                </div>
                                <span className="rounded-[4px] bg-[rgba(36,105,92,0.1)] px-3 py-1 text-xs font-semibold text-[#24695c]">
                                    Active
                                </span>
                            </div>
                        </div>
                    </VihoCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
