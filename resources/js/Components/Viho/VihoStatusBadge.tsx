import { cn } from '@/lib/utils';

type VihoStatusTone = 'primary' | 'secondary' | 'success' | 'danger' | 'muted';

interface VihoStatusBadgeProps {
    children: string;
    tone?: VihoStatusTone;
}

const toneClass: Record<VihoStatusTone, string> = {
    primary: 'bg-[rgba(36,105,92,0.1)] text-[#24695c]',
    secondary: 'bg-[rgba(186,137,93,0.12)] text-[#ba895d]',
    success: 'bg-[rgba(27,76,67,0.1)] text-[#1b4c43]',
    danger: 'bg-[rgba(210,45,61,0.1)] text-[#d22d3d]',
    muted: 'bg-[#f5f7fb] text-[#59667a]',
};

export default function VihoStatusBadge({
    children,
    tone = 'secondary',
}: VihoStatusBadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex w-fit rounded-[4px] px-3 py-1 text-xs font-semibold',
                toneClass[tone],
            )}
        >
            {children}
        </span>
    );
}
