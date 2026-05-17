import { Head, Link } from '@inertiajs/react';
import { CalendarDays, ExternalLink, Mail, MapPin, Phone, Users } from 'lucide-react';
import type { ReactNode } from 'react';

import CountdownTimer from '@/Components/Microsite/CountdownTimer';
import GalleryGrid, { type GalleryItem } from '@/Components/Microsite/GalleryGrid';
import type { PageProps } from '@/types';

interface MicrositeShowProps extends PageProps {
    project: {
        id: number;
        name: string;
        slug: string;
        description: string;
        organizationName: string;
        organizationSlug: string;
        startsAt: string | null;
        endsAt: string | null;
    };
    microsite: {
        bannerImageUrl: string | null;
        descriptionHtml: string;
        locationText: string | null;
        locationMapsUrl: string | null;
        contactName: string | null;
        contactWhatsapp: string | null;
        contactEmail: string | null;
        showCountdown: boolean;
        showCommittee: boolean;
        showGallery: boolean;
        metaTitle: string;
        metaDescription: string;
    };
    gallery: GalleryItem[];
    committee: Array<{
        name: string;
        role: string;
    }>;
    registration: {
        isAvailable: boolean;
        url: string | null;
        remainingQuota: number | null;
    };
    seo: {
        title: string;
        description: string;
        image: string | null;
        canonical: string;
    };
}

