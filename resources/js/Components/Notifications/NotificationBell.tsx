import { Link, router, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/utils';
import { PageProps } from '@/types';

export default function NotificationBell() {
    const { props } = usePage<PageProps>();
    const notifications = props.notifications;
    const [open, setOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setOpen(false);
            }
        };

        if (open) {
            document.addEventListener('mousedown', handleClickOutside);
            return () =>
                document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [open]);

    const unreadCount = notifications?.unreadCount ?? 0;
    const recent = notifications?.recent ?? [];

    const markRead = (id: string, url: string | null) => {
        router.patch(
            route('notifications.read', { notification: id }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    if (url) {
                        window.location.href = url;
                    }
                },
            },
        );
    };

    const markAllRead = () => {
        router.patch(
            route('notifications.read-all'),
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    const toggleDropdown = () => {
        const nextOpen = !open;

        setOpen(nextOpen);

        if (nextOpen) {
            router.reload({
                only: ['notifications'],
            });
        }
    };

    return (
        <div className="relative" ref={containerRef}>
            <button
                type="button"
                onClick={toggleDropdown}
                className="relative inline-flex h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb]"
                aria-label="Notifikasi"
                aria-expanded={open}
            >
                <Bell className="h-[18px] w-[18px]" />
                {unreadCount > 0 && (
                    <span className="absolute right-1.5 top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-[#d22d3d] px-1 text-[10px] font-semibold text-white">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {open && (
                <div className="absolute right-0 top-12 z-50 w-80 rounded-[4px] border border-[#e6edef] bg-white shadow-lg">
                    <div className="flex items-center justify-between border-b border-[#e6edef] px-4 py-3">
                        <p className="text-sm font-semibold text-[#242934]">
                            Notifikasi
                        </p>
                        {unreadCount > 0 && (
                            <button
                                type="button"
                                onClick={markAllRead}
                                className="text-xs font-semibold text-[#24695c] hover:underline"
                            >
                                Tandai semua dibaca
                            </button>
                        )}
                    </div>

                    <ul
                        className="max-h-80 overflow-y-auto"
                        role="list"
                        aria-label="Notifikasi terbaru"
                    >
                        {recent.length === 0 ? (
                            <li className="px-4 py-6 text-center text-sm text-[#717171]">
                                Belum ada notifikasi.
                            </li>
                        ) : (
                            recent.map((item) => (
                                <li
                                    key={item.id}
                                    className={cn(
                                        'border-b border-[#f5f7fb] last:border-b-0',
                                        item.readAt === null && 'bg-[#f5f7fb]',
                                    )}
                                >
                                    <button
                                        type="button"
                                        onClick={() =>
                                            markRead(item.id, item.url)
                                        }
                                        className="block w-full text-left px-4 py-3 transition hover:bg-[#fafbfc]"
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <p className="text-sm font-semibold text-[#242934]">
                                                {item.title}
                                            </p>
                                            {item.readAt === null && (
                                                <span className="mt-1 inline-block h-2 w-2 shrink-0 rounded-full bg-[#24695c]" />
                                            )}
                                        </div>
                                        {item.body && (
                                            <p className="mt-1 line-clamp-2 text-xs text-[#59667a]">
                                                {item.body}
                                            </p>
                                        )}
                                        <p className="mt-1 text-[10px] font-semibold text-[#717171]">
                                            {item.createdAt}
                                        </p>
                                    </button>
                                </li>
                            ))
                        )}
                    </ul>

                    <div className="border-t border-[#e6edef] px-4 py-2 text-center">
                        <Link
                            href={route('notifications.index')}
                            className="text-xs font-semibold text-[#24695c] hover:underline"
                            onClick={() => setOpen(false)}
                        >
                            Lihat semua notifikasi
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
