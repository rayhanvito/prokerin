import { InertiaLinkProps, Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={cn(
                'flex w-full items-start border-l-4 py-2 pe-4 ps-3 text-base font-medium transition duration-150 ease-in-out focus:outline-none',
                active
                    ? 'border-[#24695c] bg-[#24695c]/10 text-[#24695c] focus:border-[#1b4c43] focus:bg-[#24695c]/15'
                    : 'border-transparent text-[#59667a] hover:border-[#d7e2e5] hover:bg-[#f5f7fb] hover:text-[#24695c] focus:border-[#24695c] focus:bg-[#f5f7fb] focus:text-[#24695c]',
                className,
            )}
        >
            {children}
        </Link>
    );
}
