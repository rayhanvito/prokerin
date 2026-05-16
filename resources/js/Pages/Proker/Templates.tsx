import { BookTemplate, CalendarDays, GraduationCap, Trophy, Users } from 'lucide-react';

import VihoCard from '@/Components/Viho/VihoCard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { ProjectTemplateSummary, ProjectTemplateType } from '@/types/prokerin';
import { Head, Link, useForm } from '@inertiajs/react';

const templateIcons: Record<ProjectTemplateType, typeof GraduationCap> = {
    competition: Trophy,
    makrab: Users,
    seminar: GraduationCap,
    workshop: BookTemplate,
};

interface ProkerTemplatesProps {
    templates: ProjectTemplateSummary[];
}

export default function ProkerTemplates({ templates }: ProkerTemplatesProps) {
    const { post, processing } = useForm();

    const generateFromTemplate = (templateType: ProjectTemplateType): void => {
        post(route('proker.templates.generate', templateType), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#24695c]">
                        M05 · Template Proker
                    </p>
                    <h1 className="text-xl font-semibold tracking-tight text-[#242934] sm:text-2xl">
                        Template Proker
                    </h1>
                </div>
            }
        >
            <Head title="Template Proker" />

            <div className="space-y-6">
                <VihoCard>
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-xl font-semibold text-[#242934]">
                                Library template kegiatan
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-[#59667a]">
                                Template ini disusun dari payload backend untuk
                                membuat draft proker, timeline, task, RAB, dan
                                checklist LPJ default.
                            </p>
                        </div>
                        <Link
                            href={route('proker.create')}
                            className="inline-flex items-center gap-2 rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <CalendarDays className="h-4 w-4" />
                            Buat Custom
                        </Link>
                    </div>
                </VihoCard>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {templates.map((template) => {
                        const Icon = templateIcons[template.type];

                        return (
                            <VihoCard key={template.type}>
                                <span className="inline-flex h-12 w-12 items-center justify-center rounded-[4px] bg-[rgba(36,105,92,0.1)] text-[#24695c]">
                                    <Icon className="h-6 w-6" />
                                </span>
                                <h2 className="mt-5 text-lg font-semibold text-[#242934]">
                                    {template.label}
                                </h2>
                                <p className="mt-2 min-h-16 text-sm leading-6 text-[#59667a]">
                                    {template.plan.proposalOutline}
                                </p>
                                <div className="mt-5 rounded-[4px] bg-[#f5f7fb] px-3 py-2 text-sm font-semibold text-[#ba895d]">
                                    {template.plan.tasks.length} task ·{' '}
                                    {template.plan.budgetLines.length} RAB
                                </div>
                                <button
                                    type="button"
                                    disabled={processing}
                                    onClick={() =>
                                        generateFromTemplate(template.type)
                                    }
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-[4px] bg-[#24695c] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1b4c43] disabled:cursor-not-allowed disabled:bg-[#9fb8b3]"
                                >
                                    Gunakan Template
                                </button>
                            </VihoCard>
                        );
                    })}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
