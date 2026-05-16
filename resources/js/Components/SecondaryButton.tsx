import { ButtonHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            type={type}
            className={cn(
                'inline-flex items-center rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#59667a] shadow-sm transition duration-150 ease-in-out hover:bg-[#f5f7fb] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2 disabled:opacity-25',
                disabled && 'opacity-25',
                className,
            )}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
