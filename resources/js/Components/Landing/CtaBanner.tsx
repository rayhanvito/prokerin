import { Link } from '@inertiajs/react';
import { PlayCircle } from 'lucide-react';
import { useState } from 'react';

import DemoVideoModal from '@/Components/Landing/DemoVideoModal';

export default function CtaBanner() {
    const [isDemoOpen, setIsDemoOpen] = useState(false);

    return (
        <section className="bg-[linear-gradient(135deg,#24695c,#1b4c43)] px-4 py-20 text-white sm:px-6 lg:px-8">
            <div className="mx-auto max-w-4xl text-center">
                <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold md:text-4xl">
                    Siap Kelola Organisasi Tanpa Chaos?
                </h2>
                <p className="mt-4 text-lg text-white/75">
                    Gratis untuk 1 organisasi. Tidak perlu kartu kredit. Setup
                    dalam 5 menit.
                </p>
                <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <Link
                        href={route('register')}
                        className="rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#24695c]"
                    >
                        Daftar Sekarang — Gratis →
                    </Link>
                    <button
                        type="button"
                        onClick={() => setIsDemoOpen(true)}
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 px-8 py-4 text-base font-semibold text-white"
                    >
                        <PlayCircle className="h-5 w-5" />
                        Lihat Demo
                    </button>
                </div>
                <p className="mt-6 text-sm text-white/70">
                    ✓ Gratis selamanya untuk 1 organisasi · ✓ Tidak ada kartu
                    kredit · ✓ Cancel kapan saja
                </p>
            </div>
            <DemoVideoModal
                isOpen={isDemoOpen}
                onClose={() => setIsDemoOpen(false)}
            />
        </section>
    );
}
