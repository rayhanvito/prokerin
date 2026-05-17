<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateDocumentExportJob;
use App\Models\DocumentExport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

final class ObservabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_document_export_job_failure_notifies_requester(): void
    {
        $requester = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organization = Organization::query()->firstOrFail();
        $exportId = $this->createFailedExport($organization, $requester)->id;

        (new GenerateDocumentExportJob((int) $exportId))->failed(new RuntimeException('Renderer timeout'));

        $this->assertDatabaseHas('document_exports', [
            'id' => $exportId,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $requester->id,
            'type' => 'App\\Notifications\\QueueJobFailedNotification',
        ]);
    }

    public function test_super_admin_can_retry_failed_document_export_from_endpoint(): void
    {
        Queue::fake();

        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $requester = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organization = Organization::query()->firstOrFail();
        $export = $this->createFailedExport($organization, $requester);

        $this->actingAs($superAdmin)
            ->post(route('document-exports.retry', $export))
            ->assertRedirect();

        $this->assertDatabaseHas('document_exports', [
            'id' => $export->id,
            'status' => 'queued',
        ]);

        Queue::assertPushed(
            GenerateDocumentExportJob::class,
            fn (GenerateDocumentExportJob $job): bool => $job->documentExportId === $export->id,
        );
    }

    public function test_regular_user_cannot_retry_document_export(): void
    {
        Queue::fake();

        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $requester = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organization = Organization::query()->firstOrFail();
        $export = $this->createFailedExport($organization, $requester);

        $this->actingAs($member)
            ->post(route('document-exports.retry', $export))
            ->assertForbidden();

        $this->assertDatabaseHas('document_exports', [
            'id' => $export->id,
            'status' => 'failed',
        ]);

        Queue::assertNothingPushed();
    }

    public function test_sentry_ignores_expected_http_and_validation_exceptions(): void
    {
        $ignored = config('sentry.ignore_exceptions');

        $this->assertContains(AuthenticationException::class, $ignored);
        $this->assertContains(AuthorizationException::class, $ignored);
        $this->assertContains(NotFoundHttpException::class, $ignored);
        $this->assertContains(ValidationException::class, $ignored);
    }

    private function createFailedExport(Organization $organization, User $requester): DocumentExport
    {
        $id = (int) DB::table('document_exports')->insertGetId([
            'organization_id' => $organization->id,
            'project_id' => null,
            'requested_by_user_id' => $requester->id,
            'document_title' => 'QA Export',
            'document_type' => 'lpj',
            'format' => 'pdf',
            'queue_name' => 'exports',
            'engine' => 'browsershot',
            'storage_disk' => 's3',
            'output_path' => 'exports/qa-export.pdf',
            'status' => 'failed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DocumentExport::query()->findOrFail($id);
    }
}
