import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

interface BreadcrumbItem {
    label: string;
    href?: string;
}

interface BreadcrumbProps {
    items: BreadcrumbItem[];
}

export default function Breadcrumb({ items }: BreadcrumbProps) {
    return (
        <nav aria-label="Breadcrumb" className="mb-4">
            <ol className="flex flex-wrap items-center gap-2 text-xs font-semibold text-[#717171]">
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <li
                            key={`${item.label}-${index}`}
                            className="flex items-center gap-2"
                        >
                            {item.href && !isLast ? (
                                <Link
                                    href={item.href}
                                    className="transition hover:text-[#24695c]"
                                >
                                    {item.label}
                                </Link>
                            ) : (
                                <span
                                    className={
                                        isLast ? 'text-[#242934]' : undefined
                                    }
                                >
                                    {item.label}
                                </span>
                            )}
                            {!isLast ? (
                                <ChevronRight
                                    className="h-3.5 w-3.5 text-[#59667a]"
                                    aria-hidden="true"
                                />
                            ) : null}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