export default function MicrositeShow({
    project,
    microsite,
    gallery,
    committee,
    registration,
    seo,
}: MicrositeShowProps) {
    return (
        <main className="min-h-screen bg-[#f5f7fb] text-[#242934]">
            <Head title={seo.title}>
                <meta name="description" content={seo.description} />
                <meta property="og:title" content={seo.title} />
                <meta property="og:description" content={seo.description} />
                <meta property="og:type" content="website" />
                <meta property="og:url" content={seo.canonical} />
                {seo.image ? <meta property="og:image" content={seo.image} /> : null}
                <meta name="twitter:card" content="summary_large_image" />
                <link rel="canonical" href={seo.canonical} />
            </Head>

            <section className="relative isolate min-h-[620px] overflow-hidden bg-[#1b4c43] px-4 py-6 text-white sm:px-6 lg:px-8">
                {microsite.bannerImageUrl ? (
                    <img
                        src={microsite.bannerImageUrl}
                        alt=""
                        className="absolute inset-0 -z-10 h-full w-full object-cover opacity-45"
                    />
                ) : null}
                <div className="absolute inset-0 -z-10 bg-[#1b4c43]/80" />

                <nav className="mx-auto flex max-w-6xl items-center justify-between">
                    <Link href={route('landing.home')} className="font-semibold">
                        Prokerin
                    </Link>
                    <span className="text-sm font-medium text-white/80">
                        {project.organizationName}
                    </span>
                </nav>

                <div className="mx-auto grid max-w-6xl gap-8 pt-24 lg:grid-cols-[1fr_360px] lg:items-end">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#d8c3a5]">
                            Public Proker Microsite
                        </p>
                        <h1 className="mt-4 max-w-4xl text-4xl font-semibold leading-tight text-white sm:text-5xl">
                            {project.name}
                        </h1>
                        <p className="mt-5 max-w-2xl text-base leading-7 text-white/82">
                            {seo.description}
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            {registration.isAvailable && registration.url ? (
                                <Link
                                    href={registration.url}
                                    className="inline-flex items-center justify-center gap-2 rounded-[4px] bg-white px-5 py-3 text-sm font-semibold text-[#24695c] shadow-sm"
                                >
                                    Daftar Event
                                    <ExternalLink className="h-4 w-4" />
                                </Link>
                            ) : null}
                            {microsite.locationMapsUrl ? (
                                <a
                                    href={microsite.locationMapsUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex items-center justify-center gap-2 rounded-[4px] border border-white/35 px-5 py-3 text-sm font-semibold text-white"
                                >
                                    Lihat Lokasi
                                    <MapPin className="h-4 w-4" />
                                </a>
                            ) : null}
                        </div>
                    </div>

                    <div className="space-y-3">
                        {microsite.showCountdown ? (
                            <CountdownTimer targetDate={project.startsAt} />
                        ) : null}
                        <InfoPill
                            icon={<CalendarDays className="h-4 w-4" />}
                            label="Tanggal"
                            value={dateRange(project.startsAt, project.endsAt)}
                        />
                        {microsite.locationText ? (
                            <InfoPill
                                icon={<MapPin className="h-4 w-4" />}
                                label="Lokasi"
                                value={microsite.locationText}
                            />
                        ) : null}
                    </div>
                </div>
            </section>

            <section className="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_340px] lg:px-8">
                <article className="rounded-[4px] bg-white p-6 shadow-sm ring-1 ring-[#e6edef]">
                    <h2 className="text-xl font-semibold text-[#242934]">
                        Tentang Proker
                    </h2>
                    <div
                        className="prose prose-sm mt-4 max-w-none text-[#59667a]"
                        dangerouslySetInnerHTML={{
                            __html: microsite.descriptionHtml,
                        }}
                    />
                </article>

                <aside className="space-y-4">
                    <div className="rounded-[4px] bg-white p-5 shadow-sm ring-1 ring-[#e6edef]">
                        <h2 className="text-base font-semibold text-[#242934]">
                            Kontak Panitia
                        </h2>
                        <div className="mt-4 space-y-3 text-sm text-[#59667a]">
                            {microsite.contactName ? (
                                <ContactLine
                                    icon={<Users className="h-4 w-4" />}
                                    value={microsite.contactName}
                                />
                            ) : null}
                            {microsite.contactWhatsapp ? (
                                <ContactLine
                                    icon={<Phone className="h-4 w-4" />}
                                    value={microsite.contactWhatsapp}
                                />
                            ) : null}
                            {microsite.contactEmail ? (
                                <ContactLine
                                    icon={<Mail className="h-4 w-4" />}
                                    value={microsite.contactEmail}
                                />
                            ) : null}
                        </div>
                    </div>

                    {microsite.showCommittee && committee.length > 0 ? (
                        <div className="rounded-[4px] bg-white p-5 shadow-sm ring-1 ring-[#e6edef]">
                            <h2 className="text-base font-semibold text-[#242934]">
                                Panitia Inti
                            </h2>
                            <div className="mt-4 space-y-3">
                                {committee.map((member) => (
                                    <div
                                        key={`${member.name}-${member.role}`}
                                        className="flex items-center justify-between gap-3 text-sm"
                                    >
                                        <span className="font-medium text-[#242934]">
                                            {member.name}
                                        </span>
                                        <span className="text-[#717171]">
                                            {roleLabel(member.role)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : null}
                </aside>
            </section>

            {microsite.showGallery ? (
                <section className="mx-auto max-w-6xl px-4 pb-12 sm:px-6 lg:px-8">
                    <div className="mb-5">
                        <h2 className="text-xl font-semibold text-[#242934]">
                            Galeri
                        </h2>
                    </div>
                    <GalleryGrid items={gallery} />
                </section>
            ) : null}

            <footer className="border-t border-[#e6edef] bg-white px-4 py-6 text-center text-sm text-[#59667a]">
                Powered by{' '}
                <Link href={route('landing.home')} className="font-semibold text-[#24695c]">
                    Prokerin
                </Link>
            </footer>
        </main>
    );
}

function InfoPill({
    icon,
    label,
    value,
}: {
    icon: ReactNode;
    label: string;
    value: string;
}) {
    return (
        <div className="flex items-center gap-3 rounded-[4px] bg-white px-4 py-3 text-[#242934] shadow-sm ring-1 ring-[#e6edef]">
            <span className="flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#24695c] text-white">
                {icon}
            </span>
            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                    {label}
                </p>
                <p className="text-sm font-semibold text-[#242934]">{value}</p>
            </div>
        </div>
    );
}

function ContactLine({ icon, value }: { icon: ReactNode; value: string }) {
    return (
        <div className="flex items-center gap-3">
            <span className="text-[#24695c]">{icon}</span>
            <span>{value}</span>
        </div>
    );
}

function dateRange(startsAt: string | null, endsAt: string | null): string {
    if (startsAt === null) {
        return 'Tanggal menyusul';
    }

    if (endsAt === null || startsAt === endsAt) {
        return startsAt;
    }

    return `${startsAt} - ${endsAt}`;
}

function roleLabel(role: string): string {
    return role
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
