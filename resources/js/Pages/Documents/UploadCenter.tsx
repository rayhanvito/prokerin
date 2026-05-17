import { ChangeEvent, DragEvent, FormEvent, useRef, useState } from 'react';

import { DownloadCloud, FileUp, FolderOpen, Search, UploadCloud } from 'lucide-react';

import EmptyState from '@/Components/ui/EmptyState';
import FormField from '@/Components/ui/FormField';
import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';

interface DocumentUploadRow {
    id: number;
    name: string;
    folder: string;
    owner: string;
    visibility: string;
    status: string;
    downloadHref: string;
}

interface ProjectOption {
    id: number;
    name: string;
}

interface UploadCenterProps {
    documents: DocumentUploadRow[];
    projects: ProjectOption[];
}

interface DocumentUploadForm {
    file: File | null;
    folder: string;
    visibility: 'private' | 'restricted' | 'committee' | 'public';
    project_id: string;
}

export default function UploadCenter({
    documents,
    projects,
}: UploadCenterProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [search, setSearch] = useState('');
    const [visibilityFilter, setVisibilityFilter] = useState('all');
    const form = useForm<DocumentUploadForm>({
        file: null,
        folder: 'Proposal',
        visibility: 'private',
        project_id: '',
    });

    const filteredDocuments = documents.filter((document) => {
        const query = search.trim().toLowerCase();
        const matchesSearch =
            query.length === 0 ||
            [document.name, document.folder, document.owner, document.status]
                .join(' ')
                .toLowerCase()
                .includes(query);
        const matchesVisibility =
            visibilityFilter === 'all' ||
            document.visibility === visibilityFilter;

        return matchesSearch && matchesVisibility;
    });

    const rows = filteredDocuments.map((document) => ({
        file: document.name,
        folder: document.folder,
        owner: document.owner,
        status: humanizeStatus(document.status),
        visibility: humanizeStatus(document.visibility),
    }));

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>): void => {
        form.setData('file', event.target.files?.[0] ?? null);
    };

    const handleDrop = (event: DragEvent<HTMLDivElement>): void => {
        event.preventDefault();
        setIsDragging(false);
        form.setData('file', event.dataTransfer.files.item(0));
    };

    const handleSubmit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        form.post(route('documents.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('file');
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M09 · Upload
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Upload Center
                    </h1>
                </div>
            }
        >
            <Head title="Upload Center" />

            <div className="space-y-6">
                <VihoCard title="Upload Document">
                    <form className="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]" onSubmit={handleSubmit}>
                        <div
                            onDrop={handleDrop}
                            onDragOver={(event) => {
                                event.preventDefault();
                                setIsDragging(true);
                            }}
                            onDragLeave={() => setIsDragging(false)}
                            className={cn(
                                'flex min-h-64 flex-col items-center justify-center rounded-[4px] border border-dashed border-[#e6edef] bg-[#f5f7fb] p-8 text-center transition',
                                isDragging && 'border-[#24695c] bg-[#24695c]/5',
                            )}
                        >
                            <UploadCloud className="h-10 w-10 text-[#24695c]" />
                            <h2 className="mt-4 text-lg font-semibold text-[#242934]">
                                Drop file here
                            </h2>
                            <p className="mt-2 max-w-md text-sm text-[#717171]">
                                PDF, Office document, image, or ZIP up to 10 MB.
                            </p>
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="mt-5 inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1b4c43]"
                            >
                                <FileUp className="h-4 w-4" />
                                Choose File
                            </button>
                            <input
                                ref={fileInputRef}
                                id="file"
                                type="file"
                                className="sr-only"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                                onChange={handleFileChange}
                            />
                            {form.data.file ? (
                                <p className="mt-4 text-sm font-semibold text-[#24695c]">
                                    {form.data.file.name}
                                </p>
                            ) : null}
                            {form.errors.file ? (
                                <p className="mt-3 text-sm text-[#d22d3d]">
                                    {form.errors.file}
                                </p>
                            ) : null}
                        </div>

                        <div className="space-y-4">
                            <FormField
                                label="Folder"
                                htmlFor="folder"
                                required
                                error={form.errors.folder}
                            >
                                <input
                                    id="folder"
                                    type="text"
                                    value={form.data.folder}
                                    onChange={(event) =>
                                        form.setData('folder', event.target.value)
                                    }
                                    className="w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                                />
                            </FormField>

                            <FormField
                                label="Visibility"
                                htmlFor="visibility"
                                required
                                error={form.errors.visibility}
                            >
                                <select
                                    id="visibility"
                                    value={form.data.visibility}
                                    onChange={(event) =>
                                        form.setData(
                                            'visibility',
                                            event.target.value as DocumentUploadForm['visibility'],
                                        )
                                    }
                                    className="w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                                >
                                    <option value="private">Private</option>
                                    <option value="restricted">Finance restricted</option>
                                    <option value="committee">Committee</option>
                                    <option value="public">Public to organization</option>
                                </select>
                            </FormField>

                            <FormField
                                label="Related Proker"
                                htmlFor="project_id"
                                hint="Optional. Required only for committee-scoped documents."
                                error={form.errors.project_id}
                            >
                                <select
                                    id="project_id"
                                    value={form.data.project_id}
                                    onChange={(event) =>
                                        form.setData('project_id', event.target.value)
                                    }
                                    className="w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                                >
                                    <option value="">No project</option>
                                    {projects.map((project) => (
                                        <option key={project.id} value={project.id}>
                                            {project.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            {form.progress ? (
                                <div className="rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm font-semibold text-[#24695c]">
                                    Uploading {form.progress.percentage}%
                                </div>
                            ) : null}

                            <button
                                type="submit"
                                disabled={form.processing}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <UploadCloud className="h-4 w-4" />
                                {form.processing ? 'Uploading...' : 'Upload Document'}
                            </button>
                        </div>
                    </form>
                </VihoCard>

                <VihoCard title="Recent Uploads">
                    <div className="space-y-4">
                        <div className="grid gap-3 md:grid-cols-[1fr_190px]">
                            <label className="relative">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#717171]" />
                                <span className="sr-only">Cari dokumen</span>
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(event) =>
                                        setSearch(event.target.value)
                                    }
                                    placeholder="Cari file, folder, owner..."
                                    className="w-full rounded-[4px] border border-[#e6edef] py-2 pl-9 pr-3 text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                                />
                            </label>
                            <select
                                value={visibilityFilter}
                                onChange={(event) =>
                                    setVisibilityFilter(event.target.value)
                                }
                                className="rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
                            >
                                <option value="all">Semua visibility</option>
                                <option value="private">Private</option>
                                <option value="restricted">Restricted</option>
                                <option value="committee">Committee</option>
                                <option value="public">Public</option>
                            </select>
                        </div>
                        <VihoDataTable
                            columns={[
                                { key: 'file', label: 'File' },
                                { key: 'folder', label: 'Folder' },
                                { key: 'owner', label: 'Owner' },
                                { key: 'visibility', label: 'Visibility' },
                                { key: 'status', label: 'Status' },
                            ]}
                            rows={rows}
                            statusKey="status"
                        />
                        <div className="grid gap-2 md:grid-cols-3">
                            {filteredDocuments.map((document) => (
                                <a
                                    key={document.id}
                                    href={document.downloadHref}
                                    className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                                >
                                    <DownloadCloud className="h-4 w-4" />
                                    {document.name}
                                </a>
                            ))}
                        </div>
                        {filteredDocuments.length === 0 ? (
                            <EmptyState
                                icon={FolderOpen}
                                title={
                                    documents.length === 0
                                        ? 'Belum ada dokumen'
                                        : 'Dokumen tidak ditemukan'
                                }
                                description={
                                    documents.length === 0
                                        ? 'Upload dokumen pertama agar arsip organisasi mulai tersusun.'
                                        : 'Coba ubah kata kunci atau filter visibility.'
                                }
                            />
                        ) : null}
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
