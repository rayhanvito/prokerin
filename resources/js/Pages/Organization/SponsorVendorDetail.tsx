import { ArrowLeft, FileText, FolderOpen, Handshake } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatRupiah, humanizeStatus } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

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
}

interface LinkedProject {
    id: number;
    name: string;
    slug: string;
    roleDescription: string;
    amount: number;
    linkedAt: string;
}

interface LinkedDocument {
    id: number;
    name: string;
    folder: string;
    visibility: string;
    status: string;
}

interface SponsorVendorDetailProps {
    contact: SponsorVendorContact;
    projects: LinkedProject[];
    documents: LinkedDocument[];
}

export default function SponsorVendorDetail({
    contact,
    projects,
    documents,
}: SponsorVendorDetailProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M20 · Detail Sponsor/Vendor
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {contact.name}
                    </h1>
                </div>
            }
        >
            <Head title={contact.name} />

            <div className="mb-4">
                <Link
                    href={route('organization.sponsors-vendors')}
                    className="inline-flex items-center gap-2 text-sm font-semibold text-[#59667a] transition hover:text-[#24695c]"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali ke database
                </Link>
            </div>

            <div className="grid gap-6 xl:grid-cols-[360px_1fr]">
                <VihoCard title="Profil Kontak">
                    <div className="space-y-4">
                        <div className="flex items-start gap-3">
                            <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                <Handshake className="h-5 w-5" />
                            </span>
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="font-semibold text-[#242934]">
                                        {contact.name}
                                    </p>
                                    <VihoStatusBadge>
                                        {contact.status}
                                    </VihoStatusBadge>
                                </div>
                                <p className="mt-1 text-sm text-[#717171]">
                                    {humanizeStatus(contact.type)} ·{' '}
                                    {contact.category}
                                </p>
                            </div>
                        </div>
                        <Info label="Contact" value={contact.contactPerson} />
                        <Info label="Phone" value={contact.phone} />
                        <Info label="Email" value={contact.email} />
                        <Info
                            label="Address"
                            value={
                                contact.address === ''
                                    ? 'Belum dicatat'
                                    : contact.address
                            }
                        />
                        <Info
                            label="Notes"
                            value={
                                contact.notes === ''
                                    ? 'Belum ada catatan.'
                                    : contact.notes
                            }
                        />
                    </div>
                </VihoCard>

                <div className="space-y-6">
                    <VihoCard
                        title="Project History"
                        subtitle="Riwayat kontribusi sponsor/vendor pada proker organisasi."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {projects.length > 0 ? (
                                projects.map((project) => (
                                    <div
                                        key={project.id}
                                        className="flex flex-col gap-3 p-5 lg:flex-row lg:items-center lg:justify-between"
                                    >
                                        <div>
                                            <Link
                                                href={route(
                                                    'proker.detail',
                                                    project.slug,
                                                )}
                                                className="font-semibold text-[#242934] transition hover:text-[#24695c]"
                                            >
                                                {project.name}
                                            </Link>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {project.roleDescription} ·{' '}
                                                {project.linkedAt}
                                            </p>
                                        </div>
                                        <p className="text-sm font-semibold text-[#24695c]">
                                            {formatRupiah(project.amount)}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <div className="p-5 text-sm text-[#59667a]">
                                    Belum ada histori proyek.
                                </div>
                            )}
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Linked Documents"
                        subtitle="Dokumen proposal, invoice, MoU, atau bukti transaksi yang terkait kontak ini."
                    >
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {documents.length > 0 ? (
                                documents.map((document) => (
                                    <div
                                        key={document.id}
                                        className="flex items-center gap-3 p-5"
                                    >
                                        <span className="inline-flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                            <FileText className="h-4 w-4" />
                                        </span>
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-[#242934]">
                                                {document.name}
                                            </p>
                                            <p className="mt-1 text-sm text-[#717171]">
                                                {document.folder} ·{' '}
                                                {document.visibility}
                                            </p>
                                        </div>
                                        <VihoStatusBadge>
                                            {document.status}
                                        </VihoStatusBadge>
                                    </div>
                                ))
                            ) : (
                                <div className="flex items-center gap-3 p-5 text-sm text-[#59667a]">
                                    <FolderOpen className="h-4 w-4" />
                                    Belum ada dokumen terkait.
                                </div>
                            )}
                        </div>
                    </VihoCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-[4px] bg-[#f5f7fb] p-3">
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#717171]">
                {label}
            </p>
            <p className="mt-1 text-sm font-semibold text-[#242934]">
                {value}
            </p>
        </div>
    );
}
