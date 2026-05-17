import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';

export default function HeroSection() {
    return (
        <section className="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#1b4c43_0%,#24695c_50%,#2d7a6a_100%)] px-4 pt-28 text-white sm:px-6 lg:px-8">
            <div className="absolute inset-0 opacity-[0.05] [background-image:radial-gradient(circle_at_1px_1px,#fff_1px,transparent_0)] [background-size:28px_28px]" />
            <div className="relative mx-auto grid min-h-[calc(100vh-7rem)] max-w-7xl items-center gap-12 py-16 lg:grid-cols-2">
                <div>
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white"
                    >
                        Khusus Organisasi Kampus Indonesia
                    </motion.div>
                    <motion.h1
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.1, duration: 0.5 }}
                        className="mt-7 font-['Plus_Jakarta_Sans'] text-5xl font-extrabold leading-tight md:text-6xl lg:text-7xl"
                    >
                        Kelola Proker, Proposal & LPJ{' '}
                        <span className="text-[#ba895d]">Tanpa Ribet</span>
                    </motion.h1>
                    <motion.p
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2, duration: 0.5 }}
                        className="mt-6 max-w-xl text-lg leading-8 text-white/80 md:text-xl"
                    >
                        Prokerin menyatukan proker, task, RAB, proposal, hingga
                        LPJ dalam satu platform — bukan 10 grup WhatsApp dan 5
                        Google Doc.
                    </motion.p>
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3, duration: 0.5 }}
                        className="mt-8 flex flex-col gap-3 sm:flex-row"
                    >
                        <Link
                            href="/register"
                            onClick={() => {}}
                            className="inline-flex items-center justify-center rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#24695c] shadow-lg transition hover:bg-gray-50"
                        >
                            Mulai Gratis Sekarang
                        </Link>
                        <Link
                            href="/features"
                            className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 px-8 py-4 text-base font-semibold text-white transition hover:bg-white/10"
                        >
                            Lihat Fitur Lengkap
                        </Link>
                        {/* DemoVideoModal diaktifkan saat video demo tersedia */}
                    </motion.div>
                    <motion.p
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.4, duration: 0.5 }}
                        className="mt-5 text-sm text-white/60"
                    >
                        Gratis untuk 5 anggota pertama. Tidak perlu kartu
                        kredit.
                    </motion.p>
                </div>

                <motion.div
                    initial={{ opacity: 0, y: 24 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.25, duration: 0.6 }}
                    className="landing-float"
                    aria-label="Dashboard Prokerin menampilkan manajemen program kerja BEM"
                >
                    <DashboardMockup />
                </motion.div>
            </div>
        </section>
    );
}

function DashboardMockup() {
    return (
        <svg
            viewBox="0 0 680 460"
            className="h-auto w-full rounded-2xl shadow-2xl"
            role="img"
            aria-labelledby="dashboard-mockup-title"
        >
            <title id="dashboard-mockup-title">
                Dashboard Prokerin menampilkan status proker, task, dan RAB
            </title>
            <rect width="680" height="460" rx="24" fill="#ffffff" />
            <rect x="0" y="0" width="150" height="460" rx="24" fill="#f5f7fb" />
            <rect x="24" y="30" width="86" height="12" rx="6" fill="#24695c" />
            {[84, 122, 160, 198, 236].map((y, index) => (
                <rect
                    key={y}
                    x="24"
                    y={y}
                    width={index === 1 ? 96 : 76}
                    height="12"
                    rx="6"
                    fill={index === 1 ? '#24695c' : '#d9e4e7'}
                />
            ))}
            <rect x="180" y="34" width="230" height="20" rx="10" fill="#242934" />
            <circle cx="610" cy="44" r="18" fill="#e8f5f2" />
            <circle cx="610" cy="44" r="8" fill="#24695c" />
            {[
                ['Proker Aktif', '12', '#24695c'],
                ['Task Selesai', '86%', '#ba895d'],
                ['RAB Terpakai', '64%', '#1b4c43'],
            ].map((card, index) => (
                <g key={card[0]} transform={`translate(${180 + index * 155} 88)`}>
                    <rect width="135" height="92" rx="16" fill="#f8fafc" />
                    <rect x="16" y="18" width="70" height="10" rx="5" fill="#8ea0ad" />
                    <text x="16" y="62" fontSize="28" fontWeight="700" fill={card[2]}>
                        {card[1]}
                    </text>
                    <text x="16" y="78" fontSize="10" fill="#59667a">
                        {card[0]}
                    </text>
                </g>
            ))}
            <rect x="180" y="210" width="285" height="188" rx="18" fill="#f8fafc" />
            <rect x="204" y="236" width="150" height="15" rx="8" fill="#242934" />
            <rect x="204" y="274" width="220" height="12" rx="6" fill="#d9e4e7" />
            <rect x="204" y="274" width="158" height="12" rx="6" fill="#24695c" />
            {[310, 342, 374].map((y, index) => (
                <g key={y}>
                    <circle cx="214" cy={y} r="8" fill={index === 2 ? '#ba895d' : '#24695c'} />
                    <rect x="234" y={y - 6} width={index === 1 ? 160 : 190} height="12" rx="6" fill="#d9e4e7" />
                </g>
            ))}
            <rect x="492" y="210" width="132" height="188" rx="18" fill="#e8f5f2" />
            <rect x="516" y="236" width="84" height="84" rx="14" fill="#ffffff" />
            <path d="M538 258h40v40h-40zM548 268h20v20h-20z" fill="#24695c" />
            <rect x="516" y="342" width="84" height="12" rx="6" fill="#24695c" />
            <rect x="532" y="366" width="52" height="10" rx="5" fill="#8ea0ad" />
        </svg>
    );
}
