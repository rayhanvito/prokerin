<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Letter\LetterType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class LetterGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_owner_can_create_template(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('letters.templates.store'), [
                'name' => 'Surat Peminjaman Aula',
                'letter_type' => LetterType::RoomReservation->value,
                'template_html' => '<p>{{letter_number}} {{recipient_name}} {{project_name}}</p>',
                'numbering_pattern' => 'B.{seq}/BEM-FT/{roman_month}/{year}',
                'signatory_user_id' => $owner->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Template surat berhasil dibuat.');

        $this->assertDatabaseHas('letter_templates', ['name' => 'Surat Peminjaman Aula']);
    }

    public function test_secretary_can_draft_letter_with_unique_monthly_number(): void
    {
        $secretary = User::query()->where('email', 'secretary@prokerin.test')->firstOrFail();
        $templateId = $this->templateId(LetterType::RoomReservation);

        $this->actingAs($secretary)->post(route('letters.store'), $this->draftPayload($templateId))->assertRedirect();
        $this->actingAs($secretary)->post(route('letters.store'), $this->draftPayload($templateId, 'Surat kedua'))->assertRedirect();

        $numbers = DB::table('letters')->where('template_id', $templateId)->orderBy('id')->pluck('letter_number')->all();

        $this->assertCount(2, $numbers);
        $this->assertNotSame($numbers[0], $numbers[1]);
        $this->assertStringContainsString('/V/2026', (string) $numbers[0]);
    }

    public function test_signatory_can_sign_letter_and_generate_pdf(): void
    {
        Storage::fake('public');

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $letterId = $this->draftLetterAsSecretary();

        $this->actingAs($owner)
            ->post(route('letters.sign', ['letter' => $letterId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Surat berhasil ditandatangani dan PDF dibuat.');

        $path = (string) DB::table('letters')->where('id', $letterId)->value('rendered_pdf_path');

        $this->assertDatabaseHas('letters', ['id' => $letterId, 'status' => 'signed', 'signed_by_user_id' => $owner->id]);
        Storage::disk('public')->assertExists($path);
    }

    public function test_bulk_issue_participation_creates_letters_for_members(): void
    {
        $secretary = User::query()->where('email', 'secretary@prokerin.test')->firstOrFail();
        $projectId = $this->projectId('seminar-karier-digital');
        $recipientUserIds = DB::table('organization_members')
            ->where('organization_id', $this->organizationId('bem-fakultas-teknologi'))
            ->limit(10)
            ->pluck('user_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $this->actingAs($secretary)
            ->post(route('letters.bulk-issue-participation.store', ['project' => $projectId]), ['recipient_user_ids' => $recipientUserIds])
            ->assertRedirect()
            ->assertSessionHas('success', count($recipientUserIds).' surat partisipasi berhasil dibuat.');

        $this->assertSame(count($recipientUserIds), DB::table('letters')->where('project_id', $projectId)->where('letter_type', LetterType::ParticipationCertificate->value)->count());
    }

    public function test_cross_org_admin_cannot_sign_other_org_letter(): void
    {
        $outsider = User::factory()->create();
        DB::table('organization_members')->insert([
            'organization_id' => $this->organizationId('hima-informatika'),
            'user_id' => $outsider->id,
            'role' => 'organization_admin',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $letterId = $this->draftLetterAsSecretary();

        $this->actingAs($outsider)->post(route('letters.sign', ['letter' => $letterId]))->assertNotFound();
    }

    public function test_letter_pages_render_payloads(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('letters.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Letters/Index')->has('metrics')->has('letters'));

        $this->actingAs($owner)
            ->get(route('letters.templates.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Letters/Templates')->has('templates', 6));
    }

    private function draftPayload(int $templateId, string $subject = 'Permohonan Peminjaman Aula'): array
    {
        return [
            'template_id' => $templateId,
            'project_id' => $this->projectId('seminar-karier-digital'),
            'subject' => $subject,
            'recipient_name' => 'Kepala Bagian Sarana',
            'recipient_organization' => 'Fakultas Teknologi',
            'body_data' => ['event_location' => 'Aula Kampus C', 'contact_person' => 'Salsa Kirana'],
        ];
    }

    private function draftLetterAsSecretary(): int
    {
        $secretary = User::query()->where('email', 'secretary@prokerin.test')->firstOrFail();
        $this->actingAs($secretary)->post(route('letters.store'), $this->draftPayload($this->templateId(LetterType::RoomReservation)));

        return (int) DB::table('letters')->latest('id')->value('id');
    }

    private function templateId(LetterType $type): int
    {
        return (int) DB::table('letter_templates')->where('organization_id', $this->organizationId('bem-fakultas-teknologi'))->where('letter_type', $type->value)->value('id');
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
