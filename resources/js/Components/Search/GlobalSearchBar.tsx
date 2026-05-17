import { router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import {
    KeyboardEvent as ReactKeyboardEvent,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';

import SearchResultGroup from '@/Components/Search/SearchResultGroup';
import {
    SearchResultItem,
    useGlobalSearch,
} from '@/hooks/useGlobalSearch';

const RECENT_SEARCHES_KEY = 'prokerin.recent-searches';

export default function GlobalSearchBar() {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [activeIndex, setActiveIndex] = useState(0);
    const [recentSearches, setRecentSearches] = useState<string[]>([]);
    const inputRef = useRef<HTMLInputElement>(null);
    const { results, loading, error, flatResults } = useGlobalSearch(query);

    useEffect(() => {
        const stored = window.localStorage.getItem(RECENT_SEARCHES_KEY);

        if (stored !== null) {
            setRecentSearches(JSON.parse(stored) as string[]);
        }
    }, []);

    useEffect(() => {
        const listener = (event: globalThis.KeyboardEvent) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                setOpen(true);
            }

            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        window.addEventListener('keydown', listener);

        return () => window.removeEventListener('keydown', listener);
    }, []);

    useEffect(() => {
        if (!open) {
            return;
        }

        window.setTimeout(() => inputRef.current?.focus(), 0);
    }, [open]);

    useEffect(() => {
        setActiveIndex(0);
    }, [query]);

    const activeItem = useMemo(
        () => flatResults[activeIndex] ?? null,
        [activeIndex, flatResults],
    );

    const rememberSearch = (value: string) => {
        const normalizedValue = value.trim();

        if (normalizedValue.length < 2) {
            return;
        }

        const nextSearches = [
            normalizedValue,
            ...recentSearches.filter((item) => item !== normalizedValue),
        ].slice(0, 5);

        setRecentSearches(nextSearches);
        window.localStorage.setItem(
            RECENT_SEARCHES_KEY,
            JSON.stringify(nextSearches),
        );
    };

    const chooseItem = (item: SearchResultItem) => {
        rememberSearch(query);
        setOpen(false);
        router.visit(item.href);
    };

    const handleKeyDown = (event: ReactKeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((previous) =>
                Math.min(previous + 1, Math.max(flatResults.length - 1, 0)),
            );
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((previous) => Math.max(previous - 1, 0));
        }

        if (event.key === 'Enter' && activeItem !== null) {
            event.preventDefault();
            chooseItem(activeItem);
        }
    };

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                className="hidden min-w-[320px] items-center rounded-[4px] bg-[#f5f7fb] px-4 py-3 text-left text-sm text-[#717171] transition hover:bg-[#eef3f1] md:flex"
            >
                <Search className="mr-3 h-4 w-4 text-[#24695c]" />
                <span className="flex-1">Search proker, task, dokumen...</span>
                <kbd className="rounded-[4px] border border-[#e6edef] bg-white px-1.5 py-0.5 text-[11px] text-[#59667a]">
                    ⌘K
                </kbd>
            </button>

            <button
                type="button"
                onClick={() => setOpen(true)}
                className="inline-flex h-10 w-10 items-center justify-center rounded-[4px] transition hover:bg-[#f5f7fb] md:hidden"
            >
                <Search className="h-[18px] w-[18px]" />
            </button>

            {open && (
                <div className="fixed inset-0 z-50 bg-[#242934]/40 px-4 py-16 backdrop-blur-sm">
                    <div className="mx-auto max-w-2xl overflow-hidden rounded-[4px] bg-white shadow-2xl">
                        <div className="flex items-center gap-3 border-b border-[#e6edef] px-4 py-3">
                            <Search className="h-5 w-5 text-[#24695c]" />
                            <input
                                ref={inputRef}
                                value={query}
                                onChange={(event) =>
                                    setQuery(event.target.value)
                                }
                                onKeyDown={handleKeyDown}
                                placeholder="Cari proker, task, dokumen, rapat, anggota"
                                className="h-10 min-w-0 flex-1 border-0 bg-transparent text-sm text-[#242934] outline-none ring-0 placeholder:text-[#717171] focus:ring-0"
                            />
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="flex h-9 w-9 items-center justify-center rounded-[4px] text-[#59667a] transition hover:bg-[#f5f7fb]"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <div className="max-h-[65vh] overflow-y-auto p-3">
                            {query.trim().length < 2 && (
                                <div className="space-y-3">
                                    <p className="px-3 text-sm text-[#717171]">
                                        Ketik minimal 2 karakter untuk mulai mencari.
                                    </p>
                                    {recentSearches.length > 0 && (
                                        <div>
                                            <h3 className="px-3 pb-2 text-[11px] font-semibold uppercase text-[#717171]">
                                                Recent searches
                                            </h3>
                                            <div className="flex flex-wrap gap-2 px-3">
                                                {recentSearches.map((item) => (
                                                    <button
                                                        key={item}
                                                        type="button"
                                                        onClick={() =>
                                                            setQuery(item)
                                                        }
                                                        className="rounded-[4px] border border-[#e6edef] px-2.5 py-1 text-xs text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                                                    >
                                                        {item}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {query.trim().length >= 2 && (
                                <div className="space-y-5">
                                    {loading && (
                                        <p className="px-3 text-sm text-[#717171]">
                                            Mencari...
                                        </p>
                                    )}
                                    {error !== null && (
                                        <p className="px-3 text-sm text-[#d22d3d]">
                                            {error}
                                        </p>
                                    )}
                                    {!loading &&
                                        error === null &&
                                        flatResults.length === 0 && (
                                            <p className="px-3 text-sm text-[#717171]">
                                                Tidak ada hasil.
                                            </p>
                                        )}
                                    <SearchResultGroup
                                        title="Proker"
                                        items={results.projects}
                                        activeHref={activeItem?.href ?? null}
                                        onChoose={chooseItem}
                                    />
                                    <SearchResultGroup
                                        title="Tugas"
                                        items={results.tasks}
                                        activeHref={activeItem?.href ?? null}
                                        onChoose={chooseItem}
                                    />
                                    <SearchResultGroup
                                        title="Dokumen"
                                        items={results.documents}
                                        activeHref={activeItem?.href ?? null}
                                        onChoose={chooseItem}
                                    />
                                    <SearchResultGroup
                                        title="Rapat"
                                        items={results.meetings}
                                        activeHref={activeItem?.href ?? null}
                                        onChoose={chooseItem}
                                    />
                                    <SearchResultGroup
                                        title="Anggota"
                                        items={results.members}
                                        activeHref={activeItem?.href ?? null}
                                        onChoose={chooseItem}
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
