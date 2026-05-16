const testimonials = [
    {
        quote: 'Proposal yang biasanya makan 3 hari sekarang beres 2 jam. LPJ tidak perlu lembur lagi — data sudah terkumpul otomatis selama proker berjalan.',
        name: 'Raihan Fauzi',
        role: 'Ketua BEM, Universitas Airlangga Surabaya',
        initials: 'RF',
    },
    {
        quote: 'Anggaran kami akhirnya transparan. Semua bendahara bisa lihat real-time, approval lebih cepat, tidak ada lagi debat soal versi spreadsheet.',
        name: 'Aulia Putri Maharani',
        role: 'Bendahara Umum HIMA Teknik Informatika ITS',
        initials: 'AP',
    },
    {
        quote: 'Pergantian pengurus semester ini lancar banget. Semua dokumen, proker, dan konteks tersimpan rapi di Prokerin.',
        name: 'Dimas Prasetyo',
        role: 'Sekretaris Jenderal UKM Robotika, Universitas Brawijaya',
        initials: 'DP',
    },
];

export default function TestimonialsSection() {
    return (
        <section className="bg-white px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl">
                <div className="text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        Apa Kata Pengurus Ormawa
                    </h2>
                    <p className="mt-4 text-lg text-[#59667a]">
                        Dari yang dulunya chaos, sekarang semua terkendali.
                    </p>
                </div>
                <div className="mt-12 grid gap-6 lg:grid-cols-3">
                    {testimonials.map((testimonial) => (
                        <article
                            key={testimonial.name}
                            data-placeholder="true"
                            className="relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                        >
                            {/* Placeholder testimonial, replace with real customer quotes before go-live. */}
                            <div className="absolute right-6 top-4 text-6xl font-bold text-[#24695c]/10">
                                "
                            </div>
                            <p className="relative text-sm leading-7 text-[#59667a]">
                                "{testimonial.quote}"
                            </p>
                            <div className="mt-6 flex items-center gap-3">
                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#e8f5f2] text-sm font-bold text-[#24695c]">
                                    {testimonial.initials}
                                </span>
                                <div>
                                    <h3 className="font-semibold text-[#242934]">
                                        {testimonial.name}
                                    </h3>
                                    <p className="text-sm text-[#59667a]">
                                        {testimonial.role}
                                    </p>
                                    <p className="mt-1 text-sm text-[#f59e0b]">
                                        ★★★★★
                                    </p>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
