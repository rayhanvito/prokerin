import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface AuthUserExtended {
    email: string;
    id: number;
    name: string;
    email_verified_at?: string;
    whatsapp_number?: string | null;
    whatsapp_opt_in?: boolean;
}

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}) {
    const fallbackUser: AuthUserExtended = {
        email: '',
        id: 0,
        name: '',
        whatsapp_number: '',
        whatsapp_opt_in: true,
    };

    const user = (usePage().props.auth.user ?? fallbackUser) as AuthUserExtended;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
            whatsapp_number: user.whatsapp_number ?? '',
            whatsapp_opt_in: user.whatsapp_opt_in ?? true,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-semibold text-[#242934]">
                    Profile Information
                </h2>

                <p className="mt-1 text-sm text-[#59667a]">
                    Update your account's profile information, email, dan preferensi WhatsApp.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                <div>
                    <InputLabel
                        htmlFor="whatsapp_number"
                        value="Nomor WhatsApp"
                    />

                    <TextInput
                        id="whatsapp_number"
                        className="mt-1 block w-full"
                        value={data.whatsapp_number}
                        onChange={(e) =>
                            setData('whatsapp_number', e.target.value)
                        }
                        placeholder="+62812..."
                        autoComplete="tel"
                    />

                    <InputError
                        className="mt-2"
                        message={errors.whatsapp_number}
                    />
                </div>

                <div className="flex items-center gap-3">
                    <input
                        id="whatsapp_opt_in"
                        type="checkbox"
                        checked={data.whatsapp_opt_in}
                        onChange={(e) =>
                            setData('whatsapp_opt_in', e.target.checked)
                        }
                        className="h-4 w-4 rounded border-[#e6edef] text-[#24695c] focus:ring-[#24695c]"
                    />
                    <label
                        htmlFor="whatsapp_opt_in"
                        className="text-sm text-[#242934]"
                    >
                        Aktifkan notifikasi WhatsApp dari Prokerin (reminder
                        task, update approval, alert rapat).
                    </label>
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="mt-2 text-sm text-[#242934]">
                            Your email address is unverified.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="rounded-[4px] text-sm text-[#59667a] underline hover:text-[#24695c] focus:outline-none focus:ring-2 focus:ring-[#24695c] focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-sm font-medium text-[#24695c]">
                                A new verification link has been sent to your
                                email address.
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Save</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-[#59667a]">Saved.</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
