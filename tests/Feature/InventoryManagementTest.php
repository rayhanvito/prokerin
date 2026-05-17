<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\CheckOverdueInventoryLoansJob;
use App\Models\User;
use App\Notifications\InventoryLoanOverdueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_create_inventory_item_with_unique_qr_token(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('inventory.store'), $this->itemPayload())
            ->assertRedirect()
            ->assertSessionHas('success', 'Inventaris berhasil ditambahkan.');

        $item = DB::table('inventory_items')->first();

        $this->assertNotNull($item);
        $this->assertSame('Banner BEM', $item->name);
        $this->assertSame('available', $item->status);
        $this->assertSame(20, strlen((string) $item->qr_token));
    }

    public function test_member_can_request_loan_and_owner_can_approve_it(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $itemId = $this->createInventoryItem($owner);

        $this->actingAs($member)
            ->post(route('inventory.loans.store', ['item' => $itemId]), [
                'expected_return_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'notes' => 'Dipakai untuk booth open recruitment.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Permintaan peminjaman inventaris dikirim.');

        $loanId = (int) DB::table('inventory_loans')->value('id');

        $this->assertDatabaseHas('inventory_loans', [
            'id' => $loanId,
            'borrower_user_id' => $member->id,
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->patch(route('inventory.loans.approve', ['loan' => $loanId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Peminjaman inventaris disetujui.');

        $this->assertDatabaseHas('inventory_loans', [
            'id' => $loanId,
            'status' => 'approved',
            'approved_by_user_id' => $owner->id,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $itemId,
            'status' => 'loaned',
        ]);
    }

    public function test_return_loan_with_same_condition_makes_item_available(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        [$itemId, $loanId] = $this->approvedLoan($owner, $member);

        $this->actingAs($owner)
            ->patch(route('inventory.loans.return', ['loan' => $loanId]), [
                'return_condition' => 'same',
                'notes' => 'Aman.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Inventaris dicatat sudah kembali.');

        $this->assertDatabaseHas('inventory_loans', [
            'id' => $loanId,
            'status' => 'returned',
            'return_condition' => 'same',
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $itemId,
            'status' => 'available',
            'condition' => 'good',
        ]);
    }

    public function test_return_loan_with_damaged_condition_updates_item_condition(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        [$itemId, $loanId] = $this->approvedLoan($owner, $member);

        $this->actingAs($owner)
            ->patch(route('inventory.loans.return', ['loan' => $loanId]), [
                'return_condition' => 'damaged',
                'notes' => 'Stand patah.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'id' => $itemId,
            'status' => 'available',
            'condition' => 'needs_repair',
        ]);
    }

    public function test_cross_org_member_cannot_request_other_org_inventory(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();
        $itemId = $this->createInventoryItem($owner);

        $this->actingAs($otherOwner)
            ->withSession(['active_organization_id' => $this->organizationId('ukm-kreatif')])
            ->post(route('inventory.loans.store', ['item' => $itemId]), [
                'expected_return_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('inventory_loans', 0);
    }

    public function test_qr_lookup_requires_active_org_scope(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $itemId = $this->createInventoryItem($owner);
        $token = (string) DB::table('inventory_items')->where('id', $itemId)->value('qr_token');

        $this->actingAs($owner)
            ->get(route('inventory.qr.show', ['token' => $token]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Inventory/QrLookup')
                ->where('item.name', 'Banner BEM'));
    }

    public function test_handover_snapshot_includes_inventory_totals(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $this->createInventoryItem($owner);

        $this->actingAs($owner)
            ->post(route('organization.handover.store'))
            ->assertRedirect();

        $package = DB::table('handover_packages')->first();
        $snapshot = json_decode((string) $package->snapshot, true);

        $this->assertSame(1, $snapshot['inventory']['total']);
        $this->assertDatabaseHas('handover_items', [
            'package_id' => $package->id,
            'label' => 'Verifikasi inventaris organisasi',
        ]);
    }

    public function test_overdue_job_notifies_borrower_once(): void
    {
        Notification::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        [, $loanId] = $this->approvedLoan($owner, $member);

        DB::table('inventory_loans')
            ->where('id', $loanId)
            ->update(['expected_return_at' => now()->subDay()]);

        (new CheckOverdueInventoryLoansJob)->handle();

        Notification::assertSentTo($member, InventoryLoanOverdueNotification::class);
        $this->assertNotNull(DB::table('inventory_loans')->where('id', $loanId)->value('overdue_notified_at'));
    }

    /**
     * @return array<string, string>
     */
    private function itemPayload(): array
    {
        return [
            'name' => 'Banner BEM',
            'category' => 'Banner',
            'description' => 'Banner utama untuk booth dan seminar.',
            'location' => 'Sekretariat',
            'condition' => 'good',
            'purchased_at' => '2026-01-10',
            'purchase_amount' => '350000',
        ];
    }

    private function createInventoryItem(User $owner): int
    {
        $this->actingAs($owner)->post(route('inventory.store'), $this->itemPayload());

        return (int) DB::table('inventory_items')->where('name', 'Banner BEM')->value('id');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function approvedLoan(User $owner, User $member, ?string $expectedReturnAt = null): array
    {
        $itemId = $this->createInventoryItem($owner);

        $this->actingAs($member)
            ->post(route('inventory.loans.store', ['item' => $itemId]), [
                'expected_return_at' => $expectedReturnAt ?? now()->addDays(3)->format('Y-m-d H:i:s'),
            ]);

        $loanId = (int) DB::table('inventory_loans')->value('id');

        $this->actingAs($owner)
            ->patch(route('inventory.loans.approve', ['loan' => $loanId]));

        return [$itemId, $loanId];
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
