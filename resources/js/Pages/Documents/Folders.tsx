import { Folder, LockKeyhole, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const folders = [
    { name: 'Proposal', files: '18 files', access: 'Secretary' },
    { name: 'Finance Receipts', files: '42 files', access: 'Treasurer' },
    { name: 'Documentation', files: '73 files', access: 'Committee' },
    { name: 'LPJ Archive', files: '13 files', access: 'Admin' },
];

export default function DocumentFolders() {
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

            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {folders.map((folder) => (
                    <VihoCard key={folder.name}>
                        <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                            <Folder className="h-6 w-6" />
                        </span>
                        <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                            {folder.name}
                        </h2>
                        <p className="mt-2 text-sm text-[#717171]">
                            {folder.files}
                        </p>
                        <div className="mt-5 flex items-center gap-2 rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm text-[#59667a]">
                            {folder.access === 'Committee' ? (
                                <Users className="h-4 w-4" />
                            ) : (
                                <LockKeyhole className="h-4 w-4" />
                            )}
                            {folder.access}
                        </div>
                    </VihoCard>
                ))}
            </section>
        </AuthenticatedLayout>
    );
}
