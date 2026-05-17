import { Head, router, useForm } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Eye,
    ImagePlus,
    Images,
    Save,
    Send,
    Trash2,
} from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { useState } from 'react';

import InputError from '@/Components/InputError';
import ConfirmDialog from '@/Components/ui/ConfirmDialog';
import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

interface MicrositeSettingsProps extends PageProps {
    project: {
        id: number;
        name: string;
        slug: string;
        organizationSlug: string;
        publicUrl: string;
    };
    microsite: {
        id: number;
        isPublished: boolean;
        bannerImageUrl: string | null;
        descriptionMd: string;
        locationText: string;
        locationMapsUrl: string;
        contactName: string;
        contactWhatsapp: string;
        contactEmail: string;
        showCountdown: boolean;
        showCommittee: boolean;
        showGallery: boolean;
        metaTitle: string;
        metaDescription: string;
        publishedAt: string | null;
    };
    gallery: GalleryItem[];
}

interface SettingsFormData {
    description_md: string;
    location_text: string;
    location_maps_url: string;
    contact_name: string;
    contact_whatsapp: string;
    contact_email: string;
    show_countdown: boolean;
    show_committee: boolean;
    show_gallery: boolean;
    meta_title: string;
    meta_description: string;
}

interface UploadFormData {
    image: File | null;
    caption: string;
}

interface GalleryItem {
    id: number;
    imageUrl: string;
    caption: string;
    sortOrder: number;
}

