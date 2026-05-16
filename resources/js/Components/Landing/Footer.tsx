import { Link } from '@inertiajs/react';

const footerColumns = [
    {
        title: 'Produk',
        links: [
            { label: 'Fitur', href: route('landing.features') },
            { label: 'Harga', href: route('landing.pricing') },
            { label: 'Marketplace (coming soon)', href: '#' },
            { label: 'Changelog', href: '#' },
        ],
    },
    {
        title: 'Resources',
        links: [
            { label: 'Blog', href: '/blog' },
            { label: 'Prokerin Academy (coming soon)', href: '#' },
            { label: 'Dokumentasi API', href: '#' },
            { label: 'Status Layanan', href: '#' },
        ],
    },
    {
        title: 'Perusahaan',
        links: [
            { label: 'Tentang Kami', href: '#' },
            { label: 'Kontak', href: 'mailto:halo@prokerin.id' },
            { label: 'Kebijakan Privasi', href: '#' },
            { label: 'Syarat & Ketentuan', href: '#' },
        ],
    },
];

export default function Footer() {
    return (
        <footer className="bg-gray-900 text-gray-400">
            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="grid gap-10 lg:grid-cols-[1.2fr_2fr]">
                    <div>
                        <Link
                            href={route('landing.home')}
                            className="font-['Plus_Jakarta_Sans'] text-2xl font-bold text-white"
                        >
                            Proker
                            <span className="text-[#ba895d]">in</span>
                        </Link>
                        <p className="mt-4 max-w-sm text-sm leading-6">
                            Platform manajemen proker untuk BEM, HIMA, dan UKM
                            Indonesia.
                        </p>
                        <div className="mt-6 flex gap-3">
                            {[
                                ['Instagram Prokerin', 'IG'],
                                ['LinkedIn Prokerin', 'in'],
                                ['Twitter Prokerin', 'X'],
                            ].map(([label, mark]) => (
                                <a
                                    key={label}
                                    href="https://prokerin.id"
                                    aria-label={label}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-sm font-bold text-gray-300 transition hover:bg-white/10 hover:text-white"
                                >
                                    {mark}
                                </a>
                            ))}
                        </div>
                    </div>
                    <div className="grid gap-8 sm:grid-cols-3">
                        {footerColumns.map((column) => (
                            <div key={column.title}>
                                <h2 className="text-sm font-semibold text-white">
                                    {column.title}
                                </h2>
                                <ul className="mt-4 space-y-3">
                                    {column.links.map((link) => (
                                        <li key={link.label}>
                                            <a
                                                href={link.href}
                                                className="text-sm transition hover:text-white"
                                            >
                                                {link.label}
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="mt-12 border-t border-white/10 pt-6 text-sm">
                    © 2026 Prokerin. All rights reserved. | Made with ❤️ for
                    ormawa Indonesia
                </div>
            </div>
        </footer>
    );
}
