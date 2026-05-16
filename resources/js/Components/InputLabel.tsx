import { LabelHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}: LabelHTMLAttributes<HTMLLabelElement> & { value?: string }) {
    return (
        <label
            {...props}
            className={cn('block text-sm font-semibold text-[#242934]', className)}
        >
            {value ? value : children}
        </label>
    );
}
