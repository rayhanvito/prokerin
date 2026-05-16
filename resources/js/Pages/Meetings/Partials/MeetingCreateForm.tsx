import { router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

import VihoCard from '@/Components/Viho/VihoCard';

import type { MeetingFormOptions } from '../Index';

interface Props {
    formOptions: MeetingFormOptions;
    onSuccess: () => void;
}

interface CreateForm {
    title: string;
    agenda: string;
    starts_at: string;
    ends_at: string;
    location: string;
    project_id: string;
    attendee_user_ids: number[];
}

export default function MeetingCreateForm({ formOptions, onSuccess }: Props) {
    const form = useForm<CreateForm>({
        title: '',
        agenda: '',
        starts_at: '',
        ends_at: '',
        location: '',
        project_id: '',
        attendee_user_ids: [],
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(route('meetings.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onSuccess();
                router.reload({ only: ['meetings', 'metrics', 'latestMinutes'] });
            },
        });
    };

    const toggleAttendee = (id: number) => {
        const current = form.data.attendee_user_ids;
        form.setData(
            'attendee_user_ids',
            current.includes(id)
                ? current.filter((value) => value !== id)
                : [...current, id],
        );
    };

    return (
        <VihoCard
            title="Buat Rapat Baru"
            subtitle="Atur agenda, lokasi, project terkait, dan undang anggota organisasi."
        >
            <form onSubmit={submit} className="space-y-4">
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Judul Rapat
                        </label>
                        <input
                            type="text"
                            value={form.data.title}
                            onChange={(event) =>
                                form.setData('title', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                            required
                        />
                        {form.errors.title && (
                            <p className="mt-1 text-xs text-[#d22d3d]">
                                {form.errors.title}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Project (opsional)
                        </label>
                        <select
                            value={form.data.project_id}
                            onChange={(event) =>
                                form.setData('project_id', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        >
                            <option value="">— Tanpa project —</option>
                            {formOptions.projects.map((project) => (
                                <option key={project.id} value={project.id}>
                                    {project.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Agenda
                    </label>
                    <textarea
                        value={form.data.agenda}
                        onChange={(event) =>
                            form.setData('agenda', event.target.value)
                        }
                        rows={3}
                        className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        required
                    />
                    {form.errors.agenda && (
                        <p className="mt-1 text-xs text-[#d22d3d]">
                            {form.errors.agenda}
                        </p>
                    )}
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Mulai
                        </label>
                        <input
                            type="datetime-local"
                            value={form.data.starts_at}
                            onChange={(event) =>
                                form.setData('starts_at', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                            required
                        />
                        {form.errors.starts_at && (
                            <p className="mt-1 text-xs text-[#d22d3d]">
                                {form.errors.starts_at}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Selesai (opsional)
                        </label>
                        <input
                            type="datetime-local"
                            value={form.data.ends_at}
                            onChange={(event) =>
                                form.setData('ends_at', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        />
                        {form.errors.ends_at && (
                            <p className="mt-1 text-xs text-[#d22d3d]">
                                {form.errors.ends_at}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                            Lokasi
                        </label>
                        <input
                            type="text"
                            value={form.data.location}
                            onChange={(event) =>
                                form.setData('location', event.target.value)
                            }
                            className="mt-1 w-full rounded-[4px] border border-[#e6edef] px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-[#59667a]">
                        Undang Anggota
                    </label>
                    <div className="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {formOptions.organizationMembers.map((member) => {
                            const checked = form.data.attendee_user_ids.includes(
                                member.id,
                            );

                            return (
                                <label
                                    key={member.id}
                                    className={`flex cursor-pointer items-center gap-2 rounded-[4px] border px-3 py-2 text-sm ${
                                        checked
                                            ? 'border-[#24695c] bg-[rgba(36,105,92,0.05)]'
                                            : 'border-[#e6edef] bg-white'
                                    }`}
                                >
                                    <input
                                        type="checkbox"
                                        checked={checked}
                                        onChange={() => toggleAttendee(member.id)}
                                    />
                                    <span className="font-medium text-[#242934]">
                                        {member.name}
                                    </span>
                                    <span className="text-xs text-[#717171]">
                                        {member.role}
                                    </span>
                                </label>
                            );
                        })}
                    </div>
                </div>

                <div className="flex justify-end gap-2">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b4c43] disabled:opacity-60"
                    >
                        Simpan rapat
                    </button>
                </div>
            </form>
        </VihoCard>
    );
}
