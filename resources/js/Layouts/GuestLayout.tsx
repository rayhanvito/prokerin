import { Link, usePage } from '@inertiajs/react';
import { FolderKanban, ShieldCheck } from 'lucide-react';
import { PropsWithChildren } from 'react';

import type { PageProps } from '@/types';

export default function Guest({ children }: PropsWithChildren) {
    const { flash } = usePage<PageProps>().props;

    return (
        <div className="grid min-h-screen bg-[#f5f7fb] text-[#242934] lg:grid-cols-[1fr_480px]">
            <section className="hidden flex-col justify-between bg-[#24695c] p-10 text-white lg:flex">
                <Link href="/" className="flex items-center gap-3">
                    <img
                        src="/vendor/viho/images/logo/icon-logo.png"
                        alt=""
                        className="h-8 w-8"
                    />
                    <span className="text-lg font-bold tracking-[0.04em]">
                        PROKERIN
                    </span>
                </Link>

                <div className="max-w-xl">
                    <div className="inline-flex items-center gap-2 rounded-[4px] bg-white/10 px-3 py-1 text-sm font-semibold text-white">
                        <ShieldCheck className="h-4 w-4" />
                        Secure workspace for student organizations
                    </div>
                    <h1 className="mt-6 text-5xl font-black tracking-tight">
                        Satu pintu untuk proker, RAB, proposal, dan LPJ.
                    </h1>
                    <p className="mt-5 text-lg leading-8 text-white/80">
                        Masuk ke workspace untuk lanjut mengelola agenda,
                        anggota, dokumen, dan approval organisasi.
                    </p>
                </div>

                <div className="rounded-[4px] bg-white/10 p-5">
                    <div className="flex items-center gap-3">
                        <span className="inline-flex h-11 w-11 items-center justify-center rounded-[4px] bg-white/10 text-white">
                            <FolderKanban className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="font-bold">MVP foundation</p>
                            <p className="text-sm text-white/75">
                                Auth, organization, member roles, and proker
                                flow.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="flex min-h-screen flex-col justify-center px-4 py-10 sm:px-6 lg:px-10">
                <div className="mx-auto w-full max-w-md">
                    <Link href="/" className="mb-8 flex items-center gap-3 lg:hidden">
                        <img
                            src="/vendor/viho/images/logo/icon-logo.png"
                            alt=""
                            className="h-8 w-8"
                        />
                        <span className="text-lg font-bold tracking-[0.04em] text-[#242934]">
                            PROKERIN
                        </span>
                    </Link>

                    <div className="rounded-[4px] border border-[#e6edef] bg-white p-6 shadow-[0_25px_50px_rgba(8,21,66,0.06)]">
                        {flash.error && (
                            <div className="mb-4 rounded-[4px] border border-[rgba(210,45,61,0.25)] bg-[rgba(210,45,61,0.08)] px-3 py-2 text-sm font-medium text-[#d22d3d]">
                                {flash.error}
                            </div>
                        )}

                        {flash.success && (
                            <div className="mb-4 rounded-[4px] border border-[rgba(36,105,92,0.18)] bg-[rgba(36,105,92,0.08)] px-3 py-2 text-sm font-medium text-[#24695c]">
                                {flash.success}
                            </div>
                        )}

                        {children}
                    </div>
                </div>
            </section>
        </div>
    );
}
