<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Organization\StoreOrganizationLogoAction;
use App\Http\Requests\UploadOrganizationLogoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrganizationLogoController extends Controller
{
    public function store(UploadOrganizationLogoRequest $request, StoreOrganizationLogoAction $storeLogo): RedirectResponse
    {
        $organizationId = DB::table('organization_members')
            ->where('user_id', $request->user()?->id)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->orderBy('id')
            ->value('organization_id');

        if (! is_numeric($organizationId)) {
            throw new NotFoundHttpException('Active organization was not found.');
        }

        $logo = $request->file('logo');

        if (! $logo instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'logo' => 'Logo organisasi wajib diunggah.',
            ]);
        }

        $storeLogo->execute((int) $organizationId, $logo);

        return back()->with('success', 'Logo organisasi berhasil diperbarui.');
    }
}
