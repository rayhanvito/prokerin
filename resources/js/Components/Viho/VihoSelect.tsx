interface VihoSelectProps {
    label: string;
    value: string;
    options: string[];
    disabled?: boolean;
}

export default function VihoSelect({
    label,
    value,
    options,
    disabled = true,
}: VihoSelectProps) {
    return (
        <label className="block">
            <span className="text-sm font-semibold text-[#242934]">
                {label}
            </span>
            <select
                value={value}
                disabled={disabled}
                className="mt-2 block w-full rounded-[4px] border-[#e6edef] text-sm text-[#242934] shadow-none focus:border-[#24695c] focus:ring-[#24695c]"
            >
                {options.map((option) => (
                    <option key={option}>{option}</option>
                ))}
            </select>
        </label>
    );
}
