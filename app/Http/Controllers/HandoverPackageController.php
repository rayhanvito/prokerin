<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\InitiateHandoverPackageAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class HandoverPackageController extends Controller
{
    public function store(Request $request, InitiateHandoverPackageAction $initiateHandoverPackage): RedirectResponse
    {
        $initiateHandoverPackage->execute((int) $request->user()->id);

        return back()->with('success', 'Paket handover berhasil disiapkan.');
    }
}
