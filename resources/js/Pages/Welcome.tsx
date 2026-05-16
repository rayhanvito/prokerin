import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarCheck,
    CheckCircle2,
    FileText,
    FolderKanban,
    ReceiptText,
    ShieldCheck,
} from 'lucide-react';

import { PageProps } from '@/types';

const features = [
    {
        title: 'Timeline proker',
        description: 'Rencana, milestone, PIC, dan deadline dalam satu alur.',
        icon: CalendarCheck,
    },
    {
        title: 'Task committee',
        description: 'Pantau tugas lintas divisi tanpa chat yang tenggelam.',
        icon: CheckCircle2,
    },
    {
        title: 'RAB terkendali',
        description: 'Draft anggaran, realisasi, bukti, dan approval bendahara.',
        icon: ReceiptText,
    },
    {
        title: 'Proposal & LPJ',
        description: 'Dokumen kerja organisasi lebih rapi sejak awal kegiatan.',
        icon: FileText,
    },
];

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Prokerin" />
            <main className="min-h-screen bg-[#f5f7fb] text-[#242934]">
                <header className="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                    <Link href="/" className="flex items-center gap-3">
                        <img
                            src="/vendor/viho/images/logo/icon-logo.png"
                            alt=""
                            className="h-8 w-8"
                        />
                        <span className="text-lg font-bold tracking-[0.04em] text-[#242934]">
                            PROKERIN
                        </span>
                    </Link>

                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                            >
                                Dashboard
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="rounded-[4px] px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:bg-white hover:text-[#242934]"
                                >
                                    Login
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                                >
                                    Mulai
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <section className="mx-auto grid max-w-7xl gap-8 px-4 pb-10 pt-6 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:pb-16 lg:pt-10">
                    <div className="flex flex-col justify-center">
                        <div className="inline-flex w-fit items-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-3 py-1 text-sm font-semibold text-[#24695c] shadow-sm">
                            <ShieldCheck className="h-4 w-4" />
                            MVP untuk BEM, HIMA, UKM, dan committee
                        </div>
                        <h1 className="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-[#242934] sm:text-5xl lg:text-6xl">
                            Kelola proker organisasi tanpa chaos operasional.
                        </h1>
                        <p className="mt-5 max-w-2xl text-lg leading-8 text-[#59667a]">
                            Prokerin menyatukan proposal, timeline, task, RAB,
                            dokumentasi, dan LPJ supaya pengurus bisa fokus ke
                            eksekusi, bukan mengejar file dan follow-up manual.
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <Link
                                href={
                                    auth.user
                                        ? route('dashboard')
                                        : route('register')
                                }
                                className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-5 py-3 text-sm font-semibold text-white shadow-[0_25px_50px_rgba(8,21,66,0.06)] transition hover:bg-[#1b4c43]"
                            >
                                Buka Workspace
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link
                                href={
                                    auth.user
                                        ? route('dashboard')
                                        : route('login')
                                }
                                className="inline-flex items-center justify-center rounded-[4px] border border-[#e6edef] bg-white px-5 py-3 text-sm font-semibold text-[#59667a] transition hover:text-[#242934]"
                            >
                                Lihat Demo Flow
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-[4px] border border-[#e6edef] bg-white p-4 shadow-[0_25px_50px_rgba(8,21,66,0.06)]">
                        <div className="rounded-[4px] bg-[#24695c] p-5 text-white">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-semibold text-white/75">
                                        BEM Fakultas Teknologi
                                    </p>
                                    <h2 className="mt-1 text-2xl font-semibold">
                                        12 proker aktif
                                    </h2>
                                </div>
                                <FolderKanban className="h-8 w-8 text-white/75" />
                            </div>
                            <div className="mt-6 grid grid-cols-3 gap-3">
                                {['Proposal', 'RAB', 'LPJ'].map((item) => (
                                    <div
                                        key={item}
                                        className="rounded-[4px] bg-white/10 p-3"
                                    >
                                        <p className="text-xs text-white/70">
                                            Queue
                                        </p>
                                        <p className="mt-1 font-semibold">
                                            {item}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="mt-4 space-y-3">
                            {features.map((feature) => {
                                const Icon = feature.icon;

                                return (
                                    <div
                                        key={feature.title}
                                        className="flex gap-4 rounded-[4px] border border-[#e6edef] bg-[#f5f7fb] p-4"
                                    >
                                        <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-[4px] bg-white text-[#24695c] shadow-sm">
                                            <Icon className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h3 className="font-semibold text-[#242934]">
                                                {feature.title}
                                            </h3>
                                            <p className="mt-1 text-sm leading-6 text-[#717171]">
                                                {feature.description}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
