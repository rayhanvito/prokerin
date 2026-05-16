<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Report\CalculateLpjReadinessAction;
use App\DTOs\Report\LpjChecklistItemData;
use PHPUnit\Framework\TestCase;

final class CalculateLpjReadinessActionTest extends TestCase
{
    public function test_it_calculates_lpj_readiness(): void
    {
        $readiness = (new CalculateLpjReadinessAction)->execute([
            new LpjChecklistItemData('Data realisasi anggaran lengkap', true),
            new LpjChecklistItemData('Dokumentasi kegiatan terunggah', true),
            new LpjChecklistItemData('Daftar hadir panitia dan peserta', false),
            new LpjChecklistItemData('Catatan opsional', false, isRequired: false),
        ]);

        $this->assertSame(3, $readiness->requiredItemCount);
        $this->assertSame(2, $readiness->completedRequiredItemCount);
        $this->assertSame(67, $readiness->completionProgress->percentage);
        $this->assertFalse($readiness->isReadyForReview);
        $this->assertSame(['Daftar hadir panitia dan peserta'], $readiness->missingRequiredItems);
    }

    public function test_lpj_is_ready_when_required_items_complete(): void
    {
        $readiness = (new CalculateLpjReadinessAction)->execute([
            new LpjChecklistItemData('Data realisasi anggaran lengkap', true),
            new LpjChecklistItemData('Dokumentasi kegiatan terunggah', true),
        ]);

        $this->assertTrue($readiness->isReadyForReview);
        $this->assertSame(100, $readiness->completionProgress->percentage);
    }

    public function test_empty_lpj_checklist_is_not_ready_for_review(): void
    {
        $readiness = (new CalculateLpjReadinessAction)->execute([]);

        $this->assertFalse($readiness->isReadyForReview);
        $this->assertSame(0, $readiness->completionProgress->percentage);
    }
}
