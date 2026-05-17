<?php

declare(strict_types=1);

namespace Tests\Unit\RichText;

use App\Actions\RichText\SanitizeRichTextAction;
use PHPUnit\Framework\TestCase;

final class SanitizeRichTextActionTest extends TestCase
{
    public function test_it_strips_disallowed_nodes_and_preserves_allowed_marks(): void
    {
        $document = (new SanitizeRichTextAction)->execute([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => '<script>alert(1)</script>Proposal aman',
                            'marks' => [
                                ['type' => 'bold'],
                                ['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'iframe',
                    'attrs' => ['src' => 'https://evil.test'],
                ],
            ],
        ]);

        $this->assertSame('doc', $document['type']);
        $this->assertCount(1, $document['content']);
        $this->assertSame('alert(1)Proposal aman', $document['content'][0]['content'][0]['text']);
        $this->assertSame([['type' => 'bold']], $document['content'][0]['content'][0]['marks']);
    }

    public function test_it_clamps_heading_level_attributes(): void
    {
        $document = (new SanitizeRichTextAction)->execute([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 9, 'onclick' => 'alert(1)'],
                    'content' => [['type' => 'text', 'text' => 'Judul']],
                ],
            ],
        ]);

        $this->assertSame(['level' => 3], $document['content'][0]['attrs']);
    }
}
