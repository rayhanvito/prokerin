import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';
import { CalendarDays, RefreshCw } from 'lucide-react';
import { useState } from 'react';

interface CalendarSyncSectionProps {
    enabled: boolean;
    feedUrl: string | null;
}

export default function CalendarSyncSection({
    enabled,
    feedUrl,
}: CalendarSyncSectionProps) {
    const [copied, setCopied] = useState(false);

    const copyFeedUrl = async () => {
        if (feedUrl === null) {
            return;
        }

        await navigator.clipboard.writeText(feedUrl);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 2000);
    };

    return (
        <section>
            <header className="flex items-start gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-[4px] bg-[#24695c]/10 text-[#24695c]">
                    <CalendarDays className="h-5 w-5" />
                </span>
                <div>
                    <h2 className="text-lg font-semibold text-[#242934]">
                        Calendar Sync
                    </h2>
                    <p className="mt-1 text-sm text-[#59667a]">
                        Subscribe URL ini di Google Calendar, Apple Calendar,
                        atau Outlook untuk melihat rapat, deadline task, dan
                        deadline proker.
                    </p>
                </div>
            </header>

            <div className="mt-6 space-y-4">
                {enabled && feedUrl !== null ? (
                    <>
                        <div>
                            <label
                                htmlFor="calendar_sync_url"
                                className="text-sm font-medium text-[#242934]"
                            >
                                Feed URL
                            </label>
                            <TextInput
                                id="calendar_sync_url"
                                className="mt-1 block w-full"
                                value={feedUrl}
                                readOnly
                            />
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <PrimaryButton type="button" onClick={copyFeedUrl}>
                                {copied ? 'Copied' : 'Copy URL'}
                            </PrimaryButton>
                            <Link
                                href={route('profile.calendar-sync.store')}
                                method="post"
                                as="button"
                                className="inline-flex items-center gap-2 rounded-[4px] border border-[#e6edef] px-4 py-2 text-sm font-semibold text-[#59667a] transition hover:border-[#24695c] hover:text-[#24695c]"
                            >
                                <RefreshCw className="h-4 w-4" />
                                Regenerate URL
                            </Link>
                        </div>
                    </>
                ) : (
                    <div className="rounded-[4px] border border-dashed border-[#e6edef] bg-[#f5f7fb] p-4">
                        <p className="text-sm text-[#59667a]">
                            Belum ada feed URL. Generate URL untuk mulai sync
                            kalender pribadi.
                        </p>
                        <Link
                            href={route('profile.calendar-sync.store')}
                            method="post"
                            as="button"
                            className="mt-4 inline-flex rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43]"
                        >
                            Generate URL
                        </Link>
                    </div>
                )}

                <div className="grid gap-3 text-sm text-[#59667a] md:grid-cols-3">
                    <p className="rounded-[4px] bg-[#f5f7fb] p-3">
                        Google Calendar: Add calendar by URL.
                    </p>
                    <p className="rounded-[4px] bg-[#f5f7fb] p-3">
                        Apple Calendar: File → New Calendar Subscription.
                    </p>
                    <p className="rounded-[4px] bg-[#f5f7fb] p-3">
                        Outlook: Add calendar → Subscribe from web.
                    </p>
                </div>
            </div>
        </section>
    );
}
