import type { JSONContent } from '@tiptap/react';

export type TiptapJson = JSONContent;

export function plainTextToTiptap(text: string): TiptapJson {
    const lines = text
        .split(/\n{2,}/)
        .map((line) => line.trim())
        .filter((line) => line.length > 0);

    return {
        type: 'doc',
        content:
            lines.length > 0
                ? lines.map((line) => ({
                      type: 'paragraph',
                      content: [{ type: 'text', text: line }],
                  }))
                : [{ type: 'paragraph' }],
    };
}

export function normalizeTiptap(value: string | TiptapJson): TiptapJson {
    return typeof value === 'string' ? plainTextToTiptap(value) : value;
}
