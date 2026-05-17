import { BellRing, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { requestPermissionAndSubscribe } from '@/lib/webpush';

interface EnableWebPushBannerProps {
    publicKey: string | null;
    subscribed: boolean;
}

export default function EnableWebPushBanner({
    publicKey,
    subscribed,
}: EnableWebPushBannerProps) {
    const [dismissed, setDismissed] = useState(() =>
        window.localStorage.getItem('prokerin-webpush-dismissed') === '1',
    );
    const [processing, setProcessing] = useState(false);

    if (
        subscribed ||
        dismissed ||
        publicKey === null ||
        !('Notification' in window) ||
        Notification.permission === 'denied'
    ) {
        return null;
    }

    const dismiss = (): void => {
        window.localStorage.setItem('prokerin-webpush-dismissed', '1');
        setDismissed(true);
    };

    const enable = async (): Promise<void> => {
        setProcessing(true);

        try {
            await requestPermissionAndSubscribe(publicKey);
            toast.success('Notifikasi browser aktif.');
            dismiss();
        } catch (error) {
            const message =
                error instanceof Error
                    ? error.message
                    : 'Gagal mengaktifkan notifikasi browser.';
            toast.error(message);
        } finally {
            setProcessing(false);
        }
    };

    return (
        <div className="mb-4 flex flex-col gap-3 rounded-[4px] border border-[#e6edef] bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div className="flex gap-3">
                <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                    <BellRing className="h-5 w-5" />
                </span>
                <div>
                    <p className="text-sm font-semibold text-[#242934]">
                        Aktifkan notifikasi browser
                    </p>
                    <p className="mt-1 text-sm text-[#59667a]">
                        Dapatkan pengingat approval, deadline task, dan LPJ
                        walau tab Prokerin sedang tidak dibuka.
                    </p>
                </div>
            </div>
            <div className="flex shrink-0 items-center gap-2">
                <button
                    type="button"
                    onClick={enable}
                    disabled={processing}
                    className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-3 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:opacity-70"
                >
                    {processing ? 'Mengaktifkan...' : 'Aktifkan'}
                </button>
                <button
                    type="button"
                    onClick={dismiss}
                    aria-label="Tutup banner notifikasi browser"
                    className="inline-flex h-9 w-9 items-center justify-center rounded-[4px] text-[#59667a] hover:bg-[#f5f7fb] hover:text-[#242934]"
                >
                    <X className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
