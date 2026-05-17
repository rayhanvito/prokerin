<?php

declare(strict_types=1);

namespace App\Actions\RichText;

final class SanitizeRichTextAction
{
    private const ALLOWED_NODES = [
        'doc',
        'paragraph',
        'text',
        'heading',
        'bulletList',
        'orderedList',
        'listItem',
        'blockquote',
        'horizontalRule',
        'table',
        'tableRow',
        'tableCell',
        'tableHeader',
    ];

    private const ALLOWED_MARKS = [
        'bold',
        'italic',
        'strike',
        'underline',
        'code',
    ];

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function execute(array $document): array
    {
        $node = $this->sanitizeNode($document);

        return $node === [] ? $this->emptyDocument() : $node;
    }

    /**
     * @return array<string, mixed>
     */
    public function fromPlainText(string $text): array
    {
        $paragraphs = collect(preg_split('/\R{2,}/', trim($text)) ?: [])
            ->map(fn (string $paragraph): array => [
                'type' => 'paragraph',
                'content' => $this->textContent($paragraph),
            ])
            ->filter(fn (array $paragraph): bool => $paragraph['content'] !== [])
            ->values()
            ->all();

        return [
            'type' => 'doc',
            'content' => $paragraphs === [] ? [['type' => 'paragraph']] : $paragraphs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizeNode(array $node): array
    {
        $type = isset($node['type']) ? (string) $node['type'] : '';

        if (! in_array($type, self::ALLOWED_NODES, true)) {
            return [];
        }

        $sanitized = ['type' => $type];

        if ($type === 'text') {
            $sanitized['text'] = strip_tags((string) ($node['text'] ?? ''));
            $marks = $this->sanitizeMarks($node['marks'] ?? []);

            if ($marks !== []) {
                $sanitized['marks'] = $marks;
            }

            return $sanitized;
        }

        $attrs = $this->sanitizeAttrs($type, $node['attrs'] ?? []);

        if ($attrs !== []) {
            $sanitized['attrs'] = $attrs;
        }

        $content = $this->sanitizeContent($node['content'] ?? []);

        if ($content !== []) {
            $sanitized['content'] = $content;
        }

        return $sanitized;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeContent(mixed $content): array
    {
        if (! is_array($content)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $child): array => is_array($child) ? $this->sanitizeNode($child) : [],
            $content,
        )));
    }

    /**
     * @return array<int, array{type: string}>
     */
    private function sanitizeMarks(mixed $marks): array
    {
        if (! is_array($marks)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static function (mixed $mark): array {
                if (! is_array($mark) || ! isset($mark['type'])) {
                    return [];
                }

                $type = (string) $mark['type'];

                return in_array($type, self::ALLOWED_MARKS, true) ? ['type' => $type] : [];
            },
            $marks,
        )));
    }

    /**
     * @return array<string, int>
     */
    private function sanitizeAttrs(string $type, mixed $attrs): array
    {
        if ($type !== 'heading' || ! is_array($attrs)) {
            return [];
        }

        $level = (int) ($attrs['level'] ?? 2);

        return ['level' => min(max($level, 1), 3)];
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    private function textContent(string $text): array
    {
        return collect(preg_split('/\R/', $text) ?: [])
            ->flatMap(static fn (string $line, int $index): array => $index === 0
                ? [['type' => 'text', 'text' => trim($line)]]
                : [['type' => 'text', 'text' => ' '.trim($line)]])
            ->filter(static fn (array $node): bool => $node['text'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDocument(): array
    {
        return [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph'],
            ],
        ];
    }
}
