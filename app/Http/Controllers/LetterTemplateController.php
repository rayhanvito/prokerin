<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Letter\CreateLetterTemplateAction;
use App\Actions\Letter\GetLetterTemplatePayloadAction;
use App\Actions\Letter\UpdateLetterTemplateAction;
use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Letter\LetterType;
use App\Http\Requests\StoreLetterTemplateRequest;
use App\Http\Requests\UpdateLetterTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class LetterTemplateController extends Controller
{
    public function index(Request $request, GetLetterTemplatePayloadAction $payload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Letters/Templates', $payload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function store(
        StoreLetterTemplateRequest $request,
        CreateLetterTemplateAction $createLetterTemplate,
        GetActiveOrganizationContextAction $activeOrganizationContext,
    ): RedirectResponse {
        $activeOrganizationId = $request->session()->get('active_organization_id');
        $context = $activeOrganizationContext->execute(
            (int) $request->user()->id,
            is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        );

        $createLetterTemplate->execute(
            actorUserId: (int) $request->user()->id,
            organizationId: $context->organizationId,
            name: (string) $request->validated('name'),
            letterType: LetterType::from((string) $request->validated('letter_type')),
            templateHtml: (string) $request->validated('template_html'),
            numberingPattern: (string) $request->validated('numbering_pattern'),
            signatoryUserId: $request->validated('signatory_user_id') === null ? null : (int) $request->validated('signatory_user_id'),
        );

        return back()->with('success', 'Template surat berhasil dibuat.');
    }

    public function update(
        UpdateLetterTemplateRequest $request,
        int $template,
        UpdateLetterTemplateAction $updateLetterTemplate,
    ): RedirectResponse {
        $updateLetterTemplate->execute(
            actorUserId: (int) $request->user()->id,
            templateId: $template,
            name: (string) $request->validated('name'),
            letterType: LetterType::from((string) $request->validated('letter_type')),
            templateHtml: (string) $request->validated('template_html'),
            numberingPattern: (string) $request->validated('numbering_pattern'),
            signatoryUserId: $request->validated('signatory_user_id') === null ? null : (int) $request->validated('signatory_user_id'),
            isActive: (bool) $request->validated('is_active'),
        );

        return back()->with('success', 'Template surat berhasil diperbarui.');
    }

    public function destroy(Request $request, int $template): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $templateRecord = DB::table('letter_templates')->where('id', $template)->first(['organization_id']);
        abort_if($templateRecord === null, 404);

        $canDelete = DB::table('organization_members')
            ->where('organization_id', $templateRecord->organization_id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->exists();

        abort_unless($canDelete, 403);

        DB::table('letter_templates')
            ->where('id', $template)
            ->update([
                'deleted_at' => now(),
                'is_active' => false,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Template surat diarsipkan.');
    }
}
