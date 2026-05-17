<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Letter\DraftLetterAction;
use App\Actions\Letter\GetLetterCreatePayloadAction;
use App\Actions\Letter\GetLetterDetailPayloadAction;
use App\Actions\Letter\GetLetterIndexPayloadAction;
use App\Actions\Letter\MarkLetterSentAction;
use App\Actions\Letter\SignLetterAction;
use App\Actions\Letter\SubmitLetterForSigningAction;
use App\Http\Requests\DraftLetterRequest;
use App\Http\Requests\SignLetterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LetterController extends Controller
{
    public function index(Request $request, GetLetterIndexPayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Letters/Index', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function create(Request $request, GetLetterCreatePayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Letters/Create', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function store(DraftLetterRequest $request, DraftLetterAction $draftLetter): RedirectResponse
    {
        $letterId = $draftLetter->execute(
            actorUserId: (int) $request->user()->id,
            templateId: (int) $request->validated('template_id'),
            projectId: $request->validated('project_id') === null ? null : (int) $request->validated('project_id'),
            subject: (string) $request->validated('subject'),
            recipientName: (string) $request->validated('recipient_name'),
            recipientOrganization: $request->validated('recipient_organization') === null ? null : (string) $request->validated('recipient_organization'),
            bodyData: $request->validated('body_data') ?? [],
        );

        return redirect()->route('letters.show', ['letter' => $letterId])->with('success', 'Draft surat berhasil dibuat.');
    }

    public function show(Request $request, int $letter, GetLetterDetailPayloadAction $payload): Response
    {
        return Inertia::render('Letters/Show', $payload->execute((int) $request->user()->id, $letter));
    }

    public function submit(Request $request, int $letter, SubmitLetterForSigningAction $submitLetter): RedirectResponse
    {
        $submitLetter->execute((int) $request->user()->id, $letter);

        return back()->with('success', 'Surat diajukan untuk tanda tangan.');
    }

    public function sign(SignLetterRequest $request, int $letter, SignLetterAction $signLetter): RedirectResponse
    {
        $signLetter->execute((int) $request->user()->id, $letter);

        return back()->with('success', 'Surat berhasil ditandatangani dan PDF dibuat.');
    }

    public function markSent(Request $request, int $letter, MarkLetterSentAction $markLetterSent): RedirectResponse
    {
        $markLetterSent->execute((int) $request->user()->id, $letter);

        return back()->with('success', 'Surat ditandai terkirim.');
    }
}
