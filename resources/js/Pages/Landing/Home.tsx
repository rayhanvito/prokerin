import CtaBanner from '@/Components/Landing/CtaBanner';
import FaqSection from '@/Components/Landing/FaqSection';
import FeatureShowcase from '@/Components/Landing/FeatureShowcase';
import HeroSection from '@/Components/Landing/HeroSection';
import HowItWorksSection from '@/Components/Landing/HowItWorksSection';
import PricingSection from '@/Components/Landing/PricingSection';
import ProblemSection from '@/Components/Landing/ProblemSection';
import SocialProofBar from '@/Components/Landing/SocialProofBar';
import TestimonialsSection from '@/Components/Landing/TestimonialsSection';
import LandingLayout from '@/Layouts/LandingLayout';

export default function Home() {
    return (
        <LandingLayout
            title="Prokerin — Kelola Proker Organisasi Tanpa Chaos | Platform Ormawa Indonesia"
            description="Platform manajemen program kerja untuk BEM, HIMA, dan UKM Indonesia. Proposal otomatis, RAB terintegrasi, absensi QR, LPJ dari data nyata. Coba gratis."
            keywords="aplikasi manajemen organisasi mahasiswa, software BEM, aplikasi BEM, manajemen proker mahasiswa, aplikasi absensi organisasi, template proposal mahasiswa"
            canonicalPath="/"
            structuredData={{
                '@context': 'https://schema.org',
                '@type': 'SoftwareApplication',
                name: 'Prokerin',
                description:
                    'Platform manajemen program kerja untuk organisasi mahasiswa Indonesia',
                applicationCategory: 'BusinessApplication',
                operatingSystem: 'Web',
                offers: {
                    '@type': 'Offer',
                    price: '0',
                    priceCurrency: 'IDR',
                },
            }}
        >
            <HeroSection />
            <SocialProofBar />
            <ProblemSection />
            <FeatureShowcase />
            <HowItWorksSection />
            <TestimonialsSection />
            <PricingSection />
            <FaqSection />
            <CtaBanner />
        </LandingLayout>
    );
}
