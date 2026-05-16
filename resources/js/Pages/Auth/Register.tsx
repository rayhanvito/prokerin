import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="mb-6">
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                    Create workspace access
                </p>
                <h1 className="mt-2 text-2xl font-semibold text-[#242934]">
                    Buat akun Prokerin
                </h1>
                <p className="mt-2 text-sm leading-6 text-[#59667a]">
                    Akun ini akan dipakai untuk login dan menerima invitation
                    role organisasi.
                </p>
            </div>

            <a
                href={route('auth.google.redirect')}
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-[4px] border border-[#e6edef] bg-white px-4 py-2.5 text-sm font-semibold text-[#242934] shadow-sm transition hover:border-[#24695c] hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
            >
                <span className="flex h-5 w-5 items-center justify-center rounded-full border border-[#e6edef] text-xs font-bold text-[#d22d3d]">
                    G
                </span>
                Daftar dengan Google
            </a>

            <div className="mb-4 flex items-center gap-3">
                <div className="h-px flex-1 bg-[#e6edef]" />
                <span className="text-xs font-medium uppercase tracking-[0.14em] text-[#717171]">
                    atau form
                </span>
                <div className="h-px flex-1 bg-[#e6edef]" />
            </div>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
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
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <Link
                        href={route('login')}
                        className="rounded-[4px] text-sm text-[#59667a] underline hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
