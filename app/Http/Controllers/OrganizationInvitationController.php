<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Membership\RespondOrganizationInvitationAction;
use App\Actions\Membership\SendOrganizationInvitationAction;
use App\Domain\Membership\InvitationStatus;
use App\Http\Requests\SendOrganizationInvitationRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class OrganizationInvitationController extends Controller
{
    public function store(
        SendOrganizationInvitationRequest $request,
        SendOrganizationInvitationAction $sendInvitation,
    ): RedirectResponse {
        try {
            $sendInvitation->execute((int) $request->user()->id, $request->validated());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Invitation berhasil dikirim.');
    }

    public function show(string $token): Response
    {
        $invitation = DB::table('organization_invitations')
            ->join('organizations', 'organizations.id', '=', 'organization_invitations.organization_id')
            ->where('organization_invitations.token', $token)
            ->first([
                'organization_invitations.email',
                'organization_invitations.role',
                'organization_invitations.status',
                'organization_invitations.expires_at',
                'organizations.name as organization_name',
            ]);

        abort_if($invitation === null, 404);

        return Inertia::render('Invitations/Show', [
            'invitation' => [
                'email' => (string) $invitation->email,
                'role' => (string) $invitation->role,
                'status' => (string) $invitation->status,
                'expiresAt' => $invitation->expires_at === null ? null : (string) $invitation->expires_at,
                'organizationName' => (string) $invitation->organization_name,
                'isOpen' => (string) $invitation->status === InvitationStatus::Pending->value,
                'acceptUrl' => route('invitations.accept', ['token' => $token], false),
                'declineUrl' => route('invitations.decline', ['token' => $token], false),
            ],
        ]);
    }

    public function accept(
        Request $request,
        string $token,
        RespondOrganizationInvitationAction $respondInvitation,
    ): RedirectResponse {
        try {
            $respondInvitation->accept((int) $request->user()->id, $token);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Invitation berhasil diterima.');
    }

    public function decline(
        Request $request,
        string $token,
        RespondOrganizationInvitationAction $respondInvitation,
    ): RedirectResponse {
        try {
            $respondInvitation->decline((int) $request->user()->id, $token);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('dashboard')->with('status', 'Invitation sudah ditolak.');
    }
}
