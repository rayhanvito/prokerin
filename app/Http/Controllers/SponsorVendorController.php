<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Organization\StoreSponsorVendorAction;
use App\Actions\Organization\UpdateSponsorVendorAction;
use App\Http\Requests\StoreSponsorVendorRequest;
use App\Http\Requests\UpdateSponsorVendorRequest;
use Illuminate\Http\RedirectResponse;

final class SponsorVendorController extends Controller
{
    public function store(StoreSponsorVendorRequest $request, StoreSponsorVendorAction $storeSponsorVendor): RedirectResponse
    {
        $storeSponsorVendor->execute((int) $request->user()->id, $request->validated());

        return back()->with('success', 'Sponsor/vendor berhasil ditambahkan.');
    }

    public function update(
        UpdateSponsorVendorRequest $request,
        int $sponsorVendor,
        UpdateSponsorVendorAction $updateSponsorVendor,
    ): RedirectResponse {
        $updateSponsorVendor->execute((int) $request->user()->id, $sponsorVendor, $request->validated());

        return back()->with('success', 'Sponsor/vendor berhasil diperbarui.');
    }
}
