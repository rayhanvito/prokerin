import { useMemo, useState } from 'react';

import { FileText, Folder, LockKeyhole, Search, Users } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';

interface FolderDocument {
    id: number;
    name: string;
    visibility: string;
    status: string;
    downloadHref: string;
}

interface DocumentFolder {
    name: string;
    files: number;
    access: string;
    documents: FolderDocument[];
}

interface DocumentFoldersProps {
    folders: DocumentFolder[];
}

export default function DocumentFolders({ folders }: DocumentFoldersProps) {
    const [selectedFolder, setSelectedFolder] = useState<string>(
        folders[0]?.name ?? '',
    );
    const [search, setSearch] = useState('');
    const activeFolder = useMemo(
        () => folders.find((folder) => folder.name === selectedFolder),
        [folders, selectedFolder],
    );
    const visibleDocuments = useMemo(() => {
        const query = search.trim().toLowerCase();

        if (!activeFolder) {
            return [];
        }

        if (query.length === 0) {
            return activeFolder.documents;
        }

        return activeFolder.documents.filter((document) =>
            [document.name, document.visibility, document.status]
                .join(' ')
                .toLowerCase()
                .includes(query),
        );
    }, [activeFolder, search]);

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M09 · Folders
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Document Folders
                    </h1>
                </div>
            }
        >
            <Head title="Document Folders" />

            {folders.length === 0 ? (
                <EmptyState
                    icon={Folder}
                    title="Belum ada folder dokumen"
                    description="Folder akan muncul otomatis saat dokumen pertama diunggah ke organisasi aktif."
                    action={{
                        label: 'Upload Dokumen',
                        href: route('documents.upload-center'),
                    }}
                />
            ) : (
                <div className="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
                        {folders.map((folder) => (
                            <button
                                key={folder.name}
                                type="button"
                                onClick={() => setSelectedFolder(folder.name)}
                                className={cn(
                                    'rounded-[4px] border border-[#e6edef] bg-white p-5 text-left shadow-sm transition hover:border-[#24695c]',
                                    selectedFolder === folder.name &&
                                        'border-[#24695c] ring-2 ring-[#24695c]/10',
                                )}
                            >
                                <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Folder className="h-6 w-6" />
                                </span>
                                <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                                    {folder.name}
                                </h2>
                                <p className="mt-2 text-sm text-[#717171]">
                                    {folder.files} files
                                </p>
                                <div className="mt-5 flex items-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm text-[#59667a]">
                                    {folder.access === 'public' ||
                                    folder.access === 'committee' ? (
                                        <Users className="h-4 w-4" />
                                    ) : (
                                        <LockKeyhole className="h-4 w-4" />
                                    )}
                                    {humanizeStatus(folder.access)}
                                </div>
                            </button>
                        ))}
                    </section>

                    <VihoCard title={activeFolder?.name ?? 'Documents'}>
                        <label className="relative mb-4 block">
                            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#717171]" />
                            <span className="sr-only">Cari dokumen folder</span>
                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Cari file di folder ini..."
                                className="w-full rounded-[4px] border border-[#e6edef] py-2 pl-9 pr-3 text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                            />
                        </label>
                        <div className="divide-y divide-[#e6edef]">
                            {visibleDocuments.map((document) => (
                                <div
                                    key={document.id}
                                    className="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="flex items-start gap-3">
                                        <span className="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#f5f7fb] text-[#24695c]">
                                            <FileText className="h-4 w-4" />
                                        </span>
                                        <div>
                                            <h3 className="text-sm font-semibold text-[#242934]">
                                                {document.name}
                                            </h3>
                                            <p className="mt-1 text-xs text-[#717171]">
                                                {humanizeStatus(document.visibility)} ·{' '}
                                                {humanizeStatus(document.status)}
                                            </p>
                                        </div>
                                    </div>
                                    <Link
                                        href={document.downloadHref}
                                        className="inline-flex items-center justify-center rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                                    >
                                        Download
                                    </Link>
                                </div>
                            ))}
                            {visibleDocuments.length === 0 ? (
                                <div className="py-4">
                                    <EmptyState
                                        icon={FileText}
                                        title="Dokumen tidak ditemukan"
                                        description="Coba ubah kata kunci pencarian di folder aktif."
                                    />
                                </div>
                            ) : null}
                        </div>
                    </VihoCard>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
