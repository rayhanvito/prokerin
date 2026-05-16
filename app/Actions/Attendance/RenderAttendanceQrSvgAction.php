<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class RenderAttendanceQrSvgAction
{
    public function execute(string $token, int $size = 320): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd,
        );

        $writer = new Writer($renderer);

        return $writer->writeString($token);
    }
}
