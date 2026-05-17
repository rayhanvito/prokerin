<x-filament-panels::page>
    <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Organization</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Plan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Joined</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Progress</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($organizations as $organization)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-950">{{ $organization['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $organization['slug'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ str($organization['plan_tier'])->headline() }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $organization['created_at'] }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800">
                                    {{ $organization['completed_count'] }}/{{ $organization['total_count'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($organization['checklist'] as $label => $done)
                                        <span class="{{ $done ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }} rounded border px-2 py-1 text-xs font-medium">
                                            {{ $done ? 'Done' : 'Todo' }} · {{ $label }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                No new organizations in the last 30 days.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
