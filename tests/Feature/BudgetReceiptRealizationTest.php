<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BudgetReceiptRealizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_treasurer_can_upload_receipt_backed_budget_realization(): void
    {
        Queue::fake();
        Storage::fake('s3');

        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $budgetLineId = $this->budgetLineId('Publikasi dan printing');

        $this->actingAs($treasurer)
            ->post(route('finance.realizations.store', ['budgetLine' => $budgetLineId]), [
                'name' => 'Cetak poster tambahan',
                'amount' => 250000,
                'receipt' => UploadedFile::fake()->create('receipt-poster.jpg', 256, 'image/jpeg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Realisasi anggaran berhasil dicatat dan receipt masuk antrean review.');

        $document = DB::table('documents')->where('name', 'receipt-poster.jpg')->first();

        $this->assertNotNull($document);
        $this->assertSame('Finance Receipts', $document->folder);
        $this->assertSame('restricted', $document->visibility);
        $this->assertSame('review', $document->status);
        Storage::disk('s3')->assertExists((string) $document->storage_path);

        $this->assertDatabaseHas('budget_transactions', [
            'budget_line_id' => $budgetLineId,
            'receipt_document_id' => $document->id,
            'name' => 'Cetak poster tambahan',
            'amount' => 250000,
            'status' => 'review',
        ]);

        $this->assertDatabaseHas('budget_lines', [
            'id' => $budgetLineId,
            'realized_amount' => 900000,
            'status' => 'realized',
        ]);
        Queue::assertPushed(
            SendWhatsAppReminderJob::class,
            fn (SendWhatsAppReminderJob $job): bool => $job->messageType === 'finance_approval_requested'
                && str_contains($job->message, 'Cetak poster tambahan'),
        );
    }

    public function test_member_cannot_upload_budget_realization_receipt(): void
    {
        Storage::fake('s3');

        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('finance.realizations.store', ['budgetLine' => $this->budgetLineId('Publikasi dan printing')]), [
                'name' => 'Cetak poster tambahan',
                'amount' => 250000,
                'receipt' => UploadedFile::fake()->create('receipt-poster.jpg', 256, 'image/jpeg'),
            ])
            ->assertForbidden();
    }

    public function test_review_budget_line_cannot_receive_realization(): void
    {
        Storage::fake('s3');

        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->post(route('finance.realizations.store', ['budgetLine' => $this->budgetLineId('Sewa aula dan sound system')]), [
                'name' => 'DP sewa aula',
                'amount' => 500000,
                'receipt' => UploadedFile::fake()->create('receipt-aula.pdf', 256, 'application/pdf'),
            ])
            ->assertSessionHasErrors('budget_line');
    }

    public function test_receipt_upload_rejects_unsupported_file_types(): void
    {
        Storage::fake('s3');

        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->post(route('finance.realizations.store', ['budgetLine' => $this->budgetLineId('Publikasi dan printing')]), [
                'name' => 'Cetak poster tambahan',
                'amount' => 250000,
                'receipt' => UploadedFile::fake()->create('receipt.txt', 12, 'text/plain'),
            ])
            ->assertSessionHasErrors('receipt');
    }

    private function budgetLineId(string $name): int
    {
        return (int) DB::table('budget_lines')->where('name', $name)->value('id');
    }
}
