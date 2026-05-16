import { router } from '@inertiajs/react';
import { ChangeEvent } from 'react';

import type { MeetingFormOptions, MeetingItem } from '../Index';

interface Props {
    meeting: MeetingItem;
    formOptions: MeetingFormOptions;
}

export default function MeetingAttendanceSection({
    meeting,
    formOptions,
}: Props) {
    const updateAttendance = (
        attendeeId: number,
        event: ChangeEvent<HTMLSelectElement>,
    ) => {
        router.patch(
            route('meetings.attendees.update', { attendee: attendeeId }),
            { attendance_status: event.target.value },
            { preserveScroll: true },
        );
    };

    return (
        <div className="rounded-[4px] bg-white p-4 ring-1 ring-[#e6edef]">
            <div className="flex items-center justify-between">
                <p className="text-sm font-semibold text-[#242934]">
                    Daftar Hadir ({meeting.presentCount}/{meeting.attendeeCount})
                </p>
            </div>

            {meeting.attendees.length === 0 ? (
                <p className="mt-2 text-sm text-[#717171]">
                    Belum ada peserta diundang.
                </p>
            ) : (
                <ul className="mt-3 divide-y divide-[#e6edef]">
                    {meeting.attendees.map((attendee) => (
                        <li
                            key={attendee.id}
                            className="grid gap-3 py-2 text-sm sm:grid-cols-[1fr_180px] sm:items-center"
                        >
                            <div>
                                <p className="font-medium text-[#242934]">
                                    {attendee.name}
                                </p>
                                <p className="text-xs text-[#717171]">
                                    {attendee.role ?? '-'}
                                </p>
                            </div>
                            {formOptions.canManage ? (
                                <select
                                    value={attendee.attendanceStatus}
                                    onChange={(event) =>
                                        updateAttendance(attendee.id, event)
                                    }
                                    className="rounded-[4px] border border-[#e6edef] px-2 py-1 text-sm"
                                >
                                    {formOptions.attendanceStatusOptions.map(
                                        (option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ),
                                    )}
                                </select>
                            ) : (
                                <span className="rounded-[4px] bg-[#f5f7fb] px-3 py-1 text-xs font-semibold text-[#59667a]">
                                    {attendee.attendanceStatus}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
