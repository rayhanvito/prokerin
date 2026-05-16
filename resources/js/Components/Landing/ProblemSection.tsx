import { motion } from 'framer-motion';

const problems = [
    ['📅', 'Proker Molor Melulu', 'Deadline lewat, progress tidak jelas, tidak ada yang bisa ditagih'],
    ['📄', 'Proposal Bolak-Balik', 'Revisi ke-5 masih ada yang kurang, format tidak pernah seragam'],
    ['✅', 'Task Jatuh ke Mana?', 'Sudah didelegasikan tapi tidak ada yang follow up — hilang begitu saja'],
    ['💰', 'RAB vs Realisasi Beda Jauh', 'Laporan keuangan dikerjakan dadakan, angka tidak sinkron'],
    ['📁', 'Dokumen Nyebar di Mana-Mana', 'Ada di Drive A, Drive B, WhatsApp, dan laptop yang sudah ganti pemilik'],
    ['📋', 'LPJ Dikerjain H-1', 'Tidak ada catatan selama proker berjalan, semua ditulis ulang dari ingatan'],
    ['🔄', 'Pergantian Pengurus = Reset Total', 'Ilmu, dokumen, dan konteks hilang saat pengurus lama selesai menjabat'],
];

export default function ProblemSection() {
    return (
        <section className="bg-white px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        7 Masalah yang Bikin Pengurus Ormawa Pusing
                    </h2>
                    <p className="mt-4 text-lg text-[#59667a]">
                        Kamu pasti pernah ngalamin salah satunya. Atau semuanya.
                    </p>
                </div>
                <div className="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {problems.map(([emoji, title, description], index) => (
                        <motion.article
                            key={title}
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: index * 0.06, duration: 0.45 }}
                            className="rounded-2xl border border-gray-100 bg-white p-6 transition-all hover:border-[#24695c]/30 hover:shadow-md"
                        >
                            <div className="text-3xl">{emoji}</div>
                            <h3 className="mt-5 font-['Plus_Jakarta_Sans'] text-lg font-semibold text-[#242934]">
                                {title}
                            </h3>
                            <p className="mt-3 text-sm leading-6 text-[#59667a]">
                                {description}
                            </p>
                        </motion.article>
                    ))}
                </div>
                <p className="mx-auto mt-12 max-w-3xl border-t border-[#e6edef] pt-8 text-center text-lg font-semibold text-[#24695c]">
                    Prokerin dirancang khusus untuk menyelesaikan semua ini —
                    bukan workaround, tapi solusi permanen.
                </p>
            </div>
        </section>
    );
}
