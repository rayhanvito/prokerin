import { Edit3, Filter, Handshake, Plus, Save, Search } from 'lucide-react';
import { FormEvent, useState } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head, router, useForm } from '@inertiajs/react';

interface SponsorVendorContact {
    id: number;
    type: string;
    name: string;
    category: string;
    contactPerson: string;
    phone: string;
    email: string;
    address: string;
    status: string;
    notes: string;
    linkedProjects: number;
    totalAmount: number;
    documents: number;
}

interface SponsorsVendorsProps {
    filters: {
        search: string;
        type: string;
    };
    canManage: boolean;
    metrics: {
        total: number;
        active: number;
        sponsors: number;
        vendors: number;
    };
    contacts: SponsorVendorContact[];
}

export default function SponsorsVendors({
    filters,
    metrics,
    contacts,
    canManage,
}: SponsorsVendorsProps) {
    const [search, setSearch] = useState(filters.search);
    const [type, setType] = useState(filters.type);

    const submitFilters = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        router.get(
            route('organization.sponsors-vendors'),
            { search, type },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M20 · Sponsor & Vendor
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Sponsor & Vendor Database
                    </h1>
                </div>
            }
        >
            <Head title="Sponsor & Vendor Database" />

            <div className="grid gap-4 md:grid-cols-4">
                {[
                    ['Total Kontak', metrics.total],
                    ['Aktif', metrics.active],
                    ['Sponsor', metrics.sponsors],
                    ['Vendor', metrics.vendors],
                ].map(([label, value]) => (
                    <div
                        key={label}
                        className="rounded-[4px] border border-[#e6edef] bg-white p-4 shadow-sm"
                    >
                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                            {label}
                        </p>
                        <p className="mt-2 text-2xl font-semibold text-[#242934]">
                            {value}
                        </p>
                    </div>
                ))}
            </div>

            {canManage && (
                <div className="mt-6">
                    <VihoCard
                        title="Tambah Kontak"
                        subtitle="Kontak baru otomatis disimpan ke organisasi aktif pertama yang dapat Anda kelola."
                    >
                        <SponsorVendorForm mode="create" />
                    </VihoCard>
                </div>
            )}

            <div className="mt-6">
                <VihoCard
                    title="Contact Book"
                    subtitle="Database sponsor dan vendor per organisasi, lengkap dengan histori kontribusi proyek dan dokumen terkait."
                    action={
                        <form
                            onSubmit={submitFilters}
                            className="flex flex-col gap-2 sm:flex-row"
                        >
                            <label className="relative">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#717171]" />
                                <input
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Cari nama/kategori"
                                    className="w-full rounded-[4px] border-[#e6edef] py-2 pl-9 pr-3 text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c] sm:w-56"
                                />
                            </label>
                            <select
                                value={type}
                                onChange={(event) =>
                                    setType(event.target.value)
                                }
                                className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                <option value="all">Semua</option>
                                <option value="sponsor">Sponsor</option>
                                <option value="vendor">Vendor</option>
                            </select>
                            <button
                                type="submit"
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                            >
                                <Filter className="h-4 w-4" />
                                Filter
                            </button>
                        </form>
                    }
                >
                    <div className="-m-5 divide-y divide-[#e6edef]">
                        {contacts.length > 0 ? (
                            contacts.map((contact) => (
                                <ContactRow
                                    key={contact.id}
                                    contact={contact}
                                    canManage={canManage}
                                />
                            ))
                        ) : (
                            <div className="p-5 text-sm text-[#59667a]">
                                Belum ada sponsor atau vendor sesuai filter.
                            </div>
                        )}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}

