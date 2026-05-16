import { Link } from '@inertiajs/react';
import { Award, BarChart3, CalendarDays, ClipboardList, FileText, FolderOpen, QrCode, ReceiptText } from 'lucide-react';

import CtaBanner from '@/Components/Landing/CtaBanner';
import LandingLayout from '@/Layouts/LandingLayout';

const featureGroups = [
    {
        id: 'perencanaan',
        category: 'Perencanaan & Eksekusi',
        icon: ClipboardList,
        title: 'Proker, Template, Timeline, dan Task',
        description:
            'Buat proker dari template, susun timeline, assign PIC, dan pantau progres tanpa menunggu rekap manual dari tiap divisi.',
        bullets: ['Template proker siap pakai', 'Kanban dan kalender task', 'Progress dashboard per kegiatan'],
    },
    {
        id: 'dokumen',
        category: 'Dokumen & Proposal',
        icon: FileText,
        title: 'Proposal, Dokumen, dan LPJ Otomatis',
        description:
            'Data proker yang sudah diisi mengalir ke proposal, dokumen pendukung, dan LPJ. Pengurus tidak perlu copy-paste dari nol setiap deadline.',
        bullets: ['Draft proposal dari data proker', 'Upload center tenant-scoped', 'LPJ readiness checklist'],
    },
    {
        id: 'keuangan',
        category: 'Keuangan',
        icon: ReceiptText,
        title: 'RAB dan Realisasi yang Terkontrol',
        description:
            'Bendahara bisa menyusun RAB, mencatat realisasi, upload bukti, dan mengajukan approval dalam satu alur yang transparan.',
        bullets: ['Budget vs realisasi', 'Approval bendahara/admin', 'Receipt storage privat'],
    },
    {
        id: 'operasional',
        category: 'Operasional',
        icon: CalendarDays,
        title: 'Rapat, Notulen, dan Absensi QR',
        description:
            'Rapat punya agenda, keputusan, action item, dan absensi QR yang tersimpan bersama proker terkait.',
        bullets: ['Agenda dan notulen rapat', 'QR attendance', 'Manual fallback terkontrol'],
    },
    {
        id: 'sertifikat',
        category: 'Pasca Proker',
        icon: Award,
        title: 'Sertifikat Digital dan Dashboard Monitoring',
        description:
            'Terbitkan sertifikat terverifikasi dan pantau kesehatan organisasi dari dashboard yang menggabungkan proker, task, finance, dan anggota.',
        bullets: ['Nomor sertifikat unik', 'Verifikasi publik', 'Dashboard lintas modul'],
    },
];

const comingSoon = ['WhatsApp Reminder', 'AI Assistant', 'Campus Dashboard'];

export default function Features() {
    return (
        <LandingLayout
            title="Semua Fitur Prokerin — Platform Proker Lengkap untuk Ormawa | Prokerin"
            description="Lihat semua fitur Prokerin: manajemen proker, proposal otomatis, RAB & keuangan, absensi QR, LPJ otomatis, sertifikat digital. Dirancang untuk BEM, HIMA, dan UKM Indonesia."
            canonicalPath="/features"
        >
            <section className="bg-[linear-gradient(135deg,#e8f5f2,#ffffff)] px-4 pb-20 pt-36 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-4xl text-center">
                    <h1 className="font-['Plus_Jakarta_Sans'] text-4xl font-extrabold text-[#242934] md:text-6xl">
                        Semua yang Kamu Butuhkan untuk Kelola Organisasi
                    </h1>
                    <p className="mt-6 text-lg leading-8 text-[#59667a]">
                        Fitur-fitur Prokerin dirancang berdasarkan alur kerja
                        nyata BEM dan HIMA Indonesia — bukan adaptasi dari tools
                        Barat.
                    </p>
                </div>
            </section>

            <section className="bg-white px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl space-y-8">
                    {featureGroups.map((feature) => {
                        const Icon = feature.icon;

                        return (
                            <article
                                id={feature.id}
                                key={feature.id}
                                className="grid gap-8 rounded-2xl border border-[#e6edef] bg-white p-6 shadow-sm lg:grid-cols-[1fr_420px] lg:p-8"
                            >
                                <div>
                                    <div className="flex flex-wrap items-center gap-3">
                                        <span className="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e8f5f2] text-[#24695c]">
                                            <Icon className="h-6 w-6" />
                                        </span>
                                        <span className="rounded-full bg-[#f5ede4] px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[#ba895d]">
                                            {feature.category}
                                        </span>
                                    </div>
                                    <h2 className="mt-6 font-['Plus_Jakarta_Sans'] text-2xl font-bold text-[#242934] md:text-3xl">
                                        {feature.title}
                                    </h2>
                                    <p className="mt-4 text-base leading-8 text-[#59667a]">
                                        {feature.description}
                                    </p>
                                    <ul className="mt-6 grid gap-3 sm:grid-cols-2">
                                        {feature.bullets.map((bullet) => (
                                            <li
                                                key={bullet}
                                                className="rounded-xl bg-[#f5f7fb] px-4 py-3 text-sm font-semibold text-[#242934]"
                                            >
                                                ✓ {bullet}
                                            </li>
                                        ))}
                                    </ul>
                                    <Link
                                        href={route('register')}
                                        className="mt-6 inline-flex text-sm font-semibold text-[#24695c]"
                                    >
                                        Pelajari lebih lanjut →
                                    </Link>
                                </div>
                                <FeaturePageVisual />
                            </article>
                        );
                    })}
                </div>
            </section>

            <section className="bg-[#f5f7fb] px-4 py-16 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl rounded-2xl bg-white p-8 shadow-sm ring-1 ring-[#e6edef]">
                    <div className="flex items-start gap-4">
                        <span className="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e8f5f2] text-[#24695c]">
                            <BarChart3 className="h-6 w-6" />
                        </span>
                        <div>
                            <h2 className="font-['Plus_Jakarta_Sans'] text-2xl font-bold text-[#242934]">
                                Fitur dalam pengembangan
                            </h2>
                            <div className="mt-4 flex flex-wrap gap-3">
                                {comingSoon.map((item) => (
                                    <span
                                        key={item}
                                        className="rounded-full bg-[#f5f7fb] px-4 py-2 text-sm font-semibold text-[#59667a]"
                                    >
                                        {item}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <CtaBanner />
        </LandingLayout>
    );
}

function FeaturePageVisual() {
    return (
        <svg
            viewBox="0 0 420 280"
            className="h-auto w-full rounded-2xl bg-[#f8fafc]"
            role="img"
            aria-label="Mockup fitur Prokerin untuk organisasi mahasiswa"
        >
            <rect width="420" height="280" rx="20" fill="#f8fafc" />
            <rect x="28" y="30" width="170" height="16" rx="8" fill="#242934" />
            <rect x="28" y="72" width="364" height="54" rx="14" fill="#ffffff" />
            <rect x="52" y="92" width="160" height="9" rx="5" fill="#24695c" />
            <rect x="52" y="108" width="248" height="8" rx="4" fill="#d9e4e7" />
            <rect x="28" y="148" width="170" height="92" rx="16" fill="#e8f5f2" />
            <rect x="222" y="148" width="170" height="92" rx="16" fill="#f5ede4" />
            <FolderOpen x="72" y="178" width="34" height="34" color="#24695c" />
            <QrCode x="270" y="178" width="34" height="34" color="#ba895d" />
        </svg>
    );
}
