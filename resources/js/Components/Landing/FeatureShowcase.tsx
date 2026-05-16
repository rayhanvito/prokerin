import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Award, ClipboardList, FileText, QrCode, ReceiptText, Target } from 'lucide-react';

import { cn } from '@/lib/utils';

const features = [
    {
        id: 'perencanaan',
        icon: ClipboardList,
        color: '#24695c',
        title: 'Buat dan Pantau Proker dengan Mudah',
        body: 'Mulai proker dari template yang sudah tersedia — ada task, timeline, dan draft RAB otomatis. Tidak perlu mulai dari nol setiap semester. Pantau progress semua proker dari satu dashboard.',
        visual: 'proker',
    },
    {
        id: 'dokumen',
        icon: FileText,
        color: '#0f766e',
        title: 'Proposal Terisi Otomatis dari Data Proker',
        body: 'Isi data proker sekali, Prokerin generate draft proposal secara otomatis. Template sesuai format kampus. Export ke PDF atau Word dalam hitungan detik.',
        visual: 'proposal',
    },
    {
        id: 'keuangan',
        icon: ReceiptText,
        color: '#f59e0b',
        title: 'Keuangan Proker Transparan dan Terkontrol',
        body: 'Planning budget, pengajuan, approval, sampai realisasi — semua dalam satu alur. Perbandingan RAB vs realisasi real-time.',
        visual: 'finance',
    },
    {
        id: 'operasional',
        icon: QrCode,
        color: '#7c3aed',
        title: 'Absensi Rapat Semudah Scan QR',
        body: 'Buka kamera, scan QR yang ditampilkan panitia, hadir tercatat. Laporan kehadiran otomatis per rapat dan per proker.',
        visual: 'attendance',
    },
    {
        id: 'lpj',
        icon: Target,
        color: '#22c55e',
        title: 'LPJ Terbentuk dari Data Nyata, Bukan Ingatan',
        body: 'Prokerin mengumpulkan data selama proker berjalan — task selesai, pengeluaran, kehadiran, dokumen. LPJ tinggal generate.',
        visual: 'lpj',
    },
    {
        id: 'sertifikat',
        icon: Award,
        color: '#ba895d',
        title: 'Sertifikat Digital yang Bisa Diverifikasi Online',
        body: 'Terbitkan sertifikat untuk anggota dan peserta event dengan satu klik. Setiap sertifikat punya QR verifikasi unik dan siap dibagikan.',
        visual: 'certificate',
    },
];

export default function FeatureShowcase() {
    return (
        <section className="bg-[#f5f7fb] px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        Semua yang Kamu Butuhkan, dalam Satu Platform
                    </h2>
                </div>
                <div className="mt-16 space-y-16">
                    {features.map((feature, index) => {
                        const Icon = feature.icon;
                        const isReversed = index % 2 === 1;

                        return (
                            <motion.article
                                key={feature.title}
                                initial={{ opacity: 0, y: 24 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ duration: 0.5 }}
                                className="grid items-center gap-8 lg:grid-cols-2"
                            >
                                <div className={cn(isReversed ? 'lg:order-2' : '')}>
                                    <span
                                        className="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm"
                                        style={{ color: feature.color }}
                                    >
                                        <Icon className="h-6 w-6" />
                                    </span>
                                    <h3 className="mt-6 font-['Plus_Jakarta_Sans'] text-2xl font-bold text-[#242934] md:text-3xl">
                                        {feature.title}
                                    </h3>
                                    <p className="mt-4 text-base leading-8 text-[#59667a]">
                                        {feature.body}
                                    </p>
                                    <Link
                                        href={`${route('landing.features')}#${feature.id}`}
                                        className="mt-6 inline-flex text-sm font-semibold text-[#24695c]"
                                    >
                                        Lihat fitur lengkap →
                                    </Link>
                                </div>
                                <FeatureMockup kind={feature.visual} />
                            </motion.article>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

function FeatureMockup({ kind }: { kind: string }) {
    return (
        <div className="rounded-2xl bg-white p-4 shadow-lg ring-1 ring-[#e6edef]">
            <svg
                viewBox="0 0 480 320"
                className="h-auto w-full rounded-2xl bg-[#f8fafc]"
                role="img"
                aria-label={`Ilustrasi fitur Prokerin ${kind}`}
            >
                <rect width="480" height="320" rx="22" fill="#f8fafc" />
                <rect x="32" y="34" width="180" height="18" rx="9" fill="#242934" />
                <rect x="32" y="72" width="416" height="64" rx="16" fill="#ffffff" />
                <rect x="58" y="96" width="190" height="10" rx="5" fill="#8ea0ad" />
                <rect x="58" y="114" width="290" height="8" rx="4" fill="#d9e4e7" />
                <rect x="32" y="160" width="196" height="112" rx="18" fill="#e8f5f2" />
                <rect x="254" y="160" width="194" height="112" rx="18" fill="#ffffff" />
                {kind === 'attendance' || kind === 'certificate' ? (
                    <>
                        <rect x="82" y="192" width="60" height="60" rx="8" fill="#24695c" />
                        <path d="M96 206h32v32H96zM106 216h12v12h-12z" fill="#ffffff" />
                    </>
                ) : (
                    <>
                        <rect x="58" y="194" width="130" height="12" rx="6" fill="#24695c" />
                        <rect x="58" y="222" width="104" height="10" rx="5" fill="#ba895d" />
                    </>
                )}
                <rect x="282" y="194" width="118" height="10" rx="5" fill="#24695c" />
                <rect x="282" y="222" width="136" height="10" rx="5" fill="#d9e4e7" />
                <rect x="282" y="248" width="94" height="10" rx="5" fill="#d9e4e7" />
            </svg>
        </div>
    );
}
