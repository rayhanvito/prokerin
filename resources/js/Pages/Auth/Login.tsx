import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div className="mb-6">
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                    Welcome back
                </p>
                <h1 className="mt-2 text-2xl font-semibold text-[#242934]">
                    Login ke Prokerin
                </h1>
                <p className="mt-2 text-sm leading-6 text-[#59667a]">
                    Masuk untuk mengelola proker, proposal, RAB, task, dan LPJ
                    organisasi.
                </p>
            </div>

            {status && (
                <div className="mb-4 rounded-[4px] bg-[rgba(36,105,92,0.1)] px-3 py-2 text-sm font-medium text-[#24695c]">
                    {status}
                </div>
            )}

            <a
                href={route('auth.google.redirect')}
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2.5 text-sm font-semibold text-[#242934] shadow-sm transition hover:border-[#24695c] hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
            >
                <span className="flex h-5 w-5 items-center justify-center rounded-full border border-[#e6edef] text-xs font-bold text-[#d22d3d]">
                    G
                </span>
                Login dengan Google
            </a>

            <div className="mb-4 flex items-center gap-3">
                <div className="h-px flex-1 bg-[#e6edef]" />
                <span className="text-xs font-medium uppercase tracking-[0.14em] text-[#717171]">
                    atau email
                </span>
                <div className="h-px flex-1 bg-[#e6edef]" />
            </div>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData(
                                    'remember',
                                    (e.target.checked || false) as false,
                                )
                            }
                        />
                        <span className="ms-2 text-sm text-[#59667a]">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="rounded-[4px] text-sm text-[#59667a] underline hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
                        >
                            Forgot your password?
                        </Link>
                    )}

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Login
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
