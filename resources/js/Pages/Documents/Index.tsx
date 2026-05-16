import { FolderOpen } from 'lucide-react';

import ModuleOverview from '@/Components/Prokerin/ModuleOverview';

export default function DocumentsIndex() {
    return (
        <ModuleOverview
            title="Document Management"
            eyebrow="M09 · Files"
            description="Kelola folder proker, proposal, bukti finance, dokumentasi kegiatan, dan file handover tanpa tercecer di banyak drive."
            actionLabel="Upload File"
            actionHref={route('documents.upload-center')}
            icon={FolderOpen}
            metrics={[
                { label: 'Folders', value: '28', note: 'Per organisasi dan proker' },
                { label: 'Files', value: '146', note: 'Dokumen aktif' },
                { label: 'Need Review', value: '9', note: 'Butuh validasi admin' },
            ]}
            items={[
                {
                    title: 'Dokumentasi Seminar Karier',
                    meta: '32 file · public summary pending',
                    status: 'Review',
                    href: route('documents.folders'),
                },
                {
                    title: 'Bukti transaksi Workshop UI/UX',
                    meta: '14 file · private finance folder',
                    status: 'Private',
                    href: route('documents.upload-center'),
                },
                {
                    title: 'Template Proposal HIMA',
                    meta: '5 file · reusable template',
                    status: 'Template',
                    href: route('documents.folders'),
                },
            ]}
            focus={[
                'Gunakan storage private, jangan simpan upload mentah di public.',
                'Terapkan signed URL untuk download.',
                'Folder harus terscope organization_id dan project_id.',
            ]}
        />
    );
}
