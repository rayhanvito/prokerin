import { Link } from '@inertiajs/react';
import { Building2, Target, Zap } from 'lucide-react';

const steps = [
    {
        icon: Building2,
        title: 'Daftarkan Organisasi',
        body: 'Buat akun gratis, isi profil organisasi, setup periode kepengurusan, dan undang anggota inti. Selesai dalam hitungan menit.',
    },
    {
        icon: Zap,
        title: 'Buat Proker dari Template',
        body: 'Pilih template yang sesuai — sudah ada susunan task, RAB, dan draft proposal. Sesuaikan, lalu launch.',
    },
    {
        icon: Target,
        title: 'Kelola sampai LPJ',
        body: 'Pantau semua progress dari dashboard, kelola keuangan, absensi rapat, dan generate laporan dari satu tempat.',
    },
];

export default function HowItWorksSection() {
    return (
        <section className="bg-[#1b4c43] px-4 py-24 text-white sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl text-center">
                <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold md:text-4xl">
                    Mulai dalam 5 Menit
                </h2>
                <p className="mt-4 text-lg text-white/80">
                    Tidak ada training, tidak ada setup rumit. Langsung
                    produktif.
                </p>
                <div className="relative mt-14 grid gap-6 md:grid-cols-3">
                    <div className="absolute left-1/2 top-16 hidden h-px w-2/3 -translate-x-1/2 border-t border-dashed border-white/20 md:block" />
                    {steps.map((step, index) => {
                        const Icon = step.icon;

                        return (
                            <article
                                key={step.title}
                                className="relative rounded-2xl bg-white/10 p-6 text-left backdrop-blur"
                            >
                                <div className="flex items-center gap-4">
                                    <span className="font-['Plus_Jakarta_Sans'] text-5xl font-extrabold text-white/20">
                                        0{index + 1}
                                    </span>
                                    <span className="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#24695c]">
                                        <Icon className="h-6 w-6" />
                                    </span>
                                </div>
                                <h3 className="mt-6 font-['Plus_Jakarta_Sans'] text-xl font-bold">
                                    {step.title}
                                </h3>
                                <p className="mt-3 text-sm leading-6 text-white/75">
                                    {step.body}
                                </p>
                            </article>
                        );
                    })}
                </div>
                <Link
                    href={route('register')}
                    className="mt-10 inline-flex rounded-xl bg-white px-8 py-4 text-base font-semibold text-[#24695c]"
                >
                    Mulai Gratis Sekarang →
                </Link>
            </div>
        </section>
    );
}