export default function MicrositeSettings({
    project,
    microsite,
    gallery,
}: MicrositeSettingsProps) {
    const [publishOpen, setPublishOpen] = useState(false);
    const [unpublishOpen, setUnpublishOpen] = useState(false);
    const form = useForm<SettingsFormData>({
        description_md: microsite.descriptionMd,
        location_text: microsite.locationText,
        location_maps_url: microsite.locationMapsUrl,
        contact_name: microsite.contactName,
        contact_whatsapp: microsite.contactWhatsapp,
        contact_email: microsite.contactEmail,
        show_countdown: microsite.showCountdown,
        show_committee: microsite.showCommittee,
        show_gallery: microsite.showGallery,
        meta_title: microsite.metaTitle,
        meta_description: microsite.metaDescription,
    });
    const bannerForm = useForm<UploadFormData>({ image: null, caption: '' });
    const galleryForm = useForm<UploadFormData>({ image: null, caption: '' });

    const submitSettings = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        form.patch(route('proker.microsite.update', project.slug), {
            preserveScroll: true,
        });
    };

    const uploadBanner = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        bannerForm.post(route('proker.microsite.banner.store', project.slug), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => bannerForm.reset(),
        });
    };

    const uploadGallery = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        galleryForm.post(route('proker.microsite.gallery.store', project.slug), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => galleryForm.reset(),
        });
    };

    const publish = (): void => {
        router.post(
            route('proker.microsite.publish', project.slug),
            {},
            {
                preserveScroll: true,
                onFinish: () => setPublishOpen(false),
            },
        );
    };

    const unpublish = (): void => {
        router.post(
            route('proker.microsite.unpublish', project.slug),
            {},
            {
                preserveScroll: true,
                onFinish: () => setUnpublishOpen(false),
            },
        );
    };

    const reorderGallery = (items: GalleryItem[]): void => {
        router.patch(
            route('proker.microsite.gallery.reorder', project.slug),
            { items: items.map((item) => item.id) },
            { preserveScroll: true },
        );
    };

    const deleteGalleryItem = (item: GalleryItem): void => {
        router.delete(
            route('proker.microsite.gallery.destroy', {
                project: project.slug,
                item: item.id,
            }),
            { preserveScroll: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M31 · Public Proker Microsite
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Microsite {project.name}
                    </h1>
                </div>
            }
        >
            <Head title={`Microsite ${project.name}`} />

            <div className="grid gap-6 xl:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <VihoCard>
                        <div className="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                            <div>
                                <div className="flex flex-wrap items-center gap-3">
                                    <h2 className="text-xl font-semibold text-[#242934]">
                                        Status Publikasi
                                    </h2>
                                    <VihoStatusBadge>
                                        {microsite.isPublished
                                            ? 'Published'
                                            : 'Draft'}
                                    </VihoStatusBadge>
                                </div>
                                <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                    URL publik microsite ini memakai slug organisasi
                                    dan slug proker agar mudah dibagikan ke sosial
                                    media.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <a
                                    href={project.publicUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c]"
                                >
                                    <Eye className="h-4 w-4" />
                                    Preview
                                </a>
                                {microsite.isPublished ? (
                                    <button
                                        type="button"
                                        onClick={() => setUnpublishOpen(true)}
                                        className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#d22d3d] bg-white px-4 py-2 text-sm font-semibold text-[#d22d3d]"
                                    >
                                        Tarik
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={() => setPublishOpen(true)}
                                        className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        <Send className="h-4 w-4" />
                                        Publish
                                    </button>
                                )}
                            </div>
                        </div>
                    </VihoCard>

                    <VihoCard
                        title="Konten Microsite"
                        subtitle="Copy publik, lokasi, kontak, dan metadata SEO."
                    >
                        <form onSubmit={submitSettings} className="space-y-5">
                            <Field label="Deskripsi publik" error={form.errors.description_md}>
                                <textarea
                                    value={form.data.description_md}
                                    onChange={(event) =>
                                        form.setData(
                                            'description_md',
                                            event.target.value,
                                        )
                                    }
                                    rows={8}
                                    className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </Field>

                            <div className="grid gap-4 md:grid-cols-2">
                                <Field label="Lokasi" error={form.errors.location_text}>
                                    <TextInput
                                        value={form.data.location_text}
                                        onChange={(value) =>
                                            form.setData('location_text', value)
                                        }
                                    />
                                </Field>
                                <Field
                                    label="Google Maps URL"
                                    error={form.errors.location_maps_url}
                                >
                                    <TextInput
                                        value={form.data.location_maps_url}
                                        onChange={(value) =>
                                            form.setData(
                                                'location_maps_url',
                                                value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <div className="grid gap-4 md:grid-cols-3">
                                <Field label="Nama kontak" error={form.errors.contact_name}>
                                    <TextInput
                                        value={form.data.contact_name}
                                        onChange={(value) =>
                                            form.setData('contact_name', value)
                                        }
                                    />
                                </Field>
                                <Field label="WhatsApp" error={form.errors.contact_whatsapp}>
                                    <TextInput
                                        value={form.data.contact_whatsapp}
                                        onChange={(value) =>
                                            form.setData('contact_whatsapp', value)
                                        }
                                    />
                                </Field>
                                <Field label="Email kontak" error={form.errors.contact_email}>
                                    <TextInput
                                        value={form.data.contact_email}
                                        onChange={(value) =>
                                            form.setData('contact_email', value)
                                        }
                                    />
                                </Field>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <Toggle
                                    label="Countdown"
                                    checked={form.data.show_countdown}
                                    onChange={(checked) =>
                                        form.setData('show_countdown', checked)
                                    }
                                />
                                <Toggle
                                    label="Panitia"
                                    checked={form.data.show_committee}
                                    onChange={(checked) =>
                                        form.setData('show_committee', checked)
                                    }
                                />
                                <Toggle
                                    label="Galeri"
                                    checked={form.data.show_gallery}
                                    onChange={(checked) =>
                                        form.setData('show_gallery', checked)
                                    }
                                />
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <Field label="Meta title" error={form.errors.meta_title}>
                                    <TextInput
                                        value={form.data.meta_title}
                                        onChange={(value) =>
                                            form.setData('meta_title', value)
                                        }
                                    />
                                </Field>
                                <Field
                                    label="Meta description"
                                    error={form.errors.meta_description}
                                >
                                    <TextInput
                                        value={form.data.meta_description}
                                        onChange={(value) =>
                                            form.setData(
                                                'meta_description',
                                                value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <button
                                type="submit"
                                disabled={form.processing}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Save className="h-4 w-4" />
                                Simpan
                            </button>
                        </form>
                    </VihoCard>
                </div>

                <aside className="space-y-6">
                    <VihoCard title="Banner" subtitle="Rasio 16:9 atau 4:3 paling aman.">
                        {microsite.bannerImageUrl ? (
                            <img
                                src={microsite.bannerImageUrl}
                                alt=""
                                className="mb-4 aspect-video w-full rounded-[4px] object-cover"
                            />
                        ) : (
                            <div className="mb-4 flex aspect-video items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#717171] ring-1 ring-[#e6edef]">
                                <Images className="h-8 w-8" />
                            </div>
                        )}
                        <form onSubmit={uploadBanner} className="space-y-3">
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                onChange={(event) =>
                                    bannerForm.setData(
                                        'image',
                                        event.target.files?.[0] ?? null,
                                    )
                                }
                                className="block w-full text-sm text-[#59667a] file:mr-4 file:rounded-[4px] file:border-0 file:bg-[#24695c] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                            />
                            <InputError message={bannerForm.errors.image} />
                            <button
                                type="submit"
                                disabled={bannerForm.processing || bannerForm.data.image === null}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <ImagePlus className="h-4 w-4" />
                                Upload Banner
                            </button>
                        </form>
                    </VihoCard>

                    <VihoCard title="Galeri" subtitle="Foto dokumentasi atau teaser event.">
                        <form onSubmit={uploadGallery} className="space-y-3">
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                onChange={(event) =>
                                    galleryForm.setData(
                                        'image',
                                        event.target.files?.[0] ?? null,
                                    )
                                }
                                className="block w-full text-sm text-[#59667a] file:mr-4 file:rounded-[4px] file:border-0 file:bg-[#24695c] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                            />
                            <TextInput
                                value={galleryForm.data.caption}
                                placeholder="Caption"
                                onChange={(value) =>
                                    galleryForm.setData('caption', value)
                                }
                            />
                            <InputError message={galleryForm.errors.image} />
                            <button
                                type="submit"
                                disabled={galleryForm.processing || galleryForm.data.image === null}
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#24695c] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <ImagePlus className="h-4 w-4" />
                                Tambah Foto
                            </button>
                        </form>

                        <div className="mt-5 space-y-3">
                            {gallery.length > 0 ? (
                                gallery.map((item, index) => (
                                    <GalleryRow
                                        key={item.id}
                                        item={item}
                                        canMoveUp={index > 0}
                                        canMoveDown={index < gallery.length - 1}
                                        onDelete={() => deleteGalleryItem(item)}
                                        onMoveUp={() =>
                                            reorderGallery(moveItem(gallery, index, index - 1))
                                        }
                                        onMoveDown={() =>
                                            reorderGallery(moveItem(gallery, index, index + 1))
                                        }
                                    />
                                ))
                            ) : (
                                <EmptyState
                                    icon={Images}
                                    title="Belum ada foto"
                                    description="Tambahkan banner dan galeri agar halaman publik lebih kuat saat dibagikan."
                                />
                            )}
                        </div>
                    </VihoCard>
                </aside>
            </div>

            <ConfirmDialog
                open={publishOpen}
                onOpenChange={setPublishOpen}
                title="Publish microsite?"
                description="Halaman akan bisa diakses publik melalui URL microsite proker."
                confirmLabel="Publish"
                onConfirm={publish}
            />
            <ConfirmDialog
                open={unpublishOpen}
                onOpenChange={setUnpublishOpen}
                title="Tarik microsite?"
                description="Halaman publik akan menjadi 404 sampai dipublish ulang."
                confirmLabel="Tarik dari publik"
                confirmTone="danger"
                onConfirm={unpublish}
            />
        </AuthenticatedLayout>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">{label}</span>
            <div className="mt-2">{children}</div>
            <InputError message={error} className="mt-2" />
        </label>
    );
}

function TextInput({
    value,
    placeholder,
    onChange,
}: {
    value: string;
    placeholder?: string;
    onChange: (value: string) => void;
}) {
    return (
        <input
            type="text"
            value={value}
            placeholder={placeholder}
            onChange={(event) => onChange(event.target.value)}
            className="block w-full rounded-[4px] border-[#e6edef] text-sm shadow-sm focus:border-[#24695c] focus:ring-[#24695c]"
        />
    );
}

function Toggle({
    label,
    checked,
    onChange,
}: {
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
}) {
    return (
        <label className="flex items-center justify-between rounded-[4px] border border-[#e6edef] bg-white px-4 py-3">
            <span className="text-sm font-semibold text-[#242934]">{label}</span>
            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
                className="rounded border-[#e6edef] text-[#24695c] focus:ring-[#24695c]"
            />
        </label>
    );
}

function GalleryRow({
    item,
    canMoveUp,
    canMoveDown,
    onMoveUp,
    onMoveDown,
    onDelete,
}: {
    item: GalleryItem;
    canMoveUp: boolean;
    canMoveDown: boolean;
    onMoveUp: () => void;
    onMoveDown: () => void;
    onDelete: () => void;
}) {
    return (
        <div className="grid grid-cols-[72px_1fr_auto] items-center gap-3 rounded-[4px] border border-[#e6edef] bg-white p-2">
            <img
                src={item.imageUrl}
                alt=""
                className="h-14 w-16 rounded-[4px] object-cover"
            />
            <p className="min-w-0 truncate text-sm font-medium text-[#242934]">
                {item.caption || 'Tanpa caption'}
            </p>
            <div className="flex gap-1">
                <IconButton label="Naik" disabled={!canMoveUp} onClick={onMoveUp}>
                    <ArrowUp className="h-4 w-4" />
                </IconButton>
                <IconButton
                    label="Turun"
                    disabled={!canMoveDown}
                    onClick={onMoveDown}
                >
                    <ArrowDown className="h-4 w-4" />
                </IconButton>
                <IconButton label="Hapus" onClick={onDelete} danger>
                    <Trash2 className="h-4 w-4" />
                </IconButton>
            </div>
        </div>
    );
}

function IconButton({
    label,
    disabled = false,
    danger = false,
    onClick,
    children,
}: {
    label: string;
    disabled?: boolean;
    danger?: boolean;
    onClick: () => void;
    children: ReactNode;
}) {
    return (
        <button
            type="button"
            title={label}
            aria-label={label}
            disabled={disabled}
            onClick={onClick}
            className={cn(
                'inline-flex h-8 w-8 items-center justify-center rounded-[4px] border border-[#e6edef] bg-white text-sm transition disabled:cursor-not-allowed disabled:opacity-40',
                danger ? 'text-[#d22d3d]' : 'text-[#59667a] hover:text-[#24695c]',
            )}
        >
            {children}
        </button>
    );
}

function moveItem(items: GalleryItem[], from: number, to: number): GalleryItem[] {
    const nextItems = [...items];
    const [item] = nextItems.splice(from, 1);
    nextItems.splice(to, 0, item);

    return nextItems;
}
