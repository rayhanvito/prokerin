<?php

declare(strict_types=1);

namespace Tests\Feature\Proposal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProposalRichTextSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_save_tiptap_json_section_body(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();
        $sections = json_decode((string) $draft->sections, true);
        $sections[0]['body'] = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
                    'content' => [['type' => 'text', 'text' => 'Latar belakang revisi']],
                ],
                [
                    'type' => 'bulletList',
                    'content' => [
                        [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Masalah terpetakan']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($secretary)
            ->patch(route('reports.proposal-drafts.update', ['proposalDraft' => $draft->id]), [
                'sections' => $sections,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Draft proposal berhasil disimpan.');

        $updatedSections = json_decode((string) DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->value('sections'), true);

        $this->assertSame('doc', $updatedSections[0]['body']['type']);
        $this->assertSame('heading', $updatedSections[0]['body']['content'][0]['type']);
        $this->assertSame('Masalah terpetakan', $updatedSections[0]['body']['content'][1]['content'][0]['content'][0]['content'][0]['text']);
    }
}
