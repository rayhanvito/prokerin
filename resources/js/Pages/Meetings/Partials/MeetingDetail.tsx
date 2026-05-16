import type {
    MeetingFormOptions,
    MeetingItem,
} from '../Index';
import MeetingAttendanceSection from './MeetingAttendanceSection';
import MeetingExportSection from './MeetingExportSection';
import MeetingMinutesEditor from './MeetingMinutesEditor';
import MeetingStatusForm from './MeetingStatusForm';

interface Props {
    meeting: MeetingItem;
    formOptions: MeetingFormOptions;
}

export default function MeetingDetail({ meeting, formOptions }: Props) {
    return (
        <div className="mt-5 space-y-4 rounded-[4px] bg-[#f5f7fb] p-5">
            {formOptions.canManage && (
                <MeetingStatusForm meeting={meeting} formOptions={formOptions} />
            )}

            <MeetingAttendanceSection
                meeting={meeting}
                formOptions={formOptions}
            />

            {formOptions.canManage && (
                <MeetingMinutesEditor meeting={meeting} />
            )}

            <MeetingExportSection meeting={meeting} />
        </div>
    );
}
