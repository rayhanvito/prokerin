import {
    Bold,
    Heading1,
    Heading2,
    Heading3,
    Italic,
    List,
    ListOrdered,
    Minus,
    Pilcrow,
    Quote,
    Strikethrough,
    Table2,
    Underline,
} from 'lucide-react';

import { cn } from '@/lib/utils';
import type { Editor } from '@tiptap/react';

interface EditorToolbarProps {
    editor: Editor | null;
    disabled?: boolean;
}

interface ToolbarButton {
    label: string;
    icon: typeof Bold;
    active: (editor: Editor) => boolean;
    run: (editor: Editor) => boolean;
}

const buttons: ToolbarButton[] = [
    {
        label: 'Paragraf',
        icon: Pilcrow,
        active: (editor) => editor.isActive('paragraph'),
        run: (editor) => editor.chain().focus().setParagraph().run(),
    },
    {
        label: 'Heading 1',
        icon: Heading1,
        active: (editor) => editor.isActive('heading', { level: 1 }),
        run: (editor) => editor.chain().focus().toggleHeading({ level: 1 }).run(),
    },
    {
        label: 'Heading 2',
        icon: Heading2,
        active: (editor) => editor.isActive('heading', { level: 2 }),
        run: (editor) => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    },
    {
        label: 'Heading 3',
        icon: Heading3,
        active: (editor) => editor.isActive('heading', { level: 3 }),
        run: (editor) => editor.chain().focus().toggleHeading({ level: 3 }).run(),
    },
    {
        label: 'Bold',
        icon: Bold,
        active: (editor) => editor.isActive('bold'),
        run: (editor) => editor.chain().focus().toggleBold().run(),
    },
    {
        label: 'Italic',
        icon: Italic,
        active: (editor) => editor.isActive('italic'),
        run: (editor) => editor.chain().focus().toggleItalic().run(),
    },
    {
        label: 'Underline',
        icon: Underline,
        active: (editor) => editor.isActive('underline'),
        run: (editor) => editor.chain().focus().toggleUnderline().run(),
    },
    {
        label: 'Strikethrough',
        icon: Strikethrough,
        active: (editor) => editor.isActive('strike'),
        run: (editor) => editor.chain().focus().toggleStrike().run(),
    },
    {
        label: 'Bullet list',
        icon: List,
        active: (editor) => editor.isActive('bulletList'),
        run: (editor) => editor.chain().focus().toggleBulletList().run(),
    },
    {
        label: 'Ordered list',
        icon: ListOrdered,
        active: (editor) => editor.isActive('orderedList'),
        run: (editor) => editor.chain().focus().toggleOrderedList().run(),
    },
    {
        label: 'Blockquote',
        icon: Quote,
        active: (editor) => editor.isActive('blockquote'),
        run: (editor) => editor.chain().focus().toggleBlockquote().run(),
    },
    {
        label: 'Table',
        icon: Table2,
        active: (editor) => editor.isActive('table'),
        run: (editor) =>
            editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
    },
    {
        label: 'Horizontal rule',
        icon: Minus,
        active: () => false,
        run: (editor) => editor.chain().focus().setHorizontalRule().run(),
    },
];

export default function EditorToolbar({
    editor,
    disabled = false,
}: EditorToolbarProps) {
    return (
        <div className="flex flex-wrap gap-1 border-b border-[#e6edef] bg-white p-2">
            {buttons.map((button) => {
                const Icon = button.icon;
                const isActive = editor !== null && button.active(editor);

                return (
                    <button
                        key={button.label}
                        type="button"
                        title={button.label}
                        aria-label={button.label}
                        disabled={editor === null || disabled}
                        onClick={() => {
                            if (editor !== null) {
                                button.run(editor);
                            }
                        }}
                        className={cn(
                            'inline-flex h-8 w-8 items-center justify-center rounded-[4px] text-[#59667a] transition hover:bg-[#f5f7fb] hover:text-[#24695c] disabled:cursor-not-allowed disabled:opacity-50',
                            isActive && 'bg-[rgba(36,105,92,0.1)] text-[#24695c]',
                        )}
                    >
                        <Icon className="h-4 w-4" />
                    </button>
                );
            })}
        </div>
    );
}
