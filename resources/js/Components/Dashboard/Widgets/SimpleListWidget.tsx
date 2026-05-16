import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import type { SimpleItem } from '@/types/dashboard';

interface SimpleListWidgetProps {
    title: string;
    subtitle?: string;
    emptyText: string;
    items: SimpleItem[];
}

export default function SimpleListWidget({
    title,
    subtitle,
    emptyText,
    items,
}: SimpleListWidgetProps) {
    return (
        <VihoCard title={title} subtitle={subtitle}>
            <div className="-m-5 divide-y divide-[#e6edef]">
                {items.length > 0 ? (
                    items.map((item, index) => (
                        <div
                            key={String(item.id ?? item.title ?? item.name ?? index)}
                            className="p-5"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <p className="font-semibold text-[#242934]">
                                    {item.title ?? item.name ?? item.projectName ?? item.label}
                                </p>
                                {item.status !== undefined && (
                                    <VihoStatusBadge>
                                        {String(item.status)}
                                    </VihoStatusBadge>
                                )}
                            </div>
                            <p className="mt-1 text-sm text-[#717171]">
                                {item.meta ??
                                    item.projectName ??
                                    item.role ??
                                    item.email ??
                                    item.message ??
                                    item.startsAt ??
                                    item.dueAt ??
                                    '-'}
                            </p>
                        </div>
                    ))
                ) : (
                    <div className="p-5 text-sm text-[#59667a]">
                        {emptyText}
                    </div>
                )}
            </div>
        </VihoCard>
    );
}
