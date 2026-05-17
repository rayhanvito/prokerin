import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import CalendarSyncSection from './Partials/CalendarSyncSection';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
    calendarSync,
}: PageProps<{
    mustVerifyEmail: boolean;
    status?: string;
    calendarSync: {
        enabled: boolean;
        feedUrl: string | null;
    };
}>) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#24695c]">
                        Account
                    </p>
                    <h2 className="mt-1 text-2xl font-semibold text-[#242934]">
                        Profile
                    </h2>
                </div>
            }
        >
            <Head title="Profile" />

            <div className="space-y-6">
                <div className="rounded-[6px] border border-[#e6edef] bg-white p-5 shadow-[0_4px_22px_rgba(36,41,52,0.06)] sm:p-6">
                    <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                        <div>
                            <p className="text-sm font-semibold text-[#242934]">
                                Pengaturan Akun
                            </p>
                            <p className="mt-1 max-w-3xl text-sm text-[#59667a]">
                                Kelola identitas pengguna, password akses, dan
                                tindakan akun untuk workspace Prokerin.
                            </p>
                        </div>
                        <span className="inline-flex w-fit rounded-[4px] bg-[#24695c]/10 px-3 py-1 text-xs font-semibold text-[#24695c]">
                            Account Flow
                        </span>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div className="space-y-6">
                        <div className="rounded-[6px] border border-[#e6edef] bg-white p-5 shadow-[0_4px_22px_rgba(36,41,52,0.06)] sm:p-6">
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                className="max-w-2xl"
                            />
                        </div>

                        <div className="rounded-[6px] border border-[#e6edef] bg-white p-5 shadow-[0_4px_22px_rgba(36,41,52,0.06)] sm:p-6">
                            <UpdatePasswordForm className="max-w-2xl" />
                        </div>

                        <div className="rounded-[6px] border border-[#e6edef] bg-white p-5 shadow-[0_4px_22px_rgba(36,41,52,0.06)] sm:p-6">
                            <CalendarSyncSection
                                enabled={calendarSync.enabled}
                                feedUrl={calendarSync.feedUrl}
                            />
                        </div>
                    </div>

                    <div className="rounded-[6px] border border-[#f3d2d2] bg-white p-5 shadow-[0_4px_22px_rgba(36,41,52,0.06)] sm:p-6">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
