import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

import type { InventoryPayload } from './types';

interface ItemFormData {
    name: string;
    category: string;
    description: string;
    location: string;
    condition: string;
    status: string;
    purchased_at: string;
    purchase_amount: string;
}

export default function InventoryEdit({ item, options }: InventoryPayload) {
    if (item === null) {
        return null;
    }

    const form = useForm<ItemFormData>({
        name: item.name,
        category: item.category,
        description: item.description ?? '',
        location: item.location ?? '',
        condition: item.condition,
        status: item.status,
        purchased_at: item.purchasedAt ?? '',
        purchase_amount:
            item.purchaseAmount === null || item.purchaseAmount === undefined
                ? ''
                : String(item.purchaseAmount),
    });

    const submit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        form.patch(route('inventory.update', item.id));
    };

    return (
        <AuthenticatedLayout header={<h1>Edit Inventaris</h1>}>
            <Head title={`Edit ${item.name}`} />
            <VihoCard title={item.name}>
                <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
                    <TextField label="Nama item" value={form.data.name} onChange={(value) => form.setData('name', value)} required />
                    <TextField label="Kategori" value={form.data.category} onChange={(value) => form.setData('category', value)} required />
                    <TextField label="Lokasi" value={form.data.location} onChange={(value) => form.setData('location', value)} />
                    <label className="block text-sm font-semibold text-[#242934]">
                        Kondisi
                        <select
                            value={form.data.condition}
                            onChange={(event) => form.setData('condition', event.target.value)}
                            className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
                        >
                            {options.conditions.map((condition) => (
                                <option key={condition.value} value={condition.value}>
                                    {condition.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="block text-sm font-semibold text-[#242934]">
                        Status
                        <select
                            value={form.data.status}
                            onChange={(event) => form.setData('status', event.target.value)}
                            className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
                        >
                            {options.statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <TextField label="Tanggal beli" type="date" value={form.data.purchased_at} onChange={(value) => form.setData('purchased_at', value)} />
                    <TextField label="Harga beli" type="number" value={form.data.purchase_amount} onChange={(value) => form.setData('purchase_amount', value)} />
                    <label className="block text-sm font-semibold text-[#242934] md:col-span-2">
                        Deskripsi
                        <textarea
                            value={form.data.description}
                            onChange={(event) => form.setData('description', event.target.value)}
                            className="mt-1 min-h-28 w-full rounded-[4px] border-[#e6edef] text-sm"
                        />
                    </label>
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60 md:col-span-2"
                    >
                        Simpan Perubahan
                    </button>
                </form>
            </VihoCard>
        </AuthenticatedLayout>
    );
}

function TextField({
    label,
    value,
    onChange,
    type = 'text',
    required = false,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    type?: string;
    required?: boolean;
}) {
    return (
        <label className="block text-sm font-semibold text-[#242934]">
            {label}
            <input
                type={type}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                required={required}
                className="mt-1 w-full rounded-[4px] border-[#e6edef] text-sm"
            />
        </label>
    );
}
