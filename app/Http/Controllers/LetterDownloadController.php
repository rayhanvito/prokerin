<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LetterDownloadController extends Controller
{
    public function show(Request $request, int $letter): StreamedResponse
    {
        $record = DB::table('letters')
            ->join('organization_members', 'organization_members.organization_id', '=', 'letters.organization_id')
            ->where('letters.id', $letter)
            ->where('organization_members.user_id', $request->user()?->id)
            ->first(['letters.rendered_pdf_path', 'letters.letter_number']);

        abort_if($record === null || ! is_string($record->rendered_pdf_path), 404);

        return Storage::disk('public')->download(
            (string) $record->rendered_pdf_path,
            str_replace('/', '-', (string) $record->letter_number).'.pdf',
        );
    }
}
