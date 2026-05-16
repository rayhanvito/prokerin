import { PropsWithChildren, ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface VihoCardProps {
    title?: string;
    subtitle?: string;
    action?: ReactNode;
    className?: string;
}

export default function VihoCard({
    title,
    subtitle,
    action,
    className = '',
    children,
}: PropsWithChildren<VihoCardProps>) {
    return (
        <section
            className={cn(
                'rounded-[4px] border border-[#e6edef] bg-white shadow-[0_25px_50px_rgba(8,21,66,0.06)]',
                className,
            )}
        >
            {(title || subtitle || action) && (
                <div className="flex flex-col gap-3 border-b border-[#e6edef] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        {title && (
                            <h2 className="text-base font-semibold capitalize text-[#242934]">
                                {title}
                            </h2>
                        )}
                        {subtitle && (
                            <p className="mt-1 text-xs leading-5 text-[#59667a]">
                                {subtitle}
                            </p>
                        )}
                    </div>
                    {action}
                </div>
            )}
            <div className="p-5">{children}</div>
        </section>
    );
}
