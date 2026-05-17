import { FormEventHandler } from 'react';

import { PlusCircle } from 'lucide-react';

import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/react';

export interface TaskProjectOption {
    id: number;
    name: string;
}

interface TaskQuickAddProps {
    projects: TaskProjectOption[];
    compact?: boolean;
}

export default function TaskQuickAdd({
    projects,
    compact = false,
}: TaskQuickAddProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        project_id: projects[0]?.id ? String(projects[0].id) : '',
        title: '',
        due_at: '',
    });

    const submit: FormEventHandler<HTMLFormElement> = (event): void => {
        event.preventDefault();

        post(route('tasks.store'), {
            preserveScroll: true,
            onSuccess: () => reset('title', 'due_at'),
        });
    };

    return (
        <form
            onSubmit={submit}
            className={cn(
                'grid gap-3',
                compact ? 'md:grid-cols-[1fr_auto]' : 'lg:grid-cols-[1fr_1fr_auto]',
            )}
        >
            <div>
                <label className="sr-only" htmlFor="task-title">
                    Judul task
                </label>
                <input
                    id="task-title"
                    type="text"
                    value={data.title}
                    onChange={(event) => setData('title', event.target.value)}
                    placeholder="Tambah task baru"
                    className="h-10 w-full rounded-[4px] border border-[#e6edef] bg-white px-3 text-sm text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                />
                {errors.title ? (
                    <p className="mt-1 text-xs font-medium text-[#d22d3d]">
                        {errors.title}
                    </p>
                ) : null}
            </div>

            <div className={cn(compact && 'hidden')}>
                <label className="sr-only" htmlFor="task-project">
                    Proker
                </label>
                <select
                    id="task-project"
                    value={data.project_id}
                    onChange={(event) =>
                        setData('project_id', event.target.value)
                    }
                    className="h-10 w-full rounded-[4px] border border-[#e6edef] bg-white px-3 text-sm font-medium text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                >
                    {projects.map((project) => (
                        <option key={project.id} value={project.id}>
                            {project.name}
                        </option>
                    ))}
                </select>
                {errors.project_id ? (
                    <p className="mt-1 text-xs font-medium text-[#d22d3d]">
                        {errors.project_id}
                    </p>
                ) : null}
            </div>

            <div className={cn(compact && 'hidden')}>
                <label className="sr-only" htmlFor="task-due-at">
                    Deadline
                </label>
                <input
                    id="task-due-at"
                    type="date"
                    value={data.due_at}
                    onChange={(event) => setData('due_at', event.target.value)}
                    className="h-10 w-full rounded-[4px] border border-[#e6edef] bg-white px-3 text-sm font-medium text-[#242934] outline-none transition focus:border-[#24695c] focus:ring-2 focus:ring-[#24695c]/10"
                />
                {errors.due_at ? (
                    <p className="mt-1 text-xs font-medium text-[#d22d3d]">
                        {errors.due_at}
                    </p>
                ) : null}
            </div>

            <button
                type="submit"
                disabled={processing || projects.length === 0}
                className="inline-flex h-10 items-center justify-center gap-2 rounded-[4px] bg-[#24695c] px-4 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:opacity-60"
            >
                <PlusCircle className="h-4 w-4" />
                Tambah
            </button>
        </form>
    );
}
