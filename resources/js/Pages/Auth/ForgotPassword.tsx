import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot Password" />

            <div className="mb-6">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#24695c]">
                    Account Recovery
                </p>
                <h1 className="mt-2 text-2xl font-semibold text-[#242934]">
                    Forgot Password
                </h1>
                <p className="mt-2 text-sm text-[#59667a]">
                    Enter your email address and we will send a reset link so
                    you can choose a new password.
                </p>
            </div>

            {status && (
                <div className="mb-4 rounded-[4px] border border-[#24695c]/20 bg-[#24695c]/10 px-4 py-3 text-sm font-medium text-[#24695c]">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        isFocused={true}
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                    />
                </div>

                <InputError message={errors.email} className="mt-2" />

                <div className="mt-6 flex items-center justify-end">
                    <PrimaryButton disabled={processing}>
                        Email Password Reset Link
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
