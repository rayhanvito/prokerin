import { ReactNode } from 'react';

import { cn } from '@/lib/utils';

interface FormFieldProps {
    label: string;
    htmlFor: string;
    required?: boolean;
    error?: string;
    hint?: string;
    children: ReactNode;
}

export default function FormField({
    label,
    htmlFor,
    required = false,
    error,
    hint,
    children,
}: FormFieldProps) {
    return (
        <div>
            <label
                htmlFor={htmlFor}
                className="block text-sm font-semibold text-[#242934]"
            >
                {label}
                {required ? <span className="text-[#d22d3d]"> *</span> : null}
            </label>
            <div
                className={cn(
                    'mt-2',
                    error && 'rounded-[4px] border border-[#d22d3d]',
                )}
            >
                {children}
            </div>
            {error ? (
                <p className="mt-2 text-sm text-[#d22d3d]">{error}</p>
            ) : hint ? (
                <p className="mt-2 text-sm text-[#717171]">{hint}</p>
            ) : null}
        </div>
    );
}
