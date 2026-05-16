import { DownloadCloud, UploadCloud } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoDataTable from '@/Components/Viho/VihoDataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { humanizeStatus } from '@/lib/format';
import { Head } from '@inertiajs/react';

interface UploadValidation {
    isValid: boolean;
    errors: string[];
    requiresSignedUrl: boolean;
}

interface DocumentUploadRow {
    id: number;
    name: string;
    folder: string;
    owner: string;
    visibility: string;
    status: string;
    downloadHref: string;
}

interface UploadCenterProps {
    documents: DocumentUploadRow[];
    uploadValidation: UploadValidation;
}

export default function UploadCenter({
    documents,
    uploadValidation,
}: UploadCenterProps) {
    const rows = documents.map((document) => ({
        file: document.name,
        folder: document.folder,
        owner: document.owner,
        status: humanizeStatus(document.status),
        visibility: humanizeStatus(document.visibility),
    }));

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
                <VihoCard>
                    <div className="rounded-[4px] border border-dashed border-[#e6edef] bg-[#f5f7fb] p-8 text-center">
                        <UploadCloud className="mx-auto h-10 w-10 text-[#24695c]" />
                        <h2 className="mt-4 text-lg font-semibold text-[#242934]">
                            Drop files here
                        </h2>
                        <p className="mt-2 text-sm text-[#717171]">
                            Upload backend nanti harus validasi MIME, ukuran
                            file, dan simpan di storage private.
                        </p>
                        <div className="mt-4 text-sm font-semibold text-[#24695c]">
                            {uploadValidation.isValid
                                ? 'Validasi contoh file siap'
                                : uploadValidation.errors.join(', ')}
                            {uploadValidation.requiresSignedUrl &&
                                ' · download wajib signed URL'}
                        </div>
                    </div>
                </VihoCard>

                <VihoCard title="Recent Uploads">
                    <div className="space-y-4">
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
                            {documents.map((document) => (
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
                    </div>
                </VihoCard>
            </div>
        </AuthenticatedLayout>
    );
}
