<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MarkLetterSentAction
{
    use AuthorizesLetterAccess;

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function execute(int $actorUserId, int $letterId): void
    {
        $letter = $this->letterForActor($actorUserId, $letterId);
        $this->authorizeActiveOrganizationRole($actorUserId, (int) $letter->organization_id, ['organization_owner', 'organization_admin', 'secretary']);

        if ((string) $letter->status !== LetterStatus::Signed->value) {
            throw ValidationException::withMessages(['letter' => 'Surat harus ditandatangani sebelum ditandai terkirim.']);
        }

        DB::table('letters')
            ->where('id', $letterId)
            ->update([
                'status' => LetterStatus::Sent->value,
                'updated_at' => now(),
            ]);
    }
}
