<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Project\BuildProjectTemplatePlanAction;
use App\Actions\Proposal\BuildProposalDraftAction;
use App\Domain\Project\ProjectTemplateType;
use App\DTOs\Proposal\ProposalProjectData;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BuildProposalDraftActionTest extends TestCase
{
    public function test_it_builds_proposal_draft_from_project_and_template(): void
    {
        $draft = (new BuildProposalDraftAction)->execute(
            new ProposalProjectData(
                name: 'Seminar Karier Digital',
                organizationName: 'BEM Fakultas Teknologi',
                description: 'Kegiatan untuk mempertemukan mahasiswa dengan praktisi industri digital.',
                targetAudience: 'Mahasiswa aktif tingkat akhir dan pengurus organisasi.',
                startsAt: new DateTimeImmutable('2026-06-12'),
                endsAt: new DateTimeImmutable('2026-06-12'),
                projectLeadName: 'Dimas Aji',
            ),
            (new BuildProjectTemplatePlanAction)->execute(ProjectTemplateType::Seminar),
        );

        $this->assertSame('Proposal Seminar Karier Digital', $draft->title);
        $this->assertSame('BEM Fakultas Teknologi · 12 Jun 2026', $draft->subtitle);
        $this->assertCount(6, $draft->sections);
        $this->assertSame('RAB Ringkas', $draft->sections[4]['heading']);
        $this->assertStringContainsString('Rp9.750.000', $draft->sections[4]['body']);
    }

    public function test_proposal_draft_serializes_for_editor_payload(): void
    {
        $draft = (new BuildProposalDraftAction)->execute(
            new ProposalProjectData(
                name: 'Workshop UI/UX',
                organizationName: 'HIMA Informatika',
                description: 'Workshop praktik desain produk digital.',
                targetAudience: 'Mahasiswa Informatika semester 3 sampai 7.',
                startsAt: new DateTimeImmutable('2026-07-20'),
                endsAt: new DateTimeImmutable('2026-07-21'),
                projectLeadName: 'Nadia Putri',
            ),
            (new BuildProjectTemplatePlanAction)->execute(ProjectTemplateType::Workshop),
        )->toArray();

        $this->assertSame('Proposal Workshop UI/UX', $draft['title']);
        $this->assertSame('Latar Belakang', $draft['sections'][0]['heading']);
    }
}
