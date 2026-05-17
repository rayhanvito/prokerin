import { generateHTML } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Table } from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';

import type { JSONContent } from '@tiptap/react';

interface RichTextRendererProps {
    value: JSONContent;
}

export default function RichTextRenderer({ value }: RichTextRendererProps) {
    const html = generateHTML(value, [
        StarterKit,
        Table,
        TableRow,
        TableHeader,
        TableCell,
    ]);

    return (
        <div
            className="prokerin-rich-text text-sm leading-6 text-[#59667a] [&_blockquote]:border-l-4 [&_blockquote]:border-[#ba895d] [&_blockquote]:pl-3 [&_h1]:text-xl [&_h1]:font-semibold [&_h2]:text-lg [&_h2]:font-semibold [&_h3]:font-semibold [&_ol]:list-decimal [&_ol]:pl-5 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-[#e6edef] [&_td]:p-2 [&_th]:border [&_th]:border-[#e6edef] [&_th]:bg-white [&_th]:p-2 [&_ul]:list-disc [&_ul]:pl-5"
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
