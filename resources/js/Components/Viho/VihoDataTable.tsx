import VihoStatusBadge from '@/Components/Viho/VihoStatusBadge';
import { cn } from '@/lib/utils';

interface VihoTableColumn<TRow> {
    key: keyof TRow;
    label: string;
    align?: 'left' | 'right';
}

interface VihoDataTableProps<TRow extends Record<string, string>> {
    columns: Array<VihoTableColumn<TRow>>;
    rows: TRow[];
    statusKey?: keyof TRow;
}

export default function VihoDataTable<TRow extends Record<string, string>>({
    columns,
    rows,
    statusKey,
}: VihoDataTableProps<TRow>) {
    return (
        <div className="-m-5 overflow-x-auto">
            <table className="min-w-full border-collapse text-sm">
                <thead>
                    <tr className="border-b border-[#e6edef] bg-[#f5f7fb] text-left text-xs font-semibold uppercase tracking-[0.08em] text-[#59667a]">
                        {columns.map((column) => (
                            <th
                                key={String(column.key)}
                                className={cn(
                                    'px-5 py-3',
                                    column.align === 'right'
                                        ? 'text-right'
                                        : 'text-left',
                                )}
                            >
                                {column.label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-[#e6edef] bg-white">
                    {rows.map((row, index) => (
                        <tr key={`${row[columns[0].key]}-${index}`}>
                            {columns.map((column) => {
                                const value = row[column.key];

                                return (
                                    <td
                                        key={String(column.key)}
                                        className={cn(
                                            'whitespace-nowrap px-5 py-4',
                                            column.align === 'right'
                                                ? 'text-right'
                                                : 'text-left',
                                        )}
                                    >
                                        {statusKey === column.key ? (
                                            <VihoStatusBadge>
                                                {value}
                                            </VihoStatusBadge>
                                        ) : (
                                            <span className="font-medium text-[#242934]">
                                                {value}
                                            </span>
                                        )}
                                    </td>
                                );
                            })}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
