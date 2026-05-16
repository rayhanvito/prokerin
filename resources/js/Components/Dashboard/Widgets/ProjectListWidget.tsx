import { Link } from '@inertiajs/react';

import VihoCard from '@/Components/Viho/VihoCard';
import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import type { ProkerSummary } from '@/types/dashboard';

export default function ProjectListWidget({
    title,
    projects,
}: {
    title: string;
    projects: ProkerSummary[];
}) {
    return (
        <VihoCard title={title}>
            <div className="-m-5 divide-y divide-[#e6edef]">
                {projects.length > 0 ? (
                    projects.map((project) => (
                        <Link
                            key={project.id}
                            href={route('proker.detail', project.slug)}
                            className="block p-5 transition hover:bg-[#f5f7fb]"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <p className="font-semibold text-[#242934]">
                                    {project.name}
                                </p>
                                <VihoStatusBadge>
                                    {project.status}
                                </VihoStatusBadge>
                            </div>
                            <p className="mt-1 text-sm text-[#717171]">
                                {project.projectLead} · deadline{' '}
                                {project.deadline ?? '-'}
                            </p>
                            <div className="mt-3 h-2 rounded-full bg-[#e6edef]">
                                <div
                                    className="h-2 rounded-full bg-[#24695c]"
                                    style={{
                                        width: `${project.progressPercentage}%`,
                                    }}
                                />
                            </div>
                        </Link>
                    ))
                ) : (
                    <div className="p-5 text-sm text-[#59667a]">
                        Belum ada proker untuk ditampilkan.
                    </div>
                )}
            </div>
        </VihoCard>
    );
}
