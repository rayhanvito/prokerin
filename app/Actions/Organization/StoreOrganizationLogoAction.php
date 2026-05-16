<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\DTOs\Organization\OrganizationLogoPlanData;
use App\DTOs\Organization\OrganizationLogoUploadData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final readonly class StoreOrganizationLogoAction
{
    public function __construct(
        private PlanOrganizationLogoUploadAction $planLogoUpload,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $organizationId, UploadedFile $logo): OrganizationLogoPlanData
    {
        $plan = $this->planLogoUpload->execute(
            new OrganizationLogoUploadData(
                organizationId: (string) $organizationId,
                originalName: $logo->getClientOriginalName(),
                mimeType: (string) $logo->getMimeType(),
                sizeInKilobytes: (int) ceil($logo->getSize() / 1024),
            ),
        );

        if (! $plan->isValid || $plan->storagePath === null) {
            throw ValidationException::withMessages([
                'logo' => $plan->errors,
            ]);
        }

        Storage::disk($plan->disk)->put($plan->storagePath, $logo->getContent());

        DB::table('organizations')
            ->where('id', $organizationId)
            ->update([
                'logo_path' => $plan->storagePath,
                'updated_at' => now(),
            ]);

        return $plan;
    }
}
