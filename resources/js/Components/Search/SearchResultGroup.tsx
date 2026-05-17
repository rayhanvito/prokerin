import { Link } from '@inertiajs/react';
import {
    CalendarDays,
    CheckSquare,
    FileText,
    FolderKanban,
    LucideIcon,
    Users,
} from 'lucide-react';

import { SearchResultItem } from '@/hooks/useGlobalSearch';

const icons: Record<SearchResultItem['type'], LucideIcon> = {
    project: FolderKanban,
    task: CheckSquare,
    document: FileText,
    meeting: CalendarDays,
    member: Users,
};

interface SearchResultGroupProps {
    title: string;
    items: SearchResultItem[];
    activeHref: string | null;
    onChoose: (item: SearchResultItem) => void;
}

export default function SearchResultGroup({
    title,
    items,
    activeHref,
    onChoose,
}: SearchResultGroupProps) {
    if (items.length === 0) {
        return null;
    }

    return (
        <section>
            <h3 className="px-3 pb-2 text-[11px] font-semibold uppercase text-[#717171]">
                {title}
            </h3>
            <div className="space-y-1">
                {items.map((item) => {
                    const Icon = icons[item.type];
                    const active = activeHref === item.href;

                    return (
                        <Link
                            key={`${item.type}-${item.id}`}
                            href={item.href}
                            onClick={() => onChoose(item)}
                            className={`flex items-center gap-3 rounded-[4px] px-3 py-2.5 text-left transition ${
                                active
                                    ? 'bg-[#24695c] text-white'
                                    : 'text-[#242934] hover:bg-[#f5f7fb]'
                            }`}
                        >
                            <span
                                className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-[4px] ${
                                    active
                                        ? 'bg-white/15'
                                        : 'bg-[#f5f7fb] text-[#24695c]'
                                }`}
                            >
                                <Icon className="h-4 w-4" />
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="block truncate text-sm font-semibold">
                                    {item.title}
                                </span>
                                <span
                                    className={`block truncate text-xs ${
                                        active
                                            ? 'text-white/75'
                                            : 'text-[#717171]'
                                    }`}
                                >
                                    {item.subtitle}
                                </span>
                            </span>
                        </Link>
                    );
                })}
            </div>
        </section>
    );
}
