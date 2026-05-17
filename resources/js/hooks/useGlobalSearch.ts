import axios from 'axios';
import { useEffect, useMemo, useState } from 'react';

export interface SearchResultItem {
    type: 'project' | 'task' | 'document' | 'meeting' | 'member';
    id: number;
    title: string;
    subtitle: string;
    href: string;
}

export interface SearchResults {
    query: string;
    projects: SearchResultItem[];
    tasks: SearchResultItem[];
    documents: SearchResultItem[];
    meetings: SearchResultItem[];
    members: SearchResultItem[];
}

interface SearchResponse {
    success: boolean;
    data: SearchResults;
    message: string;
}

const EMPTY_RESULTS: SearchResults = {
    query: '',
    projects: [],
    tasks: [],
    documents: [],
    meetings: [],
    members: [],
};

export function useGlobalSearch(query: string): {
    results: SearchResults;
    loading: boolean;
    error: string | null;
    flatResults: SearchResultItem[];
} {
    const [results, setResults] = useState<SearchResults>(EMPTY_RESULTS);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const normalizedQuery = query.trim();

        if (normalizedQuery.length < 2) {
            setResults({ ...EMPTY_RESULTS, query: normalizedQuery });
            setLoading(false);
            setError(null);
            return;
        }

        const timeoutId = window.setTimeout(() => {
            setLoading(true);
            setError(null);

            axios
                .get<SearchResponse>(route('search'), {
                    params: { q: normalizedQuery },
                })
                .then((response) => {
                    setResults(response.data.data);
                })
                .catch(() => {
                    setError('Search belum bisa dimuat.');
                })
                .finally(() => {
                    setLoading(false);
                });
        }, 300);

        return () => window.clearTimeout(timeoutId);
    }, [query]);

    const flatResults = useMemo(
        () => [
            ...results.projects,
            ...results.tasks,
            ...results.documents,
            ...results.meetings,
            ...results.members,
        ],
        [results],
    );

    return { results, loading, error, flatResults };
}
