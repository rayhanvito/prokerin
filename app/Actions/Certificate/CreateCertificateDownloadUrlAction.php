<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class CreateCertificateDownloadUrlAction
{
    public function execute(int $actorUserId, string $certificateNumber): string
    {
        $certificate = DB::table('certificate_recipients')
            ->join('organization_members', 'organization_members.organization_id', '=', 'certificate_recipients.organization_id')
            ->where('certificate_recipients.certificate_number', $certificateNumber)
            ->where('organization_members.user_id', $actorUserId)
            ->select('certificate_recipients.pdf_path')
            ->first();

        abort_if($certificate === null || blank($certificate->pdf_path), 404);

        return Storage::disk('s3')->temporaryUrl((string) $certificate->pdf_path, now()->addMinutes(10));
    }
}
