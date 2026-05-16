import PrimaryButton from '@/Components/PrimaryButton';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Email Verification" />

            <div className="mb-6">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#24695c]">
                    Verification
                </p>
                <h1 className="mt-2 text-2xl font-semibold text-[#242934]">
                    Verify Email
                </h1>
                <p className="mt-2 text-sm text-[#59667a]">
                    Before getting started, verify your email address from the
                    link we sent. You can request a new email if it did not
                    arrive.
                </p>
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 rounded-[4px] border border-[#24695c]/20 bg-[#24695c]/10 px-4 py-3 text-sm font-medium text-[#24695c]">
                    A new verification link has been sent to the email address
                    you provided during registration.
                </div>
            )}

            <form onSubmit={submit}>
                <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <PrimaryButton disabled={processing}>
                        Resend Verification Email
                    </PrimaryButton>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="rounded-[4px] text-sm font-semibold text-[#59667a] underline hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
                    >
                        Log Out
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
