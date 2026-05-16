interface VihoTextareaProps {
    label: string;
    value?: string;
    placeholder?: string;
    rows?: number;
    readOnly?: boolean;
}

export default function VihoTextarea({
    label,
    value = '',
    placeholder,
    rows = 5,
    readOnly = true,
}: VihoTextareaProps) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">
                {label}
            </span>
            <textarea
                value={value}
                placeholder={placeholder}
                rows={rows}
                readOnly={readOnly}
                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm leading-6 text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
            />
        </label>
    );
}
