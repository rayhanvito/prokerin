import { motion } from 'framer-motion';
import { Calculator, FileX, LayoutDashboard } from 'lucide-react';

import type { ProblemItem } from '@/types';

const ICON_MAP = {
    FileX,
    LayoutDashboard,
    Calculator,
} as const;

const problems: ProblemItem[] = [
    {
        icon: 'FileX',
        title: 'Proposal Revisi Terus',
        description:
            'File proposal ada di email ketua, revisi di WhatsApp, versi final entah di mana. Meeting jam 9, proposal belum fix.',
    },
    {
        icon: 'LayoutDashboard',
        title: 'Task Bocor ke Mana-mana',
        description:
            'Siapa penanggung jawab sewa sound system? Sudah beli konsumsi belum? Tidak ada yang tahu sampai H-1.',
    },
    {
        icon: 'Calculator',
        title: 'RAB & LPJ Tak Pernah Sinkron',
        description:
            'Budget disetujui 5 juta, ternyata habis 6,2 juta. LPJ pun jadi drama. Tiap tahun terulang.',
    },
];

export default function ProblemSection() {
    return (
        <section className="bg-white px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        Kamu yang ini, ya?
                    </h2>
                    <p className="mt-4 text-lg text-[#59667a]">
                        Kalau kamu pernah frustrasi dengan salah satu dari ini,
                        Prokerin dibuat untuk kamu.
                    </p>
                </div>
                <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {problems.map((problem, index) => {
                        const Icon = ICON_MAP[problem.icon];
                        return (
                            <motion.article
                                key={problem.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{
                                    delay: index * 0.06,
                                    duration: 0.45,
                                }}
                                className="rounded-2xl border border-gray-100 bg-white p-6 transition-all hover:border-[#24695c]/30 hover:shadow-md"
                            >
                                <Icon
                                    className="h-6 w-6 text-[#24695c]"
                                    aria-hidden="true"
                                />
                                <h3 className="mt-5 font-['Plus_Jakarta_Sans'] text-lg font-semibold text-[#242934]">
                                    {problem.title}
                                </h3>
                                <p className="mt-3 text-sm leading-6 text-[#59667a]">
                                    {problem.description}
                                </p>
                            </motion.article>
                        );
                    })}
                </div>
                <p className="mx-auto mt-12 max-w-3xl border-t border-[#e6edef] pt-8 text-center text-lg font-semibold text-[#24695c]">
                    Prokerin dirancang khusus untuk menyelesaikan semua ini —
                    bukan workaround, tapi solusi permanen.
                </p>
            </div>
        </section>
    );
}
