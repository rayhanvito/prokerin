<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Certificate\VerifyCertificateAction;
use Inertia\Inertia;
use Inertia\Response;

final class CertificateVerificationController extends Controller
{
    public function show(string $token, VerifyCertificateAction $verifyCertificate): Response
    {
        return Inertia::render('Certificates/Verify', $verifyCertificate->execute($token));
    }
}
