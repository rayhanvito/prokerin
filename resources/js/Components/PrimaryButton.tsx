import { ButtonHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={cn(
                'inline-flex items-center rounded-[4px] border border-transparent bg-[#24695c] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-[#1b4c43] focus:bg-[#1b4c43] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2 active:bg-[#1b4c43]',
                disabled && 'opacity-25',
                className,
            )}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
