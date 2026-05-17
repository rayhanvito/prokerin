import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { MailCheck } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import GuestLayout from '@/Layouts/GuestLayout';
import { PageProps } from '@/types';

interface InvitationPayload {
    email: string;
    role: string;
    status: string;
    expiresAt: string | null;
    organizationName: string;
    isOpen: boolean;
    acceptUrl: string;
    declineUrl: string;
}

interface InvitationShowProps {
    invitation: InvitationPayload;
}

export default function InvitationShow({ invitation }: InvitationShowProps) {
    const { auth } = usePage<PageProps>().props;
    const acceptForm = useForm({});
    const declineForm = useForm({});

    return (
        <GuestLayout>
            <Head title="Organization Invitation" />

            <VihoCard title="Organization Invitation">
                <div className="text-center">
                    <span className="mx-auto flex h-14 w-14 items-center justify-center rounded-[4px] bg-[#24695c]/10 text-[#24695c]">
                        <MailCheck className="h-7 w-7" />
                    </span>
                    <h1 className="mt-5 text-xl font-semibold text-[#242934]">
                        {invitation.organizationName}
                    </h1>
                    <p className="mt-2 text-sm leading-6 text-[#59667a]">
                        {invitation.email} diundang sebagai{' '}
                        <span className="font-semibold">{invitation.role}</span>.
                    </p>
                    <p className="mt-2 text-xs font-medium text-[#717171]">
                        Status: {invitation.status}
                        {invitation.expiresAt
                            ? ` · Expires ${invitation.expiresAt}`
                            : ''}
                    </p>

                    {auth.user ? (
                        <div className="mt-6 flex justify-center gap-3">
                            <button
                                type="button"
                                disabled={
                                    !invitation.isOpen || acceptForm.processing
                                }
                                onClick={() =>
                                    acceptForm.post(invitation.acceptUrl)
                                }
                                className="rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                            >
                                Accept
                            </button>
                            <button
                                type="button"
                                disabled={
                                    !invitation.isOpen || declineForm.processing
                                }
                                onClick={() =>
                                    declineForm.post(invitation.declineUrl)
                                }
                                className="rounded-[4px] border border-[#e6edef] bg-white px-4 py-2 text-sm font-semibold text-[#59667a]"
                            >
                                Decline
                            </button>
                        </div>
                    ) : (
                        <Link
                            href={route('login')}
                            className="mt-6 inline-flex rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            Login untuk merespons
                        </Link>
                    )}
                </div>
            </VihoCard>
        </GuestLayout>
    );
}
