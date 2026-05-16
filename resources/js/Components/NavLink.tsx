import { InertiaLinkProps, Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active: boolean }) {
    return (
        <Link
            {...props}
            className={cn(
                'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none',
                active
                    ? 'border-[#24695c] text-[#242934] focus:border-[#1b4c43]'
                    : 'border-transparent text-[#59667a] hover:border-[#d7e2e5] hover:text-[#24695c] focus:border-[#24695c] focus:text-[#24695c]',
                className,
            )}
        >
            {children}
        </Link>
    );
}
