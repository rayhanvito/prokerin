<?php

declare(strict_types=1);

namespace App\Actions\Letter;

final class RenderLetterTemplateAction
{
    /**
     * @param  array<string, scalar|null>  $data
     */
    public function execute(string $templateHtml, array $data): string
    {
        $rendered = $templateHtml;

        foreach ($data as $key => $value) {
            $rendered = str_replace('{{'.$key.'}}', e((string) ($value ?? '')), $rendered);
        }

        return $rendered;
    }
}
