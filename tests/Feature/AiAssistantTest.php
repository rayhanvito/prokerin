<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Ai\AiPromptAction;
use App\Actions\Ai\DraftProposalWithAiAction;
use App\Actions\Ai\SanitizeAiPayloadAction;
use App\Actions\Ai\SummarizeLpjWithAiAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai.provider', 'fake');
        config()->set('services.ai.model', 'prokerin-local');

        $this->seed();
    }

    public function test_proposal_ai_suggestion_builds_minimized_payload_and_logs_usage(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draftId = (int) DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->value('id');

        $action = app(DraftProposalWithAiAction::class);
        $payload = $action->buildPromptPayload((int) $secretary->id, $draftId);
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('owner@prokerin.test', $payloadJson);
        $this->assertStringNotContainsString('+628111111111', $payloadJson);
        $this->assertStringNotContainsString('whatsapp', strtolower($payloadJson));
        $this->assertStringNotContainsString('email', strtolower($payloadJson));
        $this->assertStringNotContainsString('storage_path', strtolower($payloadJson));

        $this->actingAs($secretary)
            ->post(route('reports.proposal-drafts.ai-suggestions', ['proposalDraft' => $draftId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Saran AI proposal berhasil dibuat.')
            ->assertSessionHas('aiSuggestion');

        $this->assertDatabaseHas('ai_usage_logs', [
            'organization_id' => (int) $payload['organization']['id'],
            'user_id' => $secretary->id,
            'action_type' => 'proposal_draft',
            'provider' => 'fake',
            'model' => 'prokerin-local',
            'prompt_hash' => AiPromptAction::promptHashForPayload('proposal_draft', $payload),
        ]);
    }

    public function test_lpj_ai_summary_builds_minimized_payload_and_logs_usage(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $action = app(SummarizeLpjWithAiAction::class);
        $payload = $action->buildPromptPayload((int) $secretary->id, $projectId);
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('sekretaris@prokerin.test', $payloadJson);
        $this->assertStringNotContainsString('+628133333333', $payloadJson);
        $this->assertStringNotContainsString('whatsapp', strtolower($payloadJson));
        $this->assertStringNotContainsString('email', strtolower($payloadJson));
        $this->assertStringNotContainsString('storage_path', strtolower($payloadJson));

        $this->actingAs($secretary)
            ->post(route('reports.lpj.ai-summary', ['project' => $projectId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Ringkasan AI LPJ berhasil dibuat.')
            ->assertSessionHas('aiSuggestion');

        $this->assertDatabaseHas('ai_usage_logs', [
            'organization_id' => (int) $payload['organization']['id'],
            'user_id' => $secretary->id,
            'action_type' => 'lpj_summary',
            'provider' => 'fake',
            'model' => 'prokerin-local',
            'prompt_hash' => AiPromptAction::promptHashForPayload('lpj_summary', $payload),
        ]);
    }

    public function test_ai_features_are_blocked_for_free_plan_organizations(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draftId = (int) DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->value('id');
        $organizationId = (int) DB::table('projects')
            ->join('proposal_drafts', 'proposal_drafts.project_id', '=', 'projects.id')
            ->where('proposal_drafts.id', $draftId)
            ->value('projects.organization_id');

        DB::table('organizations')->where('id', $organizationId)->update(['plan_tier' => 'free']);

        $this->actingAs($secretary)
            ->post(route('reports.proposal-drafts.ai-suggestions', ['proposalDraft' => $draftId]))
            ->assertForbidden();

        $this->assertDatabaseMissing('ai_usage_logs', [
            'organization_id' => $organizationId,
            'action_type' => 'proposal_draft',
        ]);
    }

    public function test_ai_payload_sanitizer_removes_sensitive_keys_recursively(): void
    {
        $payload = app(SanitizeAiPayloadAction::class)->execute([
            'project' => [
                'name' => 'Seminar',
                'pic_email' => 'lead@kampus.test',
                'documents' => [
                    ['name' => 'LPJ', 'storage_path' => 'secret/lpj.pdf'],
                ],
            ],
            'participants' => [
                ['name' => 'Peserta A', 'whatsapp_number' => '+628123456789'],
            ],
        ]);

        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('Seminar', $payloadJson);
        $this->assertStringNotContainsString('lead@kampus.test', $payloadJson);
        $this->assertStringNotContainsString('secret/lpj.pdf', $payloadJson);
        $this->assertStringNotContainsString('+628123456789', $payloadJson);
    }
}
