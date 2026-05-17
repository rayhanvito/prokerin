const organizations = [
    'BEM UI',
    'HIMA FTUI',
    'UKM Fotografi ITB',
    'OSIS SMKN 1 JKT',
];

export default function SocialProofBar() {
    return (
        <section className="border-y border-[#e6edef] bg-[#f5f7fb] px-4 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl text-center">
                <p className="text-sm font-medium text-[#59667a]">
                    Dipercaya organisasi dari
                </p>
                <div className="mt-5 flex flex-wrap justify-center gap-3">
                    {organizations.map((organization) => (
                        <span
                            key={organization}
                            className="rounded-full bg-white px-4 py-2 text-sm font-semibold text-[#242934] shadow-sm ring-1 ring-[#e6edef]"
                        >
                            {organization}
                        </span>
                    ))}
                </div>
                {/* Whitelisted: string "Coming Soon" di bawah ini adalah pengecualian sah dari grep guardrail R1.5 (lihat requirements.md R1.1 SocialProofBar + R1.5 exception clause). */}
                <p className="mt-4 text-xs text-[#717171]">
                    Coming Soon — jadilah yang pertama.
                </p>
            </div>
        </section>
    );
}
