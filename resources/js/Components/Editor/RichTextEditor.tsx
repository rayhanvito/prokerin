import CharacterCount from '@tiptap/extension-character-count';
import Placeholder from '@tiptap/extension-placeholder';
import { Table } from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';

import EditorToolbar from '@/Components/Editor/EditorToolbar';
import { cn } from '@/lib/utils';
import type { JSONContent } from '@tiptap/react';

interface RichTextEditorProps {
    value: JSONContent;
    onChange: (json: JSONContent) => void;
    placeholder?: string;
    maxChars?: number;
    readOnly?: boolean;
}

export default function RichTextEditor({
    value,
    onChange,
    placeholder = 'Tulis konten proposal...',
    maxChars = 5000,
    readOnly = false,
}: RichTextEditorProps) {
    const editor = useEditor({
        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [1, 2, 3],
                },
            }),
            Table.configure({
                resizable: true,
            }),
            TableRow,
            TableHeader,
            TableCell,
            CharacterCount.configure({
                limit: maxChars,
            }),
            Placeholder.configure({
                placeholder,
            }),
        ],
        content: value,
        editable: !readOnly,
        onUpdate: ({ editor: currentEditor }) => {
            onChange(currentEditor.getJSON());
        },
        editorProps: {
            attributes: {
                class: 'min-h-[180px] px-4 py-3 text-sm leading-7 text-[#59667a] outline-none',
            },
        },
    });

    const characterCount = editor?.storage.characterCount.characters() as
        | number
        | undefined;

    return (
        <div
            className={cn(
                'overflow-hidden rounded-[4px] border border-[#e6edef] bg-white',
                readOnly && 'bg-[#f5f7fb]',
            )}
        >
            <EditorToolbar editor={editor} disabled={readOnly} />
            <EditorContent
                editor={editor}
                className="prokerin-rich-text [&_.ProseMirror]:min-h-[180px] [&_.ProseMirror_blockquote]:border-l-4 [&_.ProseMirror_blockquote]:border-[#ba895d] [&_.ProseMirror_blockquote]:pl-3 [&_.ProseMirror_h1]:text-xl [&_.ProseMirror_h1]:font-semibold [&_.ProseMirror_h2]:text-lg [&_.ProseMirror_h2]:font-semibold [&_.ProseMirror_h3]:text-base [&_.ProseMirror_h3]:font-semibold [&_.ProseMirror_hr]:my-4 [&_.ProseMirror_li>p]:my-0 [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:pl-5 [&_.ProseMirror_p]:my-2 [&_.ProseMirror_table]:w-full [&_.ProseMirror_table]:border-collapse [&_.ProseMirror_td]:border [&_.ProseMirror_td]:border-[#e6edef] [&_.ProseMirror_td]:p-2 [&_.ProseMirror_th]:border [&_.ProseMirror_th]:border-[#e6edef] [&_.ProseMirror_th]:bg-[#f5f7fb] [&_.ProseMirror_th]:p-2 [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:pl-5"
            />
            <div className="border-t border-[#e6edef] px-4 py-2 text-right text-xs text-[#717171]">
                {characterCount ?? 0}/{maxChars}
            </div>
        </div>
    );
}
