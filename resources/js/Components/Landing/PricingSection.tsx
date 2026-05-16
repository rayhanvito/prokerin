import { Check, X } from 'lucide-react';
import { useState } from 'react';

import { cn } from '@/lib/utils';

const tiers = [
    {
        name: 'Free',
        monthly: 'Rp 0',
        yearly: 'Rp 0',
        description: 'Untuk mulai rapi tanpa biaya.',
        cta: 'Mulai Gratis',
        href: route('register'),
        highlighted: false,
        features: ['1 organisasi', '20 anggota', '3 proker aktif', '500 MB storage', 'MVP M01-M13'],
        missing: ['Rapat M14', 'Absensi QR M15', 'Sertifikat M16'],
    },
    {
        name: 'Starter',
        monthly: 'Rp 99.000/bln',
        yearly: 'Rp 79.000/bln',
        description: 'Untuk HIMA kecil yang mulai bertumbuh.',
        cta: 'Coba 14 Hari',
        href: route('register', { plan: 'starter' }),
        highlighted: false,
        features: ['1 organisasi', '50 anggota', '10 proker aktif', '5 GB storage', 'Rapat & Absensi QR'],
        missing: ['Sertifikat M16', 'WhatsApp M17', 'AI Assistant'],
    },
    {
        name: 'Pro',
        monthly: 'Rp 299.000/bln',
        yearly: 'Rp 239.000/bln',
        description: 'Untuk BEM dan UKM yang butuh alur lengkap.',
        cta: 'Coba 14 Hari',
        href: route('register', { plan: 'pro' }),
        highlighted: true,
        features: ['Anggota unlimited', 'Proker unlimited', '20 GB storage', 'Sertifikat digital', 'WhatsApp & AI Assistant'],
        missing: ['Campus Dashboard'],
    },
    {
        name: 'Campus',
        monthly: 'Hubungi kami',
        yearly: 'Custom',
        description: 'Untuk kampus dengan banyak organisasi.',
        cta: 'Hubungi Kami',
        href: 'mailto:halo@prokerin.id',
        highlighted: false,
        features: ['Organisasi unlimited', 'Storage custom', 'Campus Dashboard', 'Dedicated support', 'SLA kampus'],
        missing: [],
    },
];

export default function PricingSection() {
    const [billingCycle, setBillingCycle] = useState<'monthly' | 'yearly'>('monthly');

    return (
        <section className="bg-[#f5f7fb] px-4 py-24 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934] md:text-4xl">
                        Harga Transparan, Tidak Ada Biaya Tersembunyi
                    </h2>
                    <p className="mt-4 text-lg text-[#59667a]">
                        Mulai gratis, upgrade kapan saja. Tidak perlu kartu
                        kredit untuk plan Free.
                    </p>
                    <div className="mt-8 inline-flex rounded-2xl bg-white p-1 shadow-sm ring-1 ring-[#e6edef]">
                        {[
                            ['monthly', 'Bulanan'],
                            ['yearly', 'Tahunan hemat 20%'],
                        ].map(([value, label]) => (
                            <button
                                key={value}
                                type="button"
                                onClick={() => setBillingCycle(value as 'monthly' | 'yearly')}
                                className={cn(
                                    'rounded-xl px-4 py-2 text-sm font-semibold transition',
                                    billingCycle === value
                                        ? 'bg-[#24695c] text-white'
                                        : 'text-[#59667a]',
                                )}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>
                <div className="mt-12 grid gap-6 lg:grid-cols-4">
                    {tiers.map((tier) => (
                        <article
                            key={tier.name}
                            className={cn(
                                'relative rounded-2xl border bg-white p-6 shadow-sm transition hover:scale-[1.02]',
                                tier.highlighted
                                    ? 'border-2 border-[#24695c] bg-[#e8f5f2]'
                                    : 'border-[#e6edef]',
                            )}
                        >
                            {tier.highlighted && (
                                <span className="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-[#24695c] px-4 py-1 text-xs font-bold text-white">
                                    Paling Populer
                                </span>
                            )}
                            <h3 className="font-['Plus_Jakarta_Sans'] text-xl font-bold text-[#242934]">
                                {tier.name}
                            </h3>
                            <p className="mt-2 text-sm leading-6 text-[#59667a]">
                                {tier.description}
                            </p>
                            <p className="mt-6 font-['Plus_Jakarta_Sans'] text-3xl font-extrabold text-[#242934]">
                                {billingCycle === 'monthly' ? tier.monthly : tier.yearly}
                            </p>
                            <a
                                href={tier.href}
                                className={cn(
                                    'mt-6 inline-flex w-full justify-center rounded-xl px-4 py-3 text-sm font-semibold',
                                    tier.highlighted
                                        ? 'bg-[#24695c] text-white'
                                        : 'bg-[#f5f7fb] text-[#24695c]',
                                )}
                            >
                                {tier.cta}
                            </a>
                            <ul className="mt-6 space-y-3 text-sm">
                                {tier.features.map((feature) => (
                                    <li key={feature} className="flex gap-2 text-[#242934]">
                                        <Check className="h-4 w-4 shrink-0 text-[#22c55e]" />
                                        {feature}
                                    </li>
                                ))}
                                {tier.missing.map((feature) => (
                                    <li key={feature} className="flex gap-2 text-[#8ea0ad]">
                                        <X className="h-4 w-4 shrink-0" />
                                        {feature}
                                    </li>
                                ))}
                            </ul>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
