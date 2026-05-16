<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Actions\Document\ValidateDocumentUploadAction;
use App\Domain\Document\DocumentVisibility;
use App\Domain\Finance\BudgetStatus;
use App\DTOs\Document\DocumentUploadCandidateData;
use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Finance\BudgetRealizationData;
use App\Support\ValueObjects\Money;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class StoreBudgetReceiptRealizationAction
{
    public function __construct(
        private readonly RecordBudgetRealizationAction $recordBudgetRealization,
        private readonly ValidateDocumentUploadAction $validateDocumentUpload,
    ) {}

    /**
     * @return array{transaction_id: int, document_id: int, realized_amount: int}
     *
     * @throws ValidationException
     */
    public function execute(
        int $actorUserId,
        int $budgetLineId,
        string $transactionName,
        int $amount,
        UploadedFile $receipt,
    ): array {
        return DB::transaction(function () use ($actorUserId, $budgetLineId, $transactionName, $amount, $receipt): array {
            $line = DB::table('budget_lines')
                ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
                ->where('budget_lines.id', $budgetLineId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'treasurer'])
                ->select([
                    'budget_lines.id',
                    'budget_lines.project_id',
                    'budget_lines.name',
                    'budget_lines.category',
                    'budget_lines.planned_amount',
                    'budget_lines.realized_amount',
                    'budget_lines.status',
                    'projects.organization_id',
                    'projects.slug as project_slug',
                ])
                ->lockForUpdate()
                ->first();

            if ($line === null) {
                throw new NotFoundHttpException('Budget line was not found for the active workspace.');
            }

            $validation = $this->validateDocumentUpload->execute(
                new DocumentUploadCandidateData(
                    originalName: $receipt->getClientOriginalName(),
                    mimeType: (string) $receipt->getMimeType(),
                    sizeInKilobytes: (int) ceil(max(1, (int) $receipt->getSize()) / 1024),
                    visibility: DocumentVisibility::Restricted,
                ),
            );

            if (! $validation->isValid) {
                throw ValidationException::withMessages([
                    'receipt' => $validation->errors,
                ]);
            }

            try {
                $updatedLine = $this->recordBudgetRealization->execute(
                    new BudgetLineData(
                        name: (string) $line->name,
                        category: (string) $line->category,
                        plannedAmount: Money::rupiah((int) $line->planned_amount),
                        realizedAmount: Money::rupiah((int) $line->realized_amount),
                        status: BudgetStatus::from((string) $line->status),
                    ),
                    new BudgetRealizationData(
                        transactionName: $transactionName,
                        amount: Money::rupiah($amount),
                        hasReceipt: true,
                    ),
                );
            } catch (DomainException $exception) {
                throw ValidationException::withMessages([
                    'budget_line' => $exception->getMessage(),
                ]);
            }

            $now = now();
            $storedPath = $this->storeReceipt($receipt, (string) $line->project_slug, $now->format('YmdHis'));

            $documentId = (int) DB::table('documents')->insertGetId([
                'organization_id' => (int) $line->organization_id,
                'project_id' => (int) $line->project_id,
                'owner_user_id' => $actorUserId,
                'name' => $receipt->getClientOriginalName(),
                'folder' => 'Finance Receipts',
                'storage_path' => $storedPath,
                'mime_type' => (string) $receipt->getMimeType(),
                'size_kb' => (int) ceil(max(1, (int) $receipt->getSize()) / 1024),
                'visibility' => DocumentVisibility::Restricted->value,
                'status' => 'review',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $transactionId = (int) DB::table('budget_transactions')->insertGetId([
                'budget_line_id' => $budgetLineId,
                'receipt_document_id' => $documentId,
                'name' => $transactionName,
                'amount' => $amount,
                'status' => 'review',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('budget_lines')
                ->where('id', $budgetLineId)
                ->update([
                    'realized_amount' => $updatedLine->realizedAmount->amount,
                    'status' => $updatedLine->status->value,
                    'updated_at' => $now,
                ]);

            return [
                'transaction_id' => $transactionId,
                'document_id' => $documentId,
                'realized_amount' => $updatedLine->realizedAmount->amount,
            ];
        });
    }

    private function storeReceipt(UploadedFile $receipt, string $projectSlug, string $timestamp): string
    {
        $extension = $receipt->getClientOriginalExtension() ?: $receipt->extension();
        $filename = $timestamp.'-'.Str::random(10).'.'.$extension;
        $directory = 'documents/'.$projectSlug.'/finance-receipts';

        Storage::disk('s3')->putFileAs($directory, $receipt, $filename, [
            'visibility' => 'private',
        ]);

        return $directory.'/'.$filename;
    }
}