function ContactRow({
    contact,
    canManage,
}: {
    contact: SponsorVendorContact;
    canManage: boolean;
}) {
    const [isEditing, setIsEditing] = useState(false);

    return (
        <div className="space-y-4 p-5">
            <div className="grid gap-4 lg:grid-cols-[1fr_220px]">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="inline-flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                            <Handshake className="h-4 w-4" />
                        </span>
                        <div>
                            <p className="font-semibold text-[#242934]">
                                {contact.name}
                            </p>
                            <p className="text-sm text-[#717171]">
                                {humanizeStatus(contact.type)} ·{' '}
                                {contact.category}
                            </p>
                        </div>
                        <VihoStatusBadge>{contact.status}</VihoStatusBadge>
                        {canManage && (
                            <button
                                type="button"
                                onClick={() => setIsEditing(!isEditing)}
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-xs font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                            >
                                <Edit3 className="h-3.5 w-3.5" />
                                Edit
                            </button>
                        )}
                    </div>
                    <p className="mt-3 text-sm text-[#59667a]">
                        {contact.contactPerson} · {contact.phone} ·{' '}
                        {contact.email}
                    </p>
                    {contact.notes !== '' && (
                        <p className="mt-2 text-sm text-[#717171]">
                            {contact.notes}
                        </p>
                    )}
                </div>
                <div className="grid grid-cols-3 gap-2 text-center lg:grid-cols-1 lg:text-left">
                    <Metric
                        label="Project"
                        value={String(contact.linkedProjects)}
                    />
                    <Metric
                        label="Value"
                        value={formatRupiah(contact.totalAmount)}
                    />
                    <Metric label="Docs" value={String(contact.documents)} />
                </div>
            </div>

            {isEditing && (
                <div className="rounded-[4px] bg-[#f5f7fb] p-4 ring-1 ring-[#e6edef]">
                    <SponsorVendorForm
                        mode="edit"
                        contact={contact}
                        onSaved={() => setIsEditing(false)}
                    />
                </div>
            )}
        </div>
    );
}

interface SponsorVendorFormData {
    type: string;
    name: string;
    category: string;
    contact_person: string;
    phone: string;
    email: string;
    address: string;
    status: string;
    notes: string;
}

function SponsorVendorForm({
    mode,
    contact,
    onSaved = () => undefined,
}: {
    mode: 'create' | 'edit';
    contact?: SponsorVendorContact;
    onSaved?: () => void;
}) {
    const form = useForm<SponsorVendorFormData>({
        type: contact?.type ?? 'sponsor',
        name: contact?.name ?? '',
        category: contact?.category ?? '',
        contact_person: contact?.contactPerson === '-' ? '' : (contact?.contactPerson ?? ''),
        phone: contact?.phone === '-' ? '' : (contact?.phone ?? ''),
        email: contact?.email === '-' ? '' : (contact?.email ?? ''),
        address: contact?.address ?? '',
        status: contact?.status ?? 'active',
        notes: contact?.notes ?? '',
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        if (mode === 'create') {
            form.post(route('organization.sponsors-vendors.store'), {
                preserveScroll: true,
                onSuccess: () => form.reset(),
            });

            return;
        }

        if (contact === undefined) {
            return;
        }

        form.patch(route('organization.sponsors-vendors.update', contact.id), {
            preserveScroll: true,
            onSuccess: onSaved,
        });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 lg:grid-cols-4">
            <select
                value={form.data.type}
                onChange={(event) => form.setData('type', event.target.value)}
                className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
            >
                <option value="sponsor">Sponsor</option>
                <option value="vendor">Vendor</option>
            </select>
            <Field
                value={form.data.name}
                placeholder="Nama"
                onChange={(value) => form.setData('name', value)}
            />
            <Field
                value={form.data.category}
                placeholder="Kategori"
                onChange={(value) => form.setData('category', value)}
            />
            <Field
                value={form.data.contact_person}
                placeholder="Contact person"
                onChange={(value) => form.setData('contact_person', value)}
            />
            <Field
                value={form.data.phone}
                placeholder="Telepon"
                onChange={(value) => form.setData('phone', value)}
            />
            <Field
                value={form.data.email}
                placeholder="Email"
                onChange={(value) => form.setData('email', value)}
            />
            <select
                value={form.data.status}
                onChange={(event) => form.setData('status', event.target.value)}
                className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
            >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <Field
                value={form.data.address}
                placeholder="Alamat"
                onChange={(value) => form.setData('address', value)}
            />
            <textarea
                value={form.data.notes}
                placeholder="Catatan"
                onChange={(event) => form.setData('notes', event.target.value)}
                className="min-h-10 rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c] lg:col-span-3"
            />
            <button
                type="submit"
                disabled={form.processing}
                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
            >
                {mode === 'create' ? (
                    <Plus className="h-4 w-4" />
                ) : (
                    <Save className="h-4 w-4" />
                )}
                {mode === 'create' ? 'Tambah' : 'Simpan'}
            </button>
        </form>
    );
}

function Field({
    value,
    placeholder,
    onChange,
}: {
    value: string;
    placeholder: string;
    onChange: (value: string) => void;
}) {
    return (
        <input
            value={value}
            placeholder={placeholder}
            onChange={(event) => onChange(event.target.value)}
            className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
        />
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-[4px] bg-[#f5f7fb] px-3 py-2">
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#717171]">
                {label}
            </p>
            <p className="mt-1 text-sm font-semibold text-[#242934]">
                {value}
            </p>
        </div>
    );
}
