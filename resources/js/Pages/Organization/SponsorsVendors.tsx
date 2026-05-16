import { Filter, Handshake, Search } from 'lucide-react';
import { FormEvent, useState } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head, router } from '@inertiajs/react';

interface SponsorVendorContact {
    id: number;
    type: string;
    name: string;
    category: string;
    contactPerson: string;
    phone: string;
    email: string;
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
                                <div
                                    key={contact.id}
                                    className="grid gap-4 p-5 lg:grid-cols-[1fr_220px]"
                                >
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
                                                    {humanizeStatus(
                                                        contact.type,
                                                    )}{' '}
                                                    · {contact.category}
                                                </p>
                                            </div>
                                            <VihoStatusBadge>
                                                {contact.status}
                                            </VihoStatusBadge>
                                        </div>
                                        <p className="mt-3 text-sm text-[#59667a]">
                                            {contact.contactPerson} ·{' '}
                                            {contact.phone} · {contact.email}
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
                                            value={String(
                                                contact.linkedProjects,
                                            )}
                                        />
                                        <Metric
                                            label="Value"
                                            value={formatRupiah(
                                                contact.totalAmount,
                                            )}
                                        />
                                        <Metric
                                            label="Docs"
                                            value={String(contact.documents)}
                                        />
                                    </div>
                                </div>
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
