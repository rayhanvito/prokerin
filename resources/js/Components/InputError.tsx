import { HTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function InputError({
    message,
    className = '',
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p
            {...props}
            className={cn('text-sm text-[#d22d3d]', className)}
        >
            {message}
        </p>
    ) : null;
}
