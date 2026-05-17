import { Images } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';

export interface GalleryItem {
    id: number;
    imageUrl: string;
    caption: string;
}

interface GalleryGridProps {
    items: GalleryItem[];
}

export default function GalleryGrid({ items }: GalleryGridProps) {
    if (items.length === 0) {
        return (
            <EmptyState
                icon={Images}
                title="Galeri belum tersedia"
                description="Dokumentasi kegiatan akan tampil di sini setelah panitia menambahkan foto."
            />
        );
    }

    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {items.map((item) => (
                <figure
                    key={item.id}
                    className="overflow-hidden rounded-[4px] bg-white shadow-sm ring-1 ring-[#e6edef]"
                >
                    <img
                        src={item.imageUrl}
                        alt={item.caption || 'Dokumentasi proker'}
                        className="aspect-[4/3] w-full object-cover"
                    />
                    {item.caption ? (
                        <figcaption className="px-4 py-3 text-sm text-[#59667a]">
                            {item.caption}
                        </figcaption>
                    ) : null}
                </figure>
            ))}
        </div>
    );
}
