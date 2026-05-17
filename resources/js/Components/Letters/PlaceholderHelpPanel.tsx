import { Braces } from 'lucide-react';

interface PlaceholderHelpPanelProps {
    placeholders: string[];
}

export default function PlaceholderHelpPanel({ placeholders }: PlaceholderHelpPanelProps) {
    return (
        <div className="rounded-[4px] border border-[#e6edef] bg-white p-5 shadow-sm">
            <div className="flex items-center gap-3">
                <span className="flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#24695c]/10 text-[#24695c]">
                    <Braces className="h-4 w-4" />
                </span>
                <h2 className="text-sm font-semibold text-[#242934]">
                    Placeholder
                </h2>
            </div>
            <div className="mt-4 flex flex-wrap gap-2">
                {placeholders.map((placeholder) => (
                    <code
                        key={placeholder}
                        className="rounded-[4px] bg-[#f5f7fb] px-2 py-1 text-xs font-semibold text-[#59667a] ring-1 ring-[#e6edef]"
                    >
                        {'{{'}
                        {placeholder}
                        {'}}'}
                    </code>
                ))}
            </div>
        </div>
    );
}
