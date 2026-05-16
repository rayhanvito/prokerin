import CtaBanner from '@/Components/Landing/CtaBanner';
import FaqSection from '@/Components/Landing/FaqSection';
import PricingSection from '@/Components/Landing/PricingSection';
import LandingLayout from '@/Layouts/LandingLayout';

const comparisonRows = [
    ['Anggota', '20', '50', 'Unlimited', 'Unlimited'],
    ['Proker aktif', '3', '10', 'Unlimited', 'Unlimited'],
    ['Storage', '500 MB', '5 GB', '20 GB', 'Custom'],
    ['Rapat M14', 'Tidak', 'Ya', 'Ya', 'Ya'],
    ['Absensi QR M15', 'Tidak', 'Ya', 'Ya', 'Ya'],
    ['Sertifikat M16', 'Tidak', 'Tidak', 'Ya', 'Ya'],
    ['WhatsApp M17', 'Tidak', 'Tidak', 'Ya', 'Ya'],
    ['AI Assistant M23', 'Tidak', 'Tidak', 'Ya', 'Ya'],
    ['Campus Dashboard', 'Tidak', 'Tidak', 'Tidak', 'Ya'],
    ['Support', 'Komunitas', 'Email', 'Email + Chat', 'Dedicated'],
];

export default function Pricing() {
    return (
        <LandingLayout
            title="Harga Prokerin — Mulai Gratis, Upgrade Kapan Saja | Prokerin"
            description="Lihat harga Prokerin: Free Rp0, Starter Rp99.000/bulan, Pro Rp299.000/bulan, Campus custom. Coba 14 hari gratis untuk semua plan berbayar."
            canonicalPath="/pricing"
        >
            <section className="bg-[linear-gradient(135deg,#e8f5f2,#ffffff)] px-4 pb-12 pt-36 text-center sm:px-6 lg:px-8">
                <div className="mx-auto max-w-4xl">
                    <h1 className="font-['Plus_Jakarta_Sans'] text-4xl font-extrabold text-[#242934] md:text-6xl">
                        Harga yang Adil untuk Semua Skala Organisasi
                    </h1>
                    <p className="mt-6 text-lg leading-8 text-[#59667a]">
                        Mulai gratis selamanya. Upgrade hanya jika kamu butuh
                        lebih.
                    </p>
                </div>
            </section>
            <PricingSection />
            <section className="bg-white px-4 py-20 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-7xl">
                    <h2 className="font-['Plus_Jakarta_Sans'] text-3xl font-bold text-[#242934]">
                        Perbandingan Fitur
                    </h2>
                    <div className="mt-8 overflow-x-auto rounded-2xl border border-[#e6edef]">
                        <table className="min-w-[760px] w-full divide-y divide-[#e6edef] text-sm">
                            <thead className="bg-[#f5f7fb]">
                                <tr>
                                    {['Fitur', 'Free', 'Starter', 'Pro', 'Campus'].map((heading) => (
                                        <th
                                            key={heading}
                                            className="px-5 py-4 text-left font-semibold text-[#242934]"
                                        >
                                            {heading}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#e6edef] bg-white">
                                {comparisonRows.map((row) => (
                                    <tr key={row[0]}>
                                        {row.map((cell) => (
                                            <td key={cell} className="px-5 py-4 text-[#59667a]">
                                                {cell}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <FaqSection />
            <CtaBanner />
        </LandingLayout>
    );
}
