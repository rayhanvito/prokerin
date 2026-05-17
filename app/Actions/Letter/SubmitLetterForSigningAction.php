<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitLetterForSigningAction
{
    use AuthorizesLetterAccess;

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function execute(int $actorUserId, int $letterId): void
    {
        $letter = $this->letterForActor($actorUserId, $letterId);
        $this->authorizeActiveOrganizationRole($actorUserId, (int) $letter->organization_id, ['organization_owner', 'organization_admin', 'secretary']);

        if ((string) $letter->status !== LetterStatus::Draft->value) {
            throw ValidationException::withMessages(['letter' => 'Hanya draft surat yang bisa diajukan tanda tangan.']);
        }

        DB::table('letters')
            ->where('id', $letterId)
            ->update([
                'status' => LetterStatus::Submitted->value,
                'updated_at' => now(),
            ]);
    }
}
