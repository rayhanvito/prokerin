<?php

declare(strict_types=1);

namespace App\Actions\RichText;

final class RenderTiptapHtmlAction
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function execute(array $document): string
    {
        return $this->renderChildren($document['content'] ?? []);
    }

    public function toPlainText(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return '';
        }

        if (($content['type'] ?? null) === 'text') {
            return (string) ($content['text'] ?? '');
        }

        $children = $content['content'] ?? $content;

        if (! is_array($children)) {
            return '';
        }

        return trim(implode(PHP_EOL, array_filter(array_map(
            fn (mixed $child): string => $this->toPlainText($child),
            $children,
        ))));
    }

    private function renderChildren(mixed $content): string
    {
        if (! is_array($content)) {
            return '';
        }

        return implode('', array_map(
            fn (mixed $node): string => is_array($node) ? $this->renderNode($node) : '',
            $content,
        ));
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderNode(array $node): string
    {
        $type = (string) ($node['type'] ?? '');

        return match ($type) {
            'doc' => $this->renderChildren($node['content'] ?? []),
            'paragraph' => '<p>'.$this->renderChildren($node['content'] ?? []).'</p>',
            'text' => $this->renderText($node),
            'heading' => $this->renderHeading($node),
            'bulletList' => '<ul>'.$this->renderChildren($node['content'] ?? []).'</ul>',
            'orderedList' => '<ol>'.$this->renderChildren($node['content'] ?? []).'</ol>',
            'listItem' => '<li>'.$this->renderChildren($node['content'] ?? []).'</li>',
            'blockquote' => '<blockquote>'.$this->renderChildren($node['content'] ?? []).'</blockquote>',
            'horizontalRule' => '<hr>',
            'table' => '<table>'.$this->renderChildren($node['content'] ?? []).'</table>',
            'tableRow' => '<tr>'.$this->renderChildren($node['content'] ?? []).'</tr>',
            'tableCell' => '<td>'.$this->renderChildren($node['content'] ?? []).'</td>',
            'tableHeader' => '<th>'.$this->renderChildren($node['content'] ?? []).'</th>',
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderText(array $node): string
    {
        $html = e((string) ($node['text'] ?? ''));
        $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

        foreach ($marks as $mark) {
            if (! is_array($mark)) {
                continue;
            }

            $html = match ((string) ($mark['type'] ?? '')) {
                'bold' => '<strong>'.$html.'</strong>',
                'italic' => '<em>'.$html.'</em>',
                'strike' => '<s>'.$html.'</s>',
                'underline' => '<u>'.$html.'</u>',
                'code' => '<code>'.$html.'</code>',
                default => $html,
            };
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function renderHeading(array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $level = min(max((int) ($attrs['level'] ?? 2), 1), 3);

        return sprintf('<h%d>%s</h%d>', $level, $this->renderChildren($node['content'] ?? []), $level);
    }
}
