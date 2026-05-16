interface VihoFieldProps {
    label: string;
    value?: string;
    placeholder?: string;
    type?: 'text' | 'date' | 'number' | 'email';
    readOnly?: boolean;
}

export default function VihoField({
    label,
    value = '',
    placeholder,
    type = 'text',
    readOnly = true,
}: VihoFieldProps) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">
                {label}
            </span>
            <input
                type={type}
                value={value}
                placeholder={placeholder}
                readOnly={readOnly}
                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
            />
        </label>
    );
}
