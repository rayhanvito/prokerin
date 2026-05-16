import { Head } from '@inertiajs/react';

import BendaharaDashboard from '@/Components/Dashboard/Variants/BendaharaDashboard';
import MemberDashboard from '@/Components/Dashboard/Variants/MemberDashboard';
import OperasionalDashboard from '@/Components/Dashboard/Variants/OperasionalDashboard';
import PimpinanDashboard from '@/Components/Dashboard/Variants/PimpinanDashboard';
import SekretarisDashboard from '@/Components/Dashboard/Variants/SekretarisDashboard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type {
    BendaharaPayload,
    DashboardPageProps,
    MemberPayload,
    OperasionalPayload,
    PimpinanPayload,
    SekretarisPayload,
} from '@/types/dashboard';

export default function DashboardIndex({
    dashboardVariant,
    payload,
}: DashboardPageProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        Dashboard Role-Aware
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        {titleForVariant(dashboardVariant)}
                    </h1>
                </div>
            }
        >
            <Head title={titleForVariant(dashboardVariant)} />
            {dashboardVariant === 'pimpinan' && (
                <PimpinanDashboard payload={payload as PimpinanPayload} />
            )}
            {dashboardVariant === 'sekretaris' && (
                <SekretarisDashboard payload={payload as SekretarisPayload} />
            )}
            {dashboardVariant === 'bendahara' && (
                <BendaharaDashboard payload={payload as BendaharaPayload} />
            )}
            {dashboardVariant === 'operasional' && (
                <OperasionalDashboard
                    payload={payload as OperasionalPayload}
                />
            )}
            {(dashboardVariant === 'member' || dashboardVariant === 'viewer') && (
                <MemberDashboard
                    payload={payload as MemberPayload}
                    isViewer={dashboardVariant === 'viewer'}
                />
            )}
        </AuthenticatedLayout>
    );
}

function titleForVariant(variant: DashboardPageProps['dashboardVariant']): string {
    return {
        pimpinan: 'Dashboard Pimpinan',
        sekretaris: 'Dashboard Sekretaris',
        bendahara: 'Dashboard Bendahara',
        operasional: 'Dashboard Operasional',
        member: 'Dashboard Anggota',
        viewer: 'Dashboard Viewer',
    }[variant];
}
