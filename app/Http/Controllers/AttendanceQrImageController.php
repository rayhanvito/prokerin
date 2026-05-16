<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Attendance\RenderAttendanceQrSvgAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AttendanceQrImageController extends Controller
{
    public function show(
        Request $request,
        RenderAttendanceQrSvgAction $renderQr,
    ): Response {
        $token = (string) $request->query('token', '');

        if ($token === '' || strlen($token) > 200) {
            abort(422, 'Token tidak valid.');
        }

        $svg = $renderQr->execute($token);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
