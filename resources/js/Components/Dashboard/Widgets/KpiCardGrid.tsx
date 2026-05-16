import VihoCard from '@/Components/Viho/VihoCard';
import type { KpiMetric } from '@/types/dashboard';

export default function KpiCardGrid({ metrics }: { metrics: KpiMetric[] }) {
    return (
        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {metrics.map((metric) => (
                <VihoCard key={metric.label} className="min-h-[128px]">
                    <p className="text-sm font-medium text-[#59667a]">
                        {metric.label}
                    </p>
                    <p className="mt-3 text-2xl font-semibold tracking-tight text-[#242934]">
                        {metric.value}
                    </p>
                </VihoCard>
            ))}
        </section>
    );
}
