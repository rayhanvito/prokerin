import { ChevronDown } from 'lucide-react';
import { useState } from 'react';

import { cn } from '@/lib/utils';

const faqs = [
    ['Apakah Prokerin benar-benar gratis?', 'Ya, plan Free kami benar-benar gratis tanpa batas waktu — untuk 1 organisasi dengan hingga 20 anggota dan 3 proker aktif. Tidak perlu kartu kredit untuk mulai.'],
    ['Apakah data organisasi kami aman?', 'Sangat aman. Data setiap organisasi sepenuhnya terisolasi — tidak ada organisasi lain yang bisa melihat data kamu. Semua file disimpan di cloud storage terenkripsi.'],
    ['Bisa diakses dari HP?', 'Bisa. Prokerin adalah Progressive Web App yang bisa diakses dari browser HP langsung tanpa perlu install dari App Store atau Play Store.'],
    ['Apakah ada masa percobaan untuk Starter dan Pro?', 'Ya, semua plan berbayar memiliki masa percobaan 14 hari gratis. Kamu bisa eksplorasi semua fitur tanpa ditagih.'],
    ['Bagaimana dengan organisasi yang sudah punya Google Drive dan Notion?', 'Prokerin tidak menggantikan Google Drive untuk storage umum. Yang Prokerin gantikan adalah spreadsheet keuangan, template proposal, tracking tugas, dan LPJ dari nol.'],
    ['Apakah bisa digunakan lebih dari satu organisasi?', 'Saat ini satu akun bisa join di banyak organisasi, tapi setiap organisasi memiliki subscription sendiri dan workspace terpisah.'],
    ['Bagaimana proses serah terima ke pengurus baru?', 'Owner lama cukup mengubah role anggota. Semua data, proker, dokumen, dan history tetap tersimpan di Prokerin.'],
    ['Apakah ada versi desktop atau native app?', 'Prokerin berjalan sepenuhnya di browser agar semua platform bisa akses dengan pengalaman yang sama.'],
    ['Bagaimana cara mengajukan demo untuk BEM kampus kami?', 'Kirim email ke halo@prokerin.id atau klik tombol Lihat Demo. Kami bisa mengatur sesi demo online 30 menit.'],
    ['Apakah ada diskon untuk kampus atau banyak organisasi?', 'Ya. Untuk universitas yang ingin mengelola banyak BEM/HIMA/UKM sekaligus, kami memiliki plan Campus dengan harga custom.'],
];

export default function FaqSection() {
    const [openIndex, setOpenIndex] = useState(0);

    return (
        <section className="bg-white px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-3xl">
                <div className="text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        Pertanyaan yang Sering Ditanyakan
                    </h2>
                </div>
                <div className="mt-10 divide-y divide-[#e6edef] rounded-2xl border border-[#e6edef] bg-white">
                    {faqs.map(([question, answer], index) => {
                        const isOpen = openIndex === index;
                        const panelId = `faq-panel-${index}`;

                        return (
                            <div key={question}>
                                <button
                                    type="button"
                                    aria-expanded={isOpen}
                                    aria-controls={panelId}
                                    onClick={() => setOpenIndex(isOpen ? -1 : index)}
                                    className="flex w-full items-center justify-between gap-4 p-5 text-left"
                                >
                                    <h3 className="font-['Plus_Jakarta_Sans'] text-base font-semibold text-[#242934]">
                                        {question}
                                    </h3>
                                    <ChevronDown
                                        className={cn(
                                            'h-5 w-5 shrink-0 text-[#59667a] transition-transform',
                                            isOpen ? 'rotate-180' : '',
                                        )}
                                    />
                                </button>
                                <div
                                    id={panelId}
                                    className={cn(
                                        'grid transition-all duration-300',
                                        isOpen
                                            ? 'grid-rows-[1fr]'
                                            : 'grid-rows-[0fr]',
                                    )}
                                >
                                    <div className="overflow-hidden">
                                        <p className="px-5 pb-5 text-sm leading-7 text-[#59667a]">
                                            {answer}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
