import VihoCard from '@/Components/Viho/VihoCard';
import { formatRupiah } from '@/lib/format';
import type { FinanceProjectSummary } from '@/types/dashboard';

export default function FinanceSummaryWidget({
    title,
    items,
}: {
    title: string;
    items: FinanceProjectSummary[];
}) {
    return (
        <VihoCard title={title}>
            <div className="-m-5 divide-y divide-[#e6edef]">
                {items.length > 0 ? (
                    items.map((item) => (
                        <div key={item.prokerName} className="p-5">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <p className="font-semibold text-[#242934]">
                                        {item.prokerName}
                                    </p>
                                    <p className="mt-1 text-sm text-[#717171]">
                                        {formatRupiah(item.realisasiTotal)} /{' '}
                                        {formatRupiah(item.rabTotal)}
                                    </p>
                                </div>
                                <p className="text-sm font-semibold text-[#24695c]">
                                    {item.usagePercentage}%
                                </p>
                            </div>
                            <div className="mt-3 h-2 rounded-full bg-[#e6edef]">
                                <div
                                    className="h-2 rounded-full bg-[#24695c]"
                                    style={{
                                        width: `${Math.min(item.usagePercentage, 100)}%`,
                                    }}
                                />
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="p-5 text-sm text-[#59667a]">
                        Belum ada data keuangan.
                    </div>
                )}
            </div>
        </VihoCard>
    );
}
