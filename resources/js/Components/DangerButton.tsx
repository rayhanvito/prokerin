import { ButtonHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={cn(
                'inline-flex items-center rounded-[4px] border border-transparent bg-[#d22d3d] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-[#b62432] focus:outline-none focus:ring-2 focus:ring-[#d22d3d] focus:ring-offset-2 active:bg-[#b62432]',
                disabled && 'opacity-25',
                className,
            )}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
