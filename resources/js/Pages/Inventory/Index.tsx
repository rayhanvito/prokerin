import { Head, Link } from '@inertiajs/react';
import { PackageSearch, Plus } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

import type { InventoryPayload } from './types';

export default function InventoryIndex({
    metrics,
    items,
    canManage,
}: InventoryPayload) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M40 · Inventaris
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Inventory & Asset Management
                    </h1>
                </div>
            }
        >
            <Head title="Inventaris" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <h2 className="text-xl font-semibold text-[#242934]">
                                Inventaris Organisasi
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                Track banner, kamera, sound system, kostum, dan
                                aset lain lengkap dengan QR, status pinjam, dan
                                kondisi saat serah terima.
                            </p>
                        </div>
                        {canManage && (
                            <Link
                                href={route('inventory.create')}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                            >
                                <Plus className="h-4 w-4" />
                                Tambah Item
                            </Link>
                        )}
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-4">
                    {[
                        ['Total', metrics.total],
                        ['Tersedia', metrics.available],
                        ['Dipinjam', metrics.loaned],
                        ['Perlu perhatian', metrics.needsAttention],
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
                </section>

                <VihoCard title="Daftar Inventaris">
                    {items.length > 0 ? (
                        <div className="-m-5 divide-y divide-[#e6edef]">
                            {items.map((item) => (
                                <Link
                                    key={item.id}
                                    href={route('inventory.show', item.id)}
                                    className="grid gap-4 p-5 transition hover:bg-[#f8fafb] md:grid-cols-[1fr_160px] md:items-center"
                                >
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[#717171]">
                                            {item.category} ·{' '}
                                            {item.location ?? 'Lokasi belum diisi'}
                                        </p>
                                        <h2 className="mt-2 font-semibold text-[#242934]">
                                            {item.name}
                                        </h2>
                                        <p className="mt-1 text-sm text-[#59667a]">
                                            {item.conditionLabel} · QR{' '}
                                            {item.qrToken}
                                        </p>
                                    </div>
                                    <VihoStatusBadge>
                                        {item.statusLabel}
                                    </VihoStatusBadge>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <EmptyState
                            icon={PackageSearch}
                            title="Belum ada inventaris"
                            description="Tambahkan aset organisasi pertama agar peminjaman dan handover bisa terlacak."
                            action={
                                canManage
                                    ? {
                                          label: 'Tambah Item',
                                          href: route('inventory.create'),
                                      }
                                    : undefined
                            }
                        />
                    )}
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
