import { PropsWithChildren, ReactNode, useState } from 'react';

import FlashBanner from '@/Components/Prokerin/FlashBanner';
import VihoHeader from '@/Components/Viho/VihoHeader';
import VihoSidebar from '@/Components/Viho/VihoSidebar';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-[#f5f7fb] font-sans text-[#242934]">
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
        </div>
    );
}
