import Dropdown from '@/Components/Dropdown';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { vihoMenu } from '@/Data/vihoMenu';
import { Link, usePage } from '@inertiajs/react';
import {
    Bell,
    Bookmark,
    Maximize,
    Menu,
    MessageCircle,
    Moon,
    Search,
    X,
} from 'lucide-react';
import { ReactNode } from 'react';

interface VihoHeaderProps {
    header?: ReactNode;
    mobileOpen: boolean;
    onToggleMobile: () => void;
}

export default function VihoHeader({
    header,
    mobileOpen,
    onToggleMobile,
}: VihoHeaderProps) {
    const { app, auth } = usePage().props;
    const user = auth.user ?? {
        email: '',
        id: 0,
        name: 'Guest',
    };

    const goFullScreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
            return;
        }

        document.exitFullscreen?.();
    };

    return (
        <header className="sticky top-0 z-30 bg-white shadow-[0_0_20px_rgba(4,122,251,0.04)]">
            <div className="flex min-h-[77px] items-center gap-4 px-4 sm:px-6 lg:px-8">
                <button
                    type="button"
                    onClick={onToggleMobile}
                    className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] border border-[#e6edef] text-[#59667a] transition hover:bg-[#f5f7fb] lg:hidden"
                >
                    {mobileOpen ? (
                        <X className="h-5 w-5" />
                    ) : (
                        <Menu className="h-5 w-5" />
                    )}
                </button>

                <div className="min-w-0 flex-1">{header}</div>

                <div className="hidden min-w-[320px] items-center rounded-[4px] bg-[#f5f7fb] px-4 py-3 text-sm text-[#717171] md:flex">
                    <Search className="mr-3 h-4 w-4 text-[#24695c]" />
                    <span>Search proker, proposal, RAB...</span>
                </div>

                <div className="flex items-center gap-1 text-[#2c323f]">
                    <button
                        type="button"
                        onClick={goFullScreen}
                        className="hidden h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] sm:inline-flex"
                    >
                        <Maximize className="h-[18px] w-[18px]" />
                    </button>
                    <button
                        type="button"
                        className="hidden h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] sm:inline-flex"
                    >
                        <Bookmark className="h-[18px] w-[18px]" />
                    </button>
                    <Link
                        href={route('notifications.index')}
                        className="relative h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] sm:inline-flex"
                    >
                        <Bell className="h-[18px] w-[18px]" />
                        <span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-[#d22d3d]" />
                    </Link>
                    <button
                        type="button"
                        className="hidden h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] sm:inline-flex"
                    >
                        <Moon className="h-[18px] w-[18px]" />
                    </button>
                    <button
                        type="button"
                        className="hidden h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] sm:inline-flex"
                    >
                        <MessageCircle className="h-[18px] w-[18px]" />
                    </button>

                    <Dropdown>
                        <Dropdown.Trigger>
                            <button
                                type="button"
                                className="ml-2 flex items-center gap-2 rounded-[4px] px-2 py-1.5 transition hover:bg-[#f5f7fb]"
                            >
                                <img
                                    src="/vendor/viho/images/user/1.jpg"
                                    alt=""
                                    className="h-9 w-9 rounded-full object-cover"
                                />
                                <span className="hidden text-left md:block">
                                    <span className="block text-sm font-semibold text-[#242934]">
                                        {user.name}
                                    </span>
                                    <span className="block text-[11px] text-[#717171]">
                                        {app.activeOrganization.period}
                                    </span>
                                </span>
                            </button>
                        </Dropdown.Trigger>
                        <Dropdown.Content>
                            <Dropdown.Link href={route('profile.edit')}>
                                Profile
                            </Dropdown.Link>
                            <Dropdown.Link
                                href={route('logout')}
                                method="post"
                                as="button"
                            >
                                Log Out
                            </Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </div>
            </div>

            <div
                className={
                    (mobileOpen ? 'block' : 'hidden') +
                    ' border-t border-[#e6edef] bg-white lg:hidden'
                }
            >
                <div className="space-y-1 px-4 py-3">
                    <div className="mb-4 flex items-center gap-3">
                        <Link href="/" className="flex items-center gap-3">
                            <img
                                src="/vendor/viho/images/logo/icon-logo.png"
                                alt=""
                                className="h-8 w-8"
                            />
                            <span className="text-lg font-bold tracking-[0.04em] text-[#242934]">
                                PROKERIN
                            </span>
                        </Link>
                    </div>
                    {vihoMenu.flatMap((section) =>
                        section.items.map((item) => (
                            <ResponsiveNavLink
                                key={`${section.title}-${item.title}`}
                                href={item.href}
                                active={isActiveMenu(item.href)}
                            >
                                {item.title}
                            </ResponsiveNavLink>
                        )),
                    )}
                </div>
            </div>
        </header>
    );
}

function isActiveMenu(href: string): boolean {
    return href !== '#' && window.location.href === href;
}
