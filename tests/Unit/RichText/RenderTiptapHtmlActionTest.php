<?php

declare(strict_types=1);

namespace Tests\Unit\RichText;

use App\Actions\RichText\RenderTiptapHtmlAction;
use PHPUnit\Framework\TestCase;

final class RenderTiptapHtmlActionTest extends TestCase
{
    public function test_it_renders_heading_list_and_table_html(): void
    {
        $html = (new RenderTiptapHtmlAction)->execute([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [['type' => 'text', 'text' => 'Latar Belakang']],
                ],
                [
                    'type' => 'bulletList',
                    'content' => [
                        [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Peserta siap']],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'tableRow',
                            'content' => [
                                [
                                    'type' => 'tableHeader',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'content' => [['type' => 'text', 'text' => 'Item']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('<h1>Latar Belakang</h1>', $html);
        $this->assertStringContainsString('<ul><li><p>Peserta siap</p></li></ul>', $html);
        $this->assertStringContainsString('<table><tr><th><p>Item</p></th></tr></table>', $html);
    }

    public function test_it_escapes_text_content(): void
    {
        $html = (new RenderTiptapHtmlAction)->execute([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => '<script>alert(1)</script>']],
                ],
            ],
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
