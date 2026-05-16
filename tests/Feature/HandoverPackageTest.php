<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateDocumentExportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class HandoverPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_handover_page_receives_live_tenant_scoped_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('organization.handover'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Handover')
                ->where('organization.name', 'BEM Fakultas Teknologi')
                ->where('organization.periodName', '2026')
                ->has('metrics', 3)
                ->where('package', null)
                ->where('items', [])
                ->where('canManage', true));
    }

    public function test_owner_can_initiate_handover_package_from_current_snapshot(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organization.handover.store'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Paket handover berhasil disiapkan.');

        $package = DB::table('handover_packages')->first();

        $this->assertNotNull($package);
        $this->assertSame('draft', $package->status);
        $this->assertDatabaseHas('handover_packages', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'from_period_id' => $this->periodId('bem-fakultas-teknologi', '2026'),
            'created_by' => $owner->id,
        ]);
        $this->assertDatabaseCount('handover_items', 4);

        $snapshot = json_decode((string) $package->snapshot, true);

        $this->assertSame(3, $snapshot['documents']);
        $this->assertSame(3, $snapshot['open_tasks']);
        $this->assertArrayHasKey('planned_budget', $snapshot);
        $this->assertArrayHasKey('outstanding_lpj_items', $snapshot);

        $this->actingAs($owner)
            ->get(route('organization.handover'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Handover')
                ->where('package.status', 'draft')
                ->has('items', 4)
                ->where('metrics.2.value', '4'));
    }

    public function test_member_cannot_initiate_handover_package(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('organization.handover.store'))
            ->assertForbidden();

        $this->assertDatabaseCount('handover_packages', 0);
    }

    public function test_owner_can_update_handover_item_status(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $itemId = (int) DB::table('handover_items')->value('id');

        $this->actingAs($owner)
            ->patch(route('organization.handover.items.update', ['item' => $itemId]), [
                'status' => 'done',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status item handover berhasil diperbarui.');

        $this->assertDatabaseHas('handover_items', [
            'id' => $itemId,
            'status' => 'done',
        ]);

        $this->actingAs($owner)
            ->patch(route('organization.handover.items.update', ['item' => $itemId]), [
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handover_items', [
            'id' => $itemId,
            'status' => 'pending',
        ]);
    }

    public function test_member_cannot_update_unassigned_handover_item_status(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $itemId = (int) DB::table('handover_items')->value('id');

        $this->actingAs($member)
            ->patch(route('organization.handover.items.update', ['item' => $itemId]), [
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('handover_items', [
            'id' => $itemId,
            'status' => 'pending',
        ]);
    }

    public function test_owner_can_submit_and_accept_completed_handover_package(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'submitted',
            ])
            ->assertUnprocessable();

        DB::table('handover_items')
            ->where('package_id', $packageId)
            ->update(['status' => 'done']);

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'submitted',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status paket handover berhasil diperbarui.');

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'status' => 'submitted',
        ]);
        $this->assertNotNull(DB::table('handover_packages')->where('id', $packageId)->value('submitted_at'));

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'accepted',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'status' => 'accepted',
            'accepted_by_user_id' => $owner->id,
        ]);
        $this->assertNotNull(DB::table('handover_packages')->where('id', $packageId)->value('accepted_at'));
    }

    public function test_incoming_owner_can_be_assigned_and_must_accept_handover_package(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $incomingOwner = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();

        DB::table('organization_members')
            ->where('organization_id', $this->organizationId('bem-fakultas-teknologi'))
            ->where('user_id', $incomingOwner->id)
            ->update(['role' => 'organization_owner']);

        $toPeriodId = $this->createFuturePeriod();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.transition', ['package' => $packageId]), [
                'to_period_id' => $toPeriodId,
                'incoming_owner_id' => $incomingOwner->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Penerima handover berhasil diperbarui.');

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'to_period_id' => $toPeriodId,
            'incoming_owner_id' => $incomingOwner->id,
        ]);

        DB::table('handover_items')
            ->where('package_id', $packageId)
            ->update(['status' => 'done']);

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'submitted',
            ])
            ->assertRedirect();

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'accepted',
            ])
            ->assertForbidden();

        $this->actingAs($incomingOwner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'accepted',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'status' => 'accepted',
            'accepted_by_user_id' => $incomingOwner->id,
        ]);
    }

    public function test_member_cannot_assign_handover_transition(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        $this->actingAs($member)
            ->patch(route('organization.handover.packages.transition', ['package' => $packageId]), [
                'incoming_owner_id' => $owner->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'incoming_owner_id' => null,
        ]);
    }

    public function test_member_cannot_submit_handover_package(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        DB::table('handover_items')
            ->where('package_id', $packageId)
            ->update(['status' => 'done']);

        $this->actingAs($member)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'submitted',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('handover_packages', [
            'id' => $packageId,
            'status' => 'draft',
        ]);
    }

    public function test_owner_can_queue_accepted_handover_package_export(): void
    {
        Queue::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $packageId = $this->acceptedHandoverPackageId($owner);

        $this->actingAs($owner)
            ->post(route('organization.handover.packages.export', ['package' => $packageId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Export handover PDF masuk antrean.');

        $exportId = (int) DB::table('document_exports')
            ->where('document_type', 'handover')
            ->where('format', 'pdf')
            ->value('id');

        $this->assertGreaterThan(0, $exportId);
        $this->assertDatabaseHas('document_exports', [
            'id' => $exportId,
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'project_id' => null,
            'requested_by_user_id' => $owner->id,
            'document_type' => 'handover',
            'format' => 'pdf',
            'status' => 'queued',
        ]);

        Queue::assertPushed(
            GenerateDocumentExportJob::class,
            fn (GenerateDocumentExportJob $job): bool => $job->documentExportId === $exportId,
        );
    }

    public function test_owner_cannot_queue_handover_export_before_package_is_accepted(): void
    {
        Queue::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        $this->actingAs($owner)
            ->post(route('organization.handover.packages.export', ['package' => $packageId]))
            ->assertSessionHasErrors('handoverPackage');

        $this->assertDatabaseMissing('document_exports', [
            'document_type' => 'handover',
        ]);

        Queue::assertNotPushed(GenerateDocumentExportJob::class);
    }

    public function test_member_cannot_queue_handover_package_export(): void
    {
        Queue::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $packageId = $this->acceptedHandoverPackageId($owner);

        $this->actingAs($member)
            ->post(route('organization.handover.packages.export', ['package' => $packageId]))
            ->assertNotFound();

        $this->assertDatabaseMissing('document_exports', [
            'document_type' => 'handover',
        ]);

        Queue::assertNotPushed(GenerateDocumentExportJob::class);
    }

    public function test_handover_export_job_generates_pdf_archive(): void
    {
        Queue::fake();
        Storage::fake('s3');

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $packageId = $this->acceptedHandoverPackageId($owner);

        $this->actingAs($owner)
            ->post(route('organization.handover.packages.export', ['package' => $packageId]))
            ->assertRedirect();

        $export = DB::table('document_exports')
            ->where('document_type', 'handover')
            ->first();

        $this->assertNotNull($export);

        (new GenerateDocumentExportJob((int) $export->id))->handle();

        Storage::disk('s3')->assertExists((string) $export->output_path);

        $pdf = Storage::disk('s3')->get((string) $export->output_path);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertDatabaseHas('document_exports', [
            'id' => $export->id,
            'status' => 'completed',
        ]);
    }

    private function acceptedHandoverPackageId(User $owner): int
    {
        $this->actingAs($owner)->post(route('organization.handover.store'));

        $packageId = (int) DB::table('handover_packages')->value('id');

        DB::table('handover_items')
            ->where('package_id', $packageId)
            ->update(['status' => 'done']);

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'submitted',
            ]);

        $this->actingAs($owner)
            ->patch(route('organization.handover.packages.status', ['package' => $packageId]), [
                'status' => 'accepted',
            ]);

        return $packageId;
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function periodId(string $organizationSlug, string $periodName): int
    {
        return (int) DB::table('organization_periods')
            ->join('organizations', 'organizations.id', '=', 'organization_periods.organization_id')
            ->where('organizations.slug', $organizationSlug)
            ->where('organization_periods.name', $periodName)
            ->value('organization_periods.id');
    }

    private function createFuturePeriod(): int
    {
        return (int) DB::table('organization_periods')->insertGetId([
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'name' => '2027',
            'starts_at' => '2027-01-01',
            'ends_at' => '2027-12-31',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
