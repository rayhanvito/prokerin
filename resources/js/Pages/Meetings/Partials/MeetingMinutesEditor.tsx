import { useForm } from '@inertiajs/react';
import { Plus, X } from 'lucide-react';

import type { MeetingActionItem, MeetingItem } from '../Index';

interface Props {
    meeting: MeetingItem;
}

interface MinutesForm {
    summary: string;
    decisions: string[];
    action_items: MeetingActionItem[];
    publish: boolean;
}

export default function MeetingMinutesEditor({ meeting }: Props) {
    const form = useForm<MinutesForm>({
        summary: meeting.minutes?.summary ?? '',
        decisions: meeting.minutes?.decisions ?? [],
        action_items: meeting.minutes?.actionItems ?? [],
        publish: meeting.minutes?.publishedAt !== null,
    });

    const addDecision = () => {
        form.setData('decisions', [...form.data.decisions, '']);
    };

    const updateDecision = (index: number, value: string) => {
        const next = [...form.data.decisions];
        next[index] = value;
        form.setData('decisions', next);
    };

    const removeDecision = (index: number) => {
        form.setData(
            'decisions',
            form.data.decisions.filter((_, i) => i !== index),
        );
    };

    const addActionItem = () => {
        form.setData('action_items', [
            ...form.data.action_items,
            { task: '', owner: '', due: '', status: 'open' },
        ]);
    };

    const updateActionItem = (
        index: number,
        field: keyof MeetingActionItem,
        value: string,
    ) => {
        const next = form.data.action_items.map((item, i) =>
            i === index ? { ...item, [field]: value } : item,
        );
        form.setData('action_items', next);
    };

    const removeActionItem = (index: number) => {
        form.setData(
            'action_items',
            form.data.action_items.filter((_, i) => i !== index),
        );
    };

    const submit = (publish: boolean) => () => {
        form.transform((data) => ({ ...data, publish }));

        form.patch(route('meetings.minutes.update', { meeting: meeting.id }), {
            preserveScroll: true,
        });
    };

    return (
        <div className="rounded-[4px] bg-white p-4 ring-1 ring-[#e6edef]">
            <div className="flex items-center justify-between">
                <p className="text-sm font-semibold text-[#242934]">
                    Notulen
                    {meeting.minutes?.publishedAt !== null
                        ? ` · Published ${meeting.minutes?.publishedAt}`
                        : meeting.minutes
                          ? ' · Draft'
                          : ''}
                </p>
            </div>

            <form className="mt-3 space-y-4" onSubmit={(event) => event.preventDefault()}>
                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Ringkasan
                    </label>
                    <textarea
                        value={form.data.summary}
                        onChange={(event) =>
                            form.setData('summary', event.target.value)
                        }
                        rows={3}
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                    />
                    {form.errors.summary && (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.summary}
                        </p>
                    )}
                </div>

                <div>
                    <div className="flex items-center justify-between">
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Keputusan
                        </label>
                        <button
                            type="button"
                            onClick={addDecision}
                            className="inline-flex items-center gap-1 text-xs font-semibold text-[#24695c]"
                        >
                            <Plus className="h-3.5 w-3.5" /> Tambah keputusan
                        </button>
                    </div>
                    <div className="mt-2 space-y-2">
                        {form.data.decisions.length === 0 && (
                            <p className="text-xs text-[#717171]">
                                Belum ada keputusan tercatat.
                            </p>
                        )}
                        {form.data.decisions.map((decision, index) => (
                            <div
                                key={`decision-${index}`}
                                className="flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    value={decision}
                                    onChange={(event) =>
                                        updateDecision(
                                            index,
                                            event.target.value,
                                        )
                                    }
                                    className="flex-1 rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                                />
                                <button
                                    type="button"
                                    onClick={() => removeDecision(index)}
                                    className="text-[#d22d3d]"
                                    aria-label="Hapus keputusan"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                <div>
                    <div className="flex items-center justify-between">
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Action Items
                        </label>
                        <button
                            type="button"
                            onClick={addActionItem}
                            className="inline-flex items-center gap-1 text-xs font-semibold text-[#24695c]"
                        >
                            <Plus className="h-3.5 w-3.5" /> Tambah tindak lanjut
                        </button>
                    </div>
                    <div className="mt-2 space-y-3">
                        {form.data.action_items.length === 0 && (
                            <p className="text-xs text-[#717171]">
                                Belum ada tindak lanjut.
                            </p>
                        )}
                        {form.data.action_items.map((item, index) => (
                            <div
                                key={`action-${index}`}
                                className="grid gap-2 rounded-[4px] bg-[#f5f7fb] p-3 sm:grid-cols-[2fr_1fr_1fr_120px_auto]"
                            >
                                <input
                                    type="text"
                                    value={item.task}
                                    placeholder="Task"
                                    onChange={(event) =>
                                        updateActionItem(
                                            index,
                                            'task',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                                />
                                <input
                                    type="text"
                                    value={item.owner}
                                    placeholder="PIC"
                                    onChange={(event) =>
                                        updateActionItem(
                                            index,
                                            'owner',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                                />
                                <input
                                    type="text"
                                    value={item.due}
                                    placeholder="Due"
                                    onChange={(event) =>
                                        updateActionItem(
                                            index,
                                            'due',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                                />
                                <select
                                    value={item.status}
                                    onChange={(event) =>
                                        updateActionItem(
                                            index,
                                            'status',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-[4px] border border-[#e6edef] px-2 py-2 text-sm"
                                >
                                    <option value="open">Open</option>
                                    <option value="in_progress">
                                        In Progress
                                    </option>
                                    <option value="done">Done</option>
                                </select>
                                <button
                                    type="button"
                                    onClick={() => removeActionItem(index)}
                                    className="text-[#d22d3d]"
                                    aria-label="Hapus tindak lanjut"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        onClick={submit(false)}
                        disabled={form.processing}
                        className="inline-flex items-center justify-center rounded-[4px] bg-white px-4 py-2 text-sm font-semibold text-[#24695c] ring-1 ring-[#24695c] hover:bg-[rgba(36,105,92,0.05)] disabled:opacity-60"
                    >
                        Simpan draft
                    </button>
                    <button
                        type="button"
                        onClick={submit(true)}
                        disabled={form.processing}
                        className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43] disabled:opacity-60"
                    >
                        Publish notulen
                    </button>
                </div>
            </form>
        </div>
    );
}
