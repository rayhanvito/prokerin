import { vihoMenu } from '@/Data/vihoMenu';
import { Link, usePage } from '@inertiajs/react';
import { Settings } from 'lucide-react';

import { cn } from '@/lib/utils';

export default function VihoSidebar() {
    const { app, auth } = usePage().props;
    const user = auth.user ?? {
        email: '',
        id: 0,
        name: 'Guest',
    };

    return (
        <aside className="fixed inset-y-0 left-0 z-40 hidden w-[255px] overflow-y-auto bg-white shadow-[0_0_11px_rgba(69,110,243,0.13)] lg:block">
            <div className="flex h-[77px] items-center border-b border-[#e6edef] px-7">
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

            <div className="relative px-5 py-6 text-center shadow-[3px_2px_7px_-1px_rgba(68,102,242,0.13)]">
                <Link
                    href={route('profile.edit')}
                    className="absolute right-5 top-5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#24695c] text-white shadow-[0_0_15px_rgba(68,102,242,0.25)]"
                >
                    <Settings className="h-4 w-4" />
                </Link>
                <img
                    src="/vendor/viho/images/dashboard/1.png"
                    alt=""
                    className="mx-auto h-[90px] w-[90px] rounded-full object-cover shadow-[0_0_15px_rgba(68,102,242,0.3)]"
                />
                <p className="mt-3 text-sm font-semibold uppercase tracking-[1.5px] text-[#24695c]">
                    {user.name}
                </p>
                <p className="mb-0 text-[11px] text-[#59667a]">
                    {app.activeOrganization.role}
                </p>
                <div className="mt-4 grid grid-cols-3 divide-x divide-[#e6edef] text-center">
                    <div>
                        <p className="text-sm font-semibold text-[#242934]">
                            12
                        </p>
                        <p className="text-[11px] text-[#717171]">Proker</p>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-[#242934]">
                            86
                        </p>
                        <p className="text-[11px] text-[#717171]">Task</p>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-[#242934]">
                            238
                        </p>
                        <p className="text-[11px] text-[#717171]">Member</p>
                    </div>
                </div>
            </div>

            <nav className="px-[15px] py-5">
                <div className="mb-5 rounded-[4px] bg-[#f5f7fb] p-3">
                    <p className="text-xs font-semibold text-[#24695c]">
                        {app.activeOrganization.name}
                    </p>
                    <p className="mt-1 text-[11px] text-[#717171]">
                        Periode {app.activeOrganization.period}
                    </p>
                </div>
                {vihoMenu.map((section) => (
                    <div key={section.title} className="mb-5">
                        <p className="mb-3 px-3 text-[12px] font-semibold text-[#24695c]">
                            {section.title}
                        </p>
                        <div className="space-y-1">
                            {section.items.map((item) => {
                                const Icon = item.icon;
                                const active = isActiveMenu(item.href);
                                const childActive = item.children?.some(
                                    (child) => isActiveMenu(child.href),
                                );

                                return (
                                    <div key={item.title}>
                                        <Link
                                            href={item.href}
                                            className={cn(
                                                'group flex min-h-11 items-center gap-[14px] rounded-[4px] px-3 py-2 text-sm font-medium tracking-[0.04em] transition',
                                                active || childActive
                                                    ? 'bg-[rgba(36,105,92,0.08)] text-[#24695c]'
                                                    : 'text-[#242934] hover:bg-[rgba(36,105,92,0.06)] hover:text-[#24695c]',
                                            )}
                                        >
                                            <Icon className="h-[16px] w-[16px]" />
                                            <span className="flex-1">
                                                {item.title}
                                            </span>
                                            {item.badge && (
                                                <span className="rounded-[3px] bg-[#ba895d] px-2 py-0.5 text-[10px] font-bold text-white">
                                                    {item.badge}
                                                </span>
                                            )}
                                        </Link>
                                        {item.children && (
                                            <div className="ml-[42px] mt-1 space-y-1 border-l border-[#e6edef] pl-4">
                                                {item.children.map((child) => (
                                                    <Link
                                                        key={child.title}
                                                        href={child.href}
                                                        className={cn(
                                                            'block py-1.5 text-[13px] tracking-[0.04em] transition hover:text-[#24695c]',
                                                            isActiveMenu(
                                                                child.href,
                                                            )
                                                                ? 'font-semibold text-[#24695c]'
                                                                : 'text-[#717171]',
                                                        )}
                                                    >
                                                        {child.title}
                                                    </Link>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </nav>
        </aside>
    );
}

function isActiveMenu(href: string): boolean {
    return href !== '#' && window.location.href === href;
}
