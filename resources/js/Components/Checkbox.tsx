import { InputHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function Checkbox({
    className = '',
    ...props
}: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            {...props}
            type="checkbox"
            className={cn(
                'rounded-[3px] border-[#d7e2e5] text-[#24695c] shadow-sm focus:ring-[#24695c]',
                className,
            )}
        />
    );
}
