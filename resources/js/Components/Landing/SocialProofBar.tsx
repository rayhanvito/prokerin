const organizations = [
    'BEM Universitas Airlangga',
    'HIMA Teknik ITS',
    'BEM UNESA',
    'BEM Brawijaya',
    'UKM Robotika ITS',
];

export default function SocialProofBar() {
    return (
        <section className="border-y border-[#e6edef] bg-[#f5f7fb] px-4 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-7xl text-center">
                <p className="text-sm font-medium text-[#59667a]">
                    Dipercaya oleh 500+ organisasi mahasiswa di Indonesia
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
            </div>
        </section>
    );
}
