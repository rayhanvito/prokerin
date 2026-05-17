import { PropsWithChildren, ReactNode, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

import FlashBanner from '@/Components/Prokerin/FlashBanner';
import ImpersonationBanner from '@/Components/Prokerin/ImpersonationBanner';
import OnboardingWizard from '@/Components/Prokerin/OnboardingWizard';
import VihoHeader from '@/Components/Viho/VihoHeader';
import VihoSidebar from '@/Components/Viho/VihoSidebar';
import Toaster from '@/Components/ui/Toaster';
import { showFlashToast } from '@/lib/toast';
import { PageProps } from '@/types';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);
    const { flash } = usePage<PageProps>().props;

    useEffect(() => {
        showFlashToast(flash);
    }, [flash]);

    return (
        <div className="min-h-screen bg-[#f5f7fb] font-sans text-[#242934]">
            <ImpersonationBanner />
            <VihoSidebar />

            <div className="lg:pl-[255px]">
                <VihoHeader
                    header={header}
                    mobileOpen={showingNavigationDropdown}
                    onToggleMobile={() =>
                        setShowingNavigationDropdown(
                            (previousState) => !previousState,
                        )
                    }
                />

                <main className="px-4 py-6 sm:px-6 lg:px-8">
                    <FlashBanner />
                    {children}
                </main>
            </div>

            <OnboardingWizard />
            <Toaster />
        </div>
    );
}
