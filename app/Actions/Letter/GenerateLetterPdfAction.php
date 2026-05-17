<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

final class GenerateLetterPdfAction
{
    public function __construct(private readonly RenderLetterTemplateAction $renderLetterTemplate) {}

    /**
     * @param  array<string, scalar|null>  $bodyData
     */
    public function execute(int $organizationId, int $letterId, string $templateHtml, array $bodyData): string
    {
        $html = $this->documentHtml($this->renderLetterTemplate->execute($templateHtml, $bodyData));
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $path = sprintf('letters/%d/%d.pdf', $organizationId, $letterId);
        Storage::disk('public')->put($path, $dompdf->output());

        return $path;
    }

    private function documentHtml(string $body): string
    {
        return <<<HTML
            <!doctype html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: DejaVu Sans, sans-serif; color: #242934; font-size: 12px; line-height: 1.65; }
                    .page { padding: 36px 42px; }
                    .kop { border-bottom: 3px solid #242934; padding-bottom: 14px; margin-bottom: 24px; text-align: center; }
                    .kop h1 { font-size: 18px; margin: 0 0 4px; text-transform: uppercase; }
                    .kop p { margin: 0; color: #59667a; }
                    .body p { margin: 0 0 12px; }
                    .signature { margin-top: 48px; width: 240px; float: right; text-align: center; }
                </style>
            </head>
            <body>
                <div class="page">
                    <div class="kop">
                        <h1>Prokerin Letter Generator</h1>
                        <p>Dokumen resmi organisasi mahasiswa</p>
                    </div>
                    <div class="body">{$body}</div>
                </div>
            </body>
            </html>
        HTML;
    }
}
