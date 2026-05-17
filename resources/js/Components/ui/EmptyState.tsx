import { Link } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

interface EmptyStateAction {
    label: string;
    onClick?: () => void;
    href?: string;
}

interface EmptyStateProps {
    icon: LucideIcon;
    title: string;
    description: string;
    action?: EmptyStateAction;
}

export default function EmptyState({
    icon: Icon,
    title,
    description,
    action,
}: EmptyStateProps) {
    return (
        <div className="rounded-[4px] border border-[#e6edef] bg-white px-6 py-8 text-center shadow-sm">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#24695c]/10 text-[#24695c]">
                <Icon className="h-6 w-6" aria-hidden="true" />
            </div>
            <h3 className="mt-4 text-base font-semibold text-[#242934]">
                {title}
            </h3>
            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-[#59667a]">
                {description}
            </p>
            {action ? (
                <div className="mt-5">
                    {action.href ? (
                        <Link
                            href={action.href}
                            className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1b4c43]"
                        >
                            {action.label}
                        </Link>
                    ) : (
                        <button
                            type="button"
                            onClick={action.onClick}
                            className={cn(
                                'inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1b4c43]',
                                !action.onClick &&
                                    'cursor-not-allowed opacity-60',
                            )}
                            disabled={!action.onClick}
                        >
                            {action.label}
                        </button>
                    )}
                </div>
            ) : null}
        </div>
    );
}
