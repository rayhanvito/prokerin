import { router, usePage } from '@inertiajs/react';

import { PageProps } from '@/types';

export default function ImpersonationBanner() {
    const { impersonating, auth } = usePage<PageProps>().props;

    if (!impersonating?.active) {
        return null;
    }

    const handleStop = () => {
        router.get(impersonating.leaveUrl);
    };

    return (
        <div className="sticky top-0 z-40 flex items-center justify-between gap-4 border-b border-amber-200 bg-amber-100 px-4 py-2 text-sm text-amber-900">
            <div className="flex items-center gap-2">
                <span className="inline-flex h-2 w-2 rounded-full bg-amber-500" />
                <span>
                    You are impersonating{' '}
                    <strong>{auth.user?.name ?? 'this user'}</strong>
                    {impersonating.impersonator
                        ? ` (signed in as ${impersonating.impersonator})`
                        : ''}
                    .
                </span>
            </div>
            <button
                type="button"
                onClick={handleStop}
                className="inline-flex items-center rounded-md bg-amber-600 px-3 py-1 text-xs font-semibold text-white hover:bg-amber-700"
            >
                Stop Impersonating
            </button>
        </div>
    );
}
