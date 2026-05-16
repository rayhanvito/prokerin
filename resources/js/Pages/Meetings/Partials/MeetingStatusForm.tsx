import { router } from '@inertiajs/react';
import { ChangeEvent } from 'react';

import type { MeetingFormOptions, MeetingItem } from '../Index';

interface Props {
    meeting: MeetingItem;
    formOptions: MeetingFormOptions;
}

export default function MeetingStatusForm({ meeting, formOptions }: Props) {
    const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const status = event.target.value;

        router.patch(
            route('meetings.update', { meeting: meeting.id }),
            { status },
            { preserveScroll: true },
        );
    };

    return (
        <div className="rounded-[4px] bg-white p-4 ring-1 ring-[#e6edef]">
            <p className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                Status rapat
            </p>
            <select
                value={meeting.status}
                onChange={handleChange}
                className="mt-2 w-full max-w-xs rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
            >
                {formOptions.statusOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    );
}
