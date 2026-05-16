import { usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Info } from 'lucide-react';

import { cn } from '@/lib/utils';

type FlashTone = 'success' | 'error' | 'status';

const toneClass = {
    success: 'border-[#24695c]/20 bg-[#24695c]/10 text-[#24695c]',
    error: 'border-[#d22d3d]/20 bg-[#d22d3d]/10 text-[#d22d3d]',
    status: 'border-[#ba895d]/20 bg-[#ba895d]/10 text-[#8a633f]',
};

export default function FlashBanner() {
    const { flash } = usePage().props;
    const message = flash.success ?? flash.error ?? flash.status ?? null;
    const tone = resolveTone(flash);

    if (!message) {
        return null;
    }

    const Icon =
        tone === 'success'
            ? CheckCircle2
            : tone === 'error'
              ? AlertCircle
              : Info;

    return (
        <div
            className={cn(
                'mb-6 flex items-start gap-3 rounded-[4px] border px-4 py-3 text-sm font-medium',
                toneClass[tone],
            )}
        >
            <Icon className="mt-0.5 h-4 w-4 shrink-0" />
            <span>{message}</span>
        </div>
    );
}

function resolveTone(flash: {
    success?: string;
    error?: string;
    status?: string;
}): FlashTone {
    if (flash.success) {
        return 'success';
    }

    if (flash.error) {
        return 'error';
    }

    return 'status';
}
