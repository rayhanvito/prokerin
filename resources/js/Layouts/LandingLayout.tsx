import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

import Footer from '@/Components/Landing/Footer';
import Navbar from '@/Components/Landing/Navbar';

interface LandingLayoutProps {
    children: ReactNode;
    title?: string;
    description?: string;
    keywords?: string;
    ogImage?: string;
    canonicalPath?: string;
    structuredData?: object;
}

export default function LandingLayout({
    children,
    title = 'Prokerin — Kelola Proker Organisasi Tanpa Chaos',
    description = 'Platform manajemen program kerja untuk BEM, HIMA, dan UKM Indonesia. Dari perencanaan, proposal, RAB, sampai LPJ — semua dalam satu tempat.',
    keywords,
    ogImage = '/images/og-prokerin.png',
    canonicalPath = '/',
    structuredData,
}: LandingLayoutProps) {
    const canonicalUrl = `https://prokerin.id${canonicalPath}`;

    return (
        <>
            <Head>
                <title>{title}</title>
                <meta name="description" content={description} />
                {keywords && <meta name="keywords" content={keywords} />}
                <meta property="og:title" content={title} />
                <meta property="og:description" content={description} />
                <meta property="og:image" content={ogImage} />
                <meta property="og:type" content="website" />
                <meta property="og:locale" content="id_ID" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={description} />
                <meta name="twitter:image" content={ogImage} />
                <link rel="canonical" href={canonicalUrl} />
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="preconnect"
                    href="https://fonts.gstatic.com"
                    crossOrigin="anonymous"
                />
                <link
                    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
                    rel="stylesheet"
                />
                {structuredData && (
                    <script
                        type="application/ld+json"
                        dangerouslySetInnerHTML={{
                            __html: JSON.stringify(structuredData),
                        }}
                    />
                )}
            </Head>
            <div className="min-h-screen bg-white font-['Inter'] text-[#242934] antialiased">
                <a
                    href="#main-content"
                    className="sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:not-sr-only focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-[#24695c] focus:shadow-lg"
                >
                    Skip to content
                </a>
                <Navbar />
                <main id="main-content">{children}</main>
                <Footer />
            </div>
        </>
    );
}
