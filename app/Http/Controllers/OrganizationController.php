<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Organization\CreateOrganizationAction;
use App\Actions\Organization\StoreOrganizationPeriodAction;
use App\Actions\Organization\SwitchActiveOrganizationAction;
use App\Actions\Organization\UpdateOrganizationAction;
use App\Actions\Organization\UpdateOrganizationPeriodAction;
use App\DTOs\Organization\CreateOrganizationData;
use App\Http\Requests\StoreOrganizationPeriodRequest;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\SwitchActiveOrganizationRequest;
use App\Http\Requests\UpdateOrganizationPeriodRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use Illuminate\Http\RedirectResponse;

final class OrganizationController extends Controller
{
    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $createOrganization): RedirectResponse
    {
        $validated = $request->validated();
        $organizationId = $createOrganization->execute(
            (int) $request->user()->id,
            new CreateOrganizationData(
                name: (string) $validated['name'],
                slug: isset($validated['slug']) ? (string) $validated['slug'] : null,
                planTier: (string) ($validated['plan_tier'] ?? 'free'),
            ),
        );

        $request->session()->put('active_organization_id', $organizationId);

        return redirect()->route('organization.setup')->with('success', 'Organisasi berhasil dibuat.');
    }

    public function switch(SwitchActiveOrganizationRequest $request, SwitchActiveOrganizationAction $switchActiveOrganization): RedirectResponse
    {
        $switchActiveOrganization->execute(
            (int) $request->user()->id,
            (int) $request->validated('organization_id'),
        );

        return back()->with('success', 'Workspace organisasi aktif berhasil diganti.');
    }

    public function update(UpdateOrganizationRequest $request, UpdateOrganizationAction $updateOrganization): RedirectResponse
    {
        $updateOrganization->execute((int) $request->user()->id, $request->validated());

        return back()->with('success', 'Profil organisasi berhasil diperbarui.');
    }

    public function storePeriod(
        StoreOrganizationPeriodRequest $request,
        StoreOrganizationPeriodAction $storeOrganizationPeriod,
    ): RedirectResponse {
        $storeOrganizationPeriod->execute((int) $request->user()->id, $request->validated());

        return back()->with('success', 'Periode kepengurusan berhasil ditambahkan.');
    }

    public function updatePeriod(
        UpdateOrganizationPeriodRequest $request,
        int $period,
        UpdateOrganizationPeriodAction $updateOrganizationPeriod,
    ): RedirectResponse {
        $updateOrganizationPeriod->execute((int) $request->user()->id, $period, $request->validated());

        return back()->with('success', 'Periode kepengurusan berhasil diperbarui.');
    }
}
